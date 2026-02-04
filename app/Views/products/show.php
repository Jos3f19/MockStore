<?php
/**
 * Single Product View
 * Displays detailed information about a product
 */
$pageTitle = $product['name'];
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/products">Products</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>
    
    <div class="row g-5">
        <div class="col-lg-6">
            <div class="bg-white rounded-3 shadow-sm p-3">
                <img src="<?= htmlspecialchars($product['image']) ?>" 
                     class="img-fluid rounded-3" 
                     alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
        </div>
        
        <div class="col-lg-6">
            <h1 class="fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="mb-4">
                <span class="fs-2 fw-bold text-primary">
                    $<?= number_format($product['price'], 2) ?>
                </span>
            </div>
            
            <p class="text-muted mb-4">
                <?= htmlspecialchars($product['description']) ?>
            </p>
            
            <div class="mb-4">
                <span class="badge bg-<?= $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger') ?> fs-6">
                    <i class="bi bi-box-seam me-1"></i>
                    <?php if ($product['stock'] > 0): ?>
                        <?= $product['stock'] ?> in stock
                    <?php else: ?>
                        Out of stock
                    <?php endif; ?>
                </span>
            </div>
            
            <?php if ($product['stock'] > 0): ?>
                <form action="/cart/add" method="POST" class="mb-4">
                    <?= Security::csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    
                    <div class="row g-3 align-items-center">
                        <div class="col-auto">
                            <label class="form-label mb-0">Quantity:</label>
                        </div>
                        <div class="col-auto">
                            <select name="quantity" class="form-select">
                                <?php for ($i = 1; $i <= min(10, $product['stock']); $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-cart-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
            
            <hr class="my-4">
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-truck fs-4 text-primary me-3"></i>
                        <div>
                            <strong>Free Shipping</strong>
                            <br>
                            <small class="text-muted">On orders over $50</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-shield-check fs-4 text-primary me-3"></i>
                        <div>
                            <strong>Secure Payment</strong>
                            <br>
                            <small class="text-muted">PlacetoPay secured</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
