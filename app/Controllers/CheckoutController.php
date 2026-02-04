<?php
/**
 * Checkout Controller
 * Handles the checkout process and PlacetoPay integration
 */
class CheckoutController extends Controller
{
    /**
     * Display checkout form
     */
    public function index(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        
        if (empty($cart)) {
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }
        
        // Get cart items with product info
        $productModel = new Product($this->config);
        $productIds = array_keys($cart);
        $products = $productModel->findMany($productIds);
        
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product['id']] = $product;
        }
        
        $cartItems = [];
        $total = 0;
        
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
        
        $this->render('checkout/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Process checkout and create PlacetoPay session
     */
    public function process(): void
    {
        // Rate limiting: Max 5 checkout attempts per minute per IP
        $clientIp = RateLimiter::getClientIdentifier();
        RateLimiter::enforce($clientIp, 5, 60, 'checkout', 'Too many checkout attempts. Please wait a minute and try again.');
        
        // Validate CSRF token
        Security::requireCsrfToken();
        
        $cart = $_SESSION['cart'] ?? [];
        
        if (empty($cart)) {
            $this->setFlash('error', 'Your cart is empty');
            $this->redirect('/cart');
            return;
        }
        
        // Validate form data
        $errors = $this->validateCheckoutForm();
        
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/checkout');
            return;
        }
        
        // Get cart items with product info
        $productModel = new Product($this->config);
        $productIds = array_keys($cart);
        $products = $productModel->findMany($productIds);
        
        $productsById = [];
        foreach ($products as $product) {
            $productsById[$product['id']] = $product;
        }
        
        // Build order items
        $orderItems = [];
        $paymentItems = [];
        $total = 0;
        
        foreach ($cart as $productId => $item) {
            if (isset($productsById[$productId])) {
                $product = $productsById[$productId];
                $subtotal = $product['price'] * $item['quantity'];
                
                $orderItems[] = [
                    'product_id' => $productId,
                    'product_name' => $product['name'],
                    'quantity' => $item['quantity'],
                    'price' => $product['price'],
                ];
                
                $paymentItems[] = [
                    'sku' => (string) $productId,
                    'name' => $product['name'],
                    'category' => 'physical',
                    'qty' => $item['quantity'],
                    'price' => $product['price'],
                    'tax' => 0,
                ];
                
                $total += $subtotal;
            }
        }
        
        // Create order in database
        $orderModel = new Order($this->config);
        $reference = $orderModel->generateReference();
        
        // Sanitize user input
        $firstName = Validator::sanitizeString($_POST['first_name']);
        $lastName = Validator::sanitizeString($_POST['last_name']);
        $email = Validator::sanitizeString($_POST['email']);
        $phone = !empty($_POST['phone']) ? Validator::sanitizeString($_POST['phone']) : null;
        $document = !empty($_POST['document']) ? Validator::sanitizeString($_POST['document']) : null;
        
        $orderId = $orderModel->create([
            'reference' => $reference,
            'total' => $total,
            'currency' => $this->config['app']['currency'],
            'customer_name' => trim($firstName . ' ' . $lastName),
            'customer_email' => $email,
            'customer_phone' => $phone,
            'customer_document' => $document,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'MockStore',
            'status' => 'PENDING',
        ]);
        
        if (!$orderId) {
            $this->setFlash('error', 'Failed to create order');
            $this->redirect('/checkout');
            return;
        }
        
        // Add order items
        $orderModel->addItems($orderId, $orderItems);
        
        // Create PlacetoPay session
        $placetoPay = new PlacetoPayService($this->config);
        
        $sessionData = [
            'reference' => $reference,
            'description' => "Order $reference - MockStore Purchase",
            'total' => $total,
            'currency' => $this->config['app']['currency'],
            'items' => $paymentItems,
            'buyer' => [
                'name' => $firstName,
                'surname' => $lastName,
                'email' => $email,
                'mobile' => $phone ?? '',
                'document' => $document ?? '',
                'documentType' => 'CC',
            ],
            'ipAddress' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'userAgent' => $_SERVER['HTTP_USER_AGENT'] ?? 'MockStore',
        ];
        
        $response = $placetoPay->createSession($sessionData);
        
        // Check if session was created successfully
        if (isset($response['status']['status']) && $response['status']['status'] === 'OK') {
            // Update order with PlacetoPay info
            $orderModel->updatePlacetoPayInfo(
                $orderId,
                $response['requestId'],
                $response['processUrl']
            );
            
            // Clear the cart
            unset($_SESSION['cart']);
            
            // Regenerate session ID for security after checkout
            Security::regenerateSession();
            
            // Redirect to PlacetoPay checkout
            $this->redirect($response['processUrl']);
        } else {
            // Handle error
            $errorMessage = $response['status']['message'] ?? 'Failed to create payment session';
            $orderModel->updateStatus($orderId, 'FAILED', $errorMessage);
            
            $this->setFlash('error', 'Payment error: ' . $errorMessage);
            $this->redirect('/checkout');
        }
    }

    /**
     * Validate checkout form data
     * 
     * @return array Array of error messages
     */
    private function validateCheckoutForm(): array
    {
        $errors = [];
        
        // Validate first name
        if (empty($_POST['first_name'])) {
            $errors[] = 'First name is required';
        } elseif (!Validator::name($_POST['first_name'], 2, 50)) {
            $errors[] = 'First name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes';
        }
        
        // Validate last name
        if (empty($_POST['last_name'])) {
            $errors[] = 'Last name is required';
        } elseif (!Validator::name($_POST['last_name'], 2, 50)) {
            $errors[] = 'Last name must be 2-50 characters and contain only letters, spaces, hyphens, and apostrophes';
        }
        
        // Validate email
        if (empty($_POST['email'])) {
            $errors[] = 'Email is required';
        } elseif (!Validator::email($_POST['email'], 100)) {
            $errors[] = 'Valid email address is required (max 100 characters)';
        }
        
        // Validate phone (optional but must be valid if provided)
        if (!empty($_POST['phone']) && !Validator::phone($_POST['phone'])) {
            $errors[] = 'Phone number format is invalid';
        }
        
        // Validate document (optional but must be valid if provided)
        if (!empty($_POST['document']) && !Validator::document($_POST['document'])) {
            $errors[] = 'Document ID format is invalid (5-20 alphanumeric characters)';
        }
        
        return $errors;
    }
}
