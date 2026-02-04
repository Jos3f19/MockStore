<?php
/**
 * Orders List View
 * Displays all orders with their status
 */
$pageTitle = 'My Orders';
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container py-5">
    <h1 class="fw-bold mb-4">
        <i class="bi bi-receipt me-2"></i>My Orders
    </h1>
    
    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="bi bi-inbox display-1 text-muted"></i>
            </div>
            <h3>No orders yet</h3>
            <p class="text-muted mb-4">You haven't placed any orders yet.</p>
            <a href="/products" class="btn btn-primary btn-lg">
                <i class="bi bi-bag me-2"></i>Start Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Reference</th>
                                <th>Customer</th>
                                <th class="text-center">Status</th>
                                <th class="text-end">Total</th>
                                <th>Date</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                $statusClass = PlacetoPayService::getStatusBadgeClass($order['status']);
                                $statusLabel = PlacetoPayService::getStatusLabel($order['status']);
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <code><?= htmlspecialchars($order['reference']) ?></code>
                                    </td>
                                    <td>
                                        <div><?= htmlspecialchars($order['customer_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($order['customer_email']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                                    </td>
                                    <td class="text-end">
                                        <strong>$<?= number_format($order['total'], 2) ?></strong>
                                        <small class="text-muted d-block"><?= $order['currency'] ?></small>
                                    </td>
                                    <td>
                                        <div><?= date('M j, Y', strtotime($order['created_at'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($order['created_at'])) ?></small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="/order/<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>View
                                        </a>
                                        <?php if ($order['process_url'] && $order['status'] === 'PENDING'): ?>
                                            <a href="<?= htmlspecialchars($order['process_url']) ?>" 
                                               class="btn btn-sm btn-primary"
                                               target="_blank">
                                                <i class="bi bi-credit-card me-1"></i>Pay
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Legend -->
        <div class="mt-4">
            <small class="text-muted">
                <strong>Status Legend:</strong>
                <span class="badge bg-success ms-2">Approved</span>
                <span class="badge bg-warning text-dark ms-2">Pending</span>
                <span class="badge bg-danger ms-2">Rejected</span>
            </small>
        </div>
    <?php endif; ?>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
