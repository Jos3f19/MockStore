<?php
/**
 * Home Controller
 * Handles the home page
 */
class HomeController extends Controller
{
    /**
     * Display the home page
     */
    public function index(): void
    {
        $productModel = new Product($this->config);
        $products = array_slice($productModel->getAll(), 0, 3); // Featured products
        
        $this->render('home/index', [
            'products' => $products,
            'flash' => $this->getFlash(),
        ]);
    }
}
