<?php
/**
 * Products List View
 * Displays all available products
 */
$pageTitle = 'Products';
include BASE_PATH . '/app/Views/layouts/header.php';
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Our Products</h1>
        <p class="text-muted">Browse our collection of premium products</p>
    </div>
    
    <div class="row g-4">
        <?php foreach ($products as $product): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card product-card h-100 shadow-sm">
                    <img src="<?= htmlspecialchars($product['image']) ?>" 
                         class="card-img-top" 
                         alt="<?= htmlspecialchars($product['name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                        <p class="card-text text-muted small flex-grow-1">
                            <?= htmlspecialchars($product['description']) ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="fs-4 fw-bold text-primary">
                                $<?= number_format($product['price'], 2) ?>
                            </span>
                            <div class="btn-group">
                                <a href="/product/<?= $product['id'] ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="/cart/add" method="POST" class="d-inline">
                                    <?= Security::csrfField() ?>
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i> Add
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-3">
                        <small class="text-muted">
                            <i class="bi bi-box-seam me-1"></i>
                            <?= $product['stock'] ?> in stock
                        </small>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include BASE_PATH . '/app/Views/layouts/footer.php'; ?>
