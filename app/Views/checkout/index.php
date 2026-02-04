<?php
/**
 * Checkout View
 * Displays checkout form for customer information
 */
$pageTitle = 'Checkout';
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">
        <i class="bi bi-credit-card me-2"></i>Checkout
    </h1>
    
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person me-2"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body">
                    <form action="/checkout/process" method="POST" id="checkoutForm">
                        <?= Security::csrfField() ?>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="first_name" 
                                       name="first_name" 
                                       required
                                       minlength="2"
                                       maxlength="50"
                                       pattern="[a-zA-ZÀ-ÿ\s\-']+"
                                       title="Only letters, spaces, hyphens, and apostrophes allowed"
                                       placeholder="John">
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="last_name" 
                                       name="last_name" 
                                       required
                                       minlength="2"
                                       maxlength="50"
                                       pattern="[a-zA-ZÀ-ÿ\s\-']+"
                                       title="Only letters, spaces, hyphens, and apostrophes allowed"
                                       placeholder="Doe">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       required
                                       maxlength="100"
                                       placeholder="john.doe@example.com">
                                <small class="text-muted">We'll send your order confirmation here</small>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone"
                                       pattern="[+]?[0-9\s\-()]{7,20}"
                                       title="Enter a valid phone number (7-20 characters)"
                                       placeholder="+1 234 567 8900">
                            </div>
                            <div class="col-md-6">
                                <label for="document" class="form-label">Document ID</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="document" 
                                       name="document"
                                       pattern="[A-Z0-9\-]{5,20}"
                                       title="5-20 alphanumeric characters and hyphens"
                                       placeholder="1234567890">
                                <small class="text-muted">Optional identification number</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Note:</strong> After clicking "Pay with PlacetoPay", you will be redirected to 
                            PlacetoPay's secure checkout page to complete your payment.
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-lock me-2"></i>Pay with PlacetoPay
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Test Cards Info -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning bg-opacity-25">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>Test Card Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        For testing purposes, you can use the following card numbers:
                    </p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Scenario</th>
                                    <th>Card Number</th>
                                    <th>CVV</th>
                                    <th>Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="table-success">
                                    <td><span class="badge bg-success">APPROVED</span></td>
                                    <td><code>4111 1111 1111 1111</code></td>
                                    <td>123</td>
                                    <td>Any future date</td>
                                </tr>
                                <tr class="table-warning">
                                    <td><span class="badge bg-warning text-dark">PENDING</span></td>
                                    <td><code>4864 9213 3682 4366</code></td>
                                    <td>123</td>
                                    <td>Any future date</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><span class="badge bg-danger">REJECTED</span></td>
                                    <td><code>5367 6800 0000 0013</code></td>
                                    <td>123</td>
                                    <td>Any future date</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted d-block mt-2">
                        * Use any name, any future expiration date, and any 3-digit CVV.
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-5">
            <div class="card shadow-sm sticky-top" style="top: 20px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bag me-2"></i>Order Summary
                    </h5>
                </div>
                <div class="card-body">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="d-flex mb-3">
                            <img src="<?= htmlspecialchars($item['product']['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['product']['name']) ?>"
                                 class="rounded me-3"
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($item['product']['name']) ?></h6>
                                <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                            </div>
                            <div class="text-end">
                                <strong>$<?= number_format($item['subtotal'], 2) ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
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
                    
                    <div class="d-flex justify-content-between">
                        <strong class="fs-5">Total</strong>
                        <strong class="fs-5 text-primary">$<?= number_format($total, 2) ?></strong>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-shield-check text-success me-2"></i>
                        Your payment is secured by PlacetoPay
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
