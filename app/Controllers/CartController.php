<?php
/**
 * Cart Controller
 * Handles shopping cart operations
 */
class CartController extends Controller
{
    /**
     * Display the cart
     */
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        $cartItems = [];
        $total = 0;
        
        if (!empty($cart)) {
            $productModel = new Product($this->config);
            $productIds = array_keys($cart);
            $products = $productModel->findMany($productIds);
            
            // Index products by ID
            $productsById = [];
            foreach ($products as $product) {
                $productsById[$product['id']] = $product;
            }
            
            // Build cart items with full product info
            foreach ($cart as $productId => $item) {
                if (isset($productsById[$productId])) {
                    $product = $productsById[$productId];
                    $subtotal = $product['price'] * $item['quantity'];
                    $cartItems[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'subtotal' => $subtotal,
                    ];
                    $total += $subtotal;
                }
            }
        }
        
        $this->render('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Add a product to the cart
     */
    public function add(): void
    {
        // Rate limiting: Max 20 cart additions per minute per IP
        $clientIp = RateLimiter::getClientIdentifier();
        RateLimiter::enforce($clientIp, 20, 60, 'cart_add', 'Too many requests. Please slow down.');
        
        // Validate CSRF token
        Security::requireCsrfToken();
        
        // Validate product_id
        if (!isset($_POST['product_id']) || !Validator::positiveInteger($_POST['product_id'])) {
            $this->setFlash('error', 'Invalid product ID');
            $this->redirect('/products');
            return;
        }
        
        $productId = (int) $_POST['product_id'];
        
        // Validate quantity
        if (!isset($_POST['quantity']) || !Validator::positiveInteger($_POST['quantity'])) {
            $this->setFlash('error', 'Invalid quantity');
            $this->redirect('/products');
            return;
        }
        
        $quantity = (int) $_POST['quantity'];
        
        // Enforce maximum quantity per item (prevents abuse)
        if (!Validator::quantity($quantity, 99)) {
            $this->setFlash('error', 'Quantity must be between 1 and 99');
            $this->redirect('/products');
            return;
        }
        
        // Verify product exists
        $productModel = new Product($this->config);
        $product = $productModel->find($productId);
        
        if (!$product) {
            $this->setFlash('error', 'Product not found');
            $this->redirect('/products');
            return;
        }
        
        // Initialize cart if needed
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Calculate new quantity and enforce limit
        $currentQuantity = $_SESSION['cart'][$productId]['quantity'] ?? 0;
        $newQuantity = $currentQuantity + $quantity;
        
        if (!Validator::quantity($newQuantity, 99)) {
            $this->setFlash('error', 'Cannot add more items. Maximum quantity per product is 99');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/products';
            $this->redirect($referer);
            return;
        }
        
        // Add or update cart item
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] = $newQuantity;
        } else {
            $_SESSION['cart'][$productId] = ['quantity' => $quantity];
        }
        
        $this->setFlash('success', "Added '{$product['name']}' to cart");
        
        // Redirect back to referrer or products
        $referer = $_SERVER['HTTP_REFERER'] ?? '/products';
        $this->redirect($referer);
    }

    /**
     * Remove a product from the cart
     */
    public function remove(): void
    {
        // Validate CSRF token
        Security::requireCsrfToken();
        
        // Validate product_id
        if (!isset($_POST['product_id']) || !Validator::positiveInteger($_POST['product_id'])) {
            $this->setFlash('error', 'Invalid product ID');
            $this->redirect('/cart');
            return;
        }
        
        $productId = (int) $_POST['product_id'];
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
            $this->setFlash('success', 'Item removed from cart');
        }
        
        $this->redirect('/cart');
    }
}
