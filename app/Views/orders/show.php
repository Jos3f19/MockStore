<?php
/**
 * Single Order View
 * Displays detailed order information
 */
$pageTitle = 'Order ' . $order['reference'];
include BASE_PATH . '/app/Views/layouts/header.php';

$statusClass = PlacetoPayService::getStatusBadgeClass($order['status']);
$statusLabel = PlacetoPayService::getStatusLabel($order['status']);
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/orders">Orders</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($order['reference']) ?></li>
        </ol>
    </nav>
    
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Order Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>Order <?= htmlspecialchars($order['reference']) ?>
                    </h5>
                    <span class="badge <?= $statusClass ?> status-badge"><?= $statusLabel ?></span>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Order Information</h6>
                            <p class="mb-2">
                                <strong>Reference:</strong> 
                                <code><?= htmlspecialchars($order['reference']) ?></code>
                            </p>
                            <p class="mb-2">
                                <strong>Created:</strong> 
                                <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?>
                            </p>
                            <?php if ($order['updated_at'] !== $order['created_at']): ?>
                                <p class="mb-2">
                                    <strong>Last Updated:</strong> 
                                    <?= date('F j, Y \a\t g:i A', strtotime($order['updated_at'])) ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($order['status_message']): ?>
                                <p class="mb-0">
                                    <strong>Status Message:</strong><br>
                                    <span class="text-muted"><?= htmlspecialchars($order['status_message']) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Customer Information</h6>
                            <p class="mb-2">
                                <strong>Name:</strong> <?= htmlspecialchars($order['customer_name']) ?>
                            </p>
                            <p class="mb-2">
                                <strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?>
                            </p>
                            <?php if ($order['customer_phone']): ?>
                                <p class="mb-2">
                                    <strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone']) ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($order['customer_document']): ?>
                                <p class="mb-0">
                                    <strong>Document:</strong> <?= htmlspecialchars($order['customer_document']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
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
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($item['product_name']) ?>
                                        <small class="text-muted d-block">SKU: <?= $item['product_id'] ?></small>
                                    </td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end">$<?= number_format($item['price'], 2) ?></td>
                                    <td class="text-end fw-bold">$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
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
        </div>
        
        <div class="col-lg-4">
            <!-- PlacetoPay Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-credit-card me-2"></i>Payment Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Gateway:</strong> PlacetoPay WebCheckout
                    </p>
                    <?php if ($order['request_id']): ?>
                        <p class="mb-2">
                            <strong>Request ID:</strong><br>
                            <code class="small"><?= $order['request_id'] ?></code>
                        </p>
                    <?php endif; ?>
                    <p class="mb-2">
                        <strong>Amount:</strong> 
                        <span class="fs-5 fw-bold text-primary">
                            $<?= number_format($order['total'], 2) ?> <?= $order['currency'] ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Status:</strong><br>
                        <span class="badge <?= $statusClass ?> mt-1"><?= $statusLabel ?></span>
                    </p>
                    
                    <?php if ($order['process_url'] && $order['status'] === 'PENDING'): ?>
                        <hr>
                        <a href="<?= htmlspecialchars($order['process_url']) ?>" 
                           class="btn btn-primary w-100"
                           target="_blank">
                            <i class="bi bi-credit-card me-2"></i>Complete Payment
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Technical Details (for debugging) -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-code-slash me-2"></i>Technical Details
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-2 small">
                        <strong>IP Address:</strong><br>
                        <code><?= htmlspecialchars($order['ip_address'] ?? 'N/A') ?></code>
                    </p>
                    <p class="mb-0 small">
                        <strong>User Agent:</strong><br>
                        <code class="text-break"><?= htmlspecialchars(substr($order['user_agent'] ?? 'N/A', 0, 100)) ?>...</code>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="/orders" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Orders
        </a>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
