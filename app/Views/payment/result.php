<?php
/**
 * Payment Result View
 * Displays the payment result after returning from PlacetoPay
 */
$pageTitle = 'Payment Result';
include BASE_PATH . '/app/Views/layouts/header.php';

$status = $order['status'];
$statusClass = PlacetoPayService::getStatusBadgeClass($status);
$statusLabel = PlacetoPayService::getStatusLabel($status);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4 text-center">
                <div class="card-body py-5">
                    <?php if ($status === 'APPROVED'): ?>
                        <div class="mb-4">
                            <i class="bi bi-check-circle-fill display-1 text-success"></i>
                        </div>
                        <h2 class="fw-bold text-success">Payment Successful!</h2>
                        <p class="text-muted mb-0">
                            Thank you for your purchase. Your order has been processed successfully.
                        </p>
                    <?php elseif ($status === 'PENDING'): ?>
                        <div class="mb-4">
                            <i class="bi bi-clock-fill display-1 text-warning"></i>
                        </div>
                        <h2 class="fw-bold text-warning">Payment Pending</h2>
                        <p class="text-muted mb-0">
                            Your payment is being processed. We'll notify you once it's confirmed.
                        </p>
                    <?php elseif ($status === 'REJECTED'): ?>
                        <div class="mb-4">
                            <i class="bi bi-x-circle-fill display-1 text-danger"></i>
                        </div>
                        <h2 class="fw-bold text-danger">Payment Rejected</h2>
                        <p class="text-muted mb-0">
                            Unfortunately, your payment could not be processed.
                        </p>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="bi bi-question-circle-fill display-1 text-secondary"></i>
                        </div>
                        <h2 class="fw-bold">Payment Status: <?= htmlspecialchars($statusLabel) ?></h2>
                        <p class="text-muted mb-0">
                            <?= htmlspecialchars($order['status_message'] ?? 'Please check your order details below.') ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>Order Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Order Reference:</div>
                        <div class="col-sm-8 fw-bold"><?= htmlspecialchars($order['reference']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Status:</div>
                        <div class="col-sm-8">
                            <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Customer:</div>
                        <div class="col-sm-8"><?= htmlspecialchars($order['customer_name']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Email:</div>
                        <div class="col-sm-8"><?= htmlspecialchars($order['customer_email']) ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Date:</div>
                        <div class="col-sm-8"><?= date('F j, Y g:i A', strtotime($order['created_at'])) ?></div>
                    </div>
                    <?php if ($order['request_id']): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted">PlacetoPay Request ID:</div>
                            <div class="col-sm-8"><code><?= $order['request_id'] ?></code></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Items Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bag me-2"></i>Order Items
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                <td class="text-end fw-bold fs-5 text-primary">
                                    $<?= number_format($order['total'], 2) ?> <?= $order['currency'] ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="text-center">
                <a href="/orders" class="btn btn-outline-primary me-2">
                    <i class="bi bi-list me-2"></i>View All Orders
                </a>
                <a href="/products" class="btn btn-primary">
                    <i class="bi bi-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
