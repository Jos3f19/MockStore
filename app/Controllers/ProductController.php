<?php
/**
 * Product Controller
 * Handles product listing and details
 */
class ProductController extends Controller
{
    /**
     * Display all products
     */
    public function index(): void
    {
        $productModel = new Product($this->config);
        $products = $productModel->getAll();
        
        $this->render('products/index', [
            'products' => $products,
            'flash' => $this->getFlash(),
        ]);
    }

    /**
     * Display a single product
     * 
     * @param string $id Product ID
     */
    public function show(string $id): void
    {
        $productModel = new Product($this->config);
        $product = $productModel->find((int) $id);
        
        if (!$product) {
            $this->redirect('/products');
            return;
        }
        
        $this->render('products/show', [
            'product' => $product,
            'flash' => $this->getFlash(),
        ]);
    }
}
