<?php
/**
 * Home Page View
 * Displays welcome message and featured products
 */
$pageTitle = 'Welcome';
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">Welcome to <?= $appName ?></h1>
        <p class="lead mb-4 opacity-75">
            Experience secure online payments with PlacetoPay WebCheckout integration
        </p>
        <a href="/products" class="btn btn-light btn-lg px-5">
            <i class="bi bi-bag me-2"></i>Shop Now
        </a>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-shield-check fs-1 text-primary"></i>
                    </div>
                    <h5>Secure Payments</h5>
                    <p class="text-muted">
                        All transactions are processed securely through PlacetoPay's PCI-compliant platform.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-credit-card fs-1 text-primary"></i>
                    </div>
                    <h5>Multiple Payment Methods</h5>
                    <p class="text-muted">
                        Accept credit cards, debit cards, and other payment methods supported by PlacetoPay.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                        <i class="bi bi-phone fs-1 text-primary"></i>
                    </div>
                    <h5>Mobile Optimized</h5>
                    <p class="text-muted">
                        Checkout experience optimized for all devices - desktop, tablet, and mobile.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Products</h2>
            <p class="text-muted">Discover our most popular items</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card product-card h-100 shadow-sm">
                        <img src="<?= htmlspecialchars($product['image']) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <?= htmlspecialchars(substr($product['description'], 0, 80)) ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fs-4 fw-bold text-primary">
                                    $<?= number_format($product['price'], 2) ?>
                                </span>
                                <form action="/cart/add" method="POST">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="/products" class="btn btn-outline-primary btn-lg">
                View All Products <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Integration Info Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">PlacetoPay WebCheckout Integration</h2>
                <p class="text-muted mb-4">
                    This demo store demonstrates a complete integration with PlacetoPay's WebCheckout API.
                    The integration follows the MVC (Model-View-Controller) architecture pattern.
                </p>
                <ul class="list-unstyled">
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Create payment sessions with customer and order data
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Redirect users to secure PlacetoPay checkout
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Handle payment callbacks and status updates
                    </li>
                    <li class="mb-3">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        Query session status for real-time updates
                    </li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="bg-dark text-white p-4 rounded-3">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-danger me-2">●</span>
                        <span class="badge bg-warning me-2">●</span>
                        <span class="badge bg-success me-2">●</span>
                        <span class="ms-2 text-muted small">PlacetoPayService.php</span>
                    </div>
                    <pre class="mb-0 text-light"><code>// Generate authentication for API requests
$auth = [
    'login' => $this->login,
    'tranKey' => base64_encode(
        hash('sha256', 
            $nonce . $seed . $secretKey, 
            true
        )
    ),
    'nonce' => base64_encode($nonce),
    'seed' => date('c'),
];</code></pre>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
