<?php
/**
 * MockStore - Entry Point
 * All requests are routed through this file
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/Controllers/',
        BASE_PATH . '/app/Models/',
        BASE_PATH . '/app/Services/',
        BASE_PATH . '/app/Core/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Load environment variables
Env::load(BASE_PATH);

// Load configuration
$config = require BASE_PATH . '/config/config.php';

// Initialize error handler (must be early in the bootstrap process)
ErrorHandler::init($config);

// Initialize rate limiter
RateLimiter::init($config);

// Set security headers (must be before any output)
Security::setSecurityHeaders();

// Start secure session with security hardening
Security::startSecureSession();

// Simple Router
$router = new Router($config);

// Define routes
$router->get('/', 'HomeController@index');
$router->get('/products', 'ProductController@index');
$router->get('/product/{id}', 'ProductController@show');
$router->post('/cart/add', 'CartController@add');
$router->get('/cart', 'CartController@index');
$router->post('/cart/remove', 'CartController@remove');
$router->get('/checkout', 'CheckoutController@index');
$router->post('/checkout/process', 'CheckoutController@process');
$router->get('/payment/return', 'PaymentController@return');
$router->get('/payment/cancel', 'PaymentController@cancel');
$router->get('/orders', 'OrderController@index');
$router->get('/order/{id}', 'OrderController@show');

// Dispatch the request
$router->dispatch();
