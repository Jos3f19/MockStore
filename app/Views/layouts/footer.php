    </main>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">
                        <i class="bi bi-shop me-2"></i><?= $appName ?>
                    </h5>
                    <p class="mb-0">
                        A demo store showcasing PlacetoPay WebCheckout integration.
                        Built with PHP using the MVC pattern.
                    </p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="text-white mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/" class="text-decoration-none text-secondary">Home</a></li>
                        <li><a href="/products" class="text-decoration-none text-secondary">Products</a></li>
                        <li><a href="/cart" class="text-decoration-none text-secondary">Shopping Cart</a></li>
                        <li><a href="/orders" class="text-decoration-none text-secondary">My Orders</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="text-white mb-3">Payment Gateway</h5>
                    <p class="mb-2">Powered by</p>
                    <a href="https://www.placetopay.com" target="_blank" class="text-decoration-none">
                        <span class="badge bg-primary fs-6 px-3 py-2">
                            <i class="bi bi-credit-card me-2"></i>PlacetoPay
                        </span>
                    </a>
                </div>
            </div>
            <hr class="my-4 border-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= $appName ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">
                        <a href="https://docs.placetopay.dev/en/checkout/" target="_blank" class="text-secondary text-decoration-none">
                            <i class="bi bi-book me-1"></i>PlacetoPay Docs
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
