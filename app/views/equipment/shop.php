<?php
declare(strict_types=1);
$pageTitle = 'Equipment Shop';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-cart3 me-2"></i>Equipment Shop</h1>
    <p class="text-muted">Browse and purchase gym equipment. Remaining budget: <strong>₱<?= number_format($remaining, 2) ?></strong></p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <?php foreach ($equipment as $item): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100">
            <div class="card-body">
                <h3 class="h6 fw-bold"><?= htmlspecialchars($item['name']) ?></h3>
                <span class="badge bg-info mb-2"><?= htmlspecialchars($item['category'] ?? 'General') ?></span>
                <p class="small text-muted mb-2"><?= htmlspecialchars($item['description'] ?? '') ?></p>
                <p class="small text-muted mb-2">Supplier: <?= htmlspecialchars($item['supplier_name']) ?></p>
                <div class="fw-bold mb-3" style="color:#1B6B2A">₱<?= number_format((float)$item['price'], 2) ?></div>
                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="equipment_id" value="<?= $item['id'] ?>">
                    <input class="form-control form-control-sm" type="number" name="quantity" value="1" min="1" max="100" style="max-width:70px">
                    <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-cart-plus"></i> Buy</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($equipment)): ?>
        <div class="col-12"><div class="card"><div class="card-body text-center py-5 text-muted">No equipment available.</div></div></div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
