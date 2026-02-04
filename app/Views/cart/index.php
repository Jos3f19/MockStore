<?php
/**
 * Shopping Cart View
 * Displays cart contents and allows checkout
 */
$pageTitle = 'Shopping Cart';
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">
        <i class="bi bi-cart3 me-2"></i>Shopping Cart
    </h1>
    
    <?php if (empty($cartItems)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-cart-x display-1 text-muted"></i>
            </div>
            <h3>Your cart is empty</h3>
            <p class="text-muted mb-4">Looks like you haven't added any items yet.</p>
            <a href="/products" class="btn btn-primary btn-lg">
                <i class="bi bi-bag me-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 ps-4">Product</th>
                                        <th class="border-0 text-center">Price</th>
                                        <th class="border-0 text-center">Quantity</th>
                                        <th class="border-0 text-center">Subtotal</th>
                                        <th class="border-0 pe-4"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $item): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="d-flex align-items-center">
                                                    <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                                         alt="<?= htmlspecialchars($item['product']['name']) ?>"
                                                         class="rounded me-3"
                                                         style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?= htmlspecialchars($item['product']['name']) ?></h6>
                                                        <small class="text-muted">SKU: <?= $item['product']['id'] ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center align-middle">
                                                $<?= number_format($item['product']['price'], 2) ?>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-secondary"><?= $item['quantity'] ?></span>
                                            </td>
                                            <td class="text-center align-middle fw-bold">
                                                $<?= number_format($item['subtotal'], 2) ?>
                                            </td>
                                            <td class="text-end pe-4 align-middle">
                                                <form action="/cart/remove" method="POST" class="d-inline">
                                                    <?= Security::csrfField() ?>
                                                    <input type="hidden" name="product_id" value="<?= $item['product']['id'] ?>">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="mt-3">
                    <a href="/products" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>$<?= number_format($total, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax</span>
                            <span>$0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <strong class="fs-5">Total</strong>
                            <strong class="fs-5 text-primary">$<?= number_format($total, 2) ?></strong>
                        </div>
                        <a href="/checkout" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-credit-card me-2"></i>Proceed to Checkout
                        </a>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="d-flex align-items-center justify-content-center text-muted small">
                            <i class="bi bi-shield-lock me-2"></i>
                            Secure checkout powered by PlacetoPay
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
