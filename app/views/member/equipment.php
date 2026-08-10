<?php
declare(strict_types=1);
$pageTitle = 'Available Equipment';
require __DIR__ . '/../partials/header.php';

$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'Member';
?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1">
                <i class="bi bi-tools me-2"></i>Available Equipment
            </h1>
            <p class="text-muted mb-0">Browse equipment available at your gym</p>
        </div>
        <a href="index.php?r=member/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
    </div>
</div>

<!-- Equipment Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-tools text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Total Equipment</div>
                        <div class="fs-4 fw-bold text-primary"><?= $totalEquipment ?></div>
                        <div class="small text-muted">Items available</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-grid-3x3 text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Categories</div>
                        <div class="fs-4 fw-bold text-success"><?= count($categories) ?></div>
                        <div class="small text-muted">Equipment types</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-check-circle text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Status</div>
                        <div class="fs-4 fw-bold text-info">Active</div>
                        <div class="small text-muted">All equipment ready</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($equipment)): ?>
<!-- No Equipment Message -->
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <i class="bi bi-tools text-muted" style="font-size: 4rem;"></i>
        </div>
        <h5 class="text-muted mb-2">No Equipment Available</h5>
        <p class="text-muted mb-0">Your gym hasn't added any equipment to the inventory yet.</p>
    </div>
</div>
<?php else: ?>

<!-- Category Filter Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-sm btn-primary active" data-filter="all">
                All Equipment (<?= $totalEquipment ?>)
            </button>
            <?php foreach ($categories as $category => $count): ?>
            <button class="btn btn-sm btn-outline-primary" data-filter="<?= htmlspecialchars(strtolower(str_replace(' ', '-', $category))) ?>">
                <?= htmlspecialchars($category) ?> (<?= $count ?>)
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Equipment Grid -->
<div class="row g-4" id="equipmentGrid">
    <?php foreach ($equipment as $item): 
        $categoryClass = strtolower(str_replace(' ', '-', $item['category'] ?? 'other'));
        $quantity = (int)($item['quantity'] ?? 0);
        $equipmentName = htmlspecialchars($item['equipment_name'] ?? 'Unknown Equipment');
        $category = htmlspecialchars($item['category'] ?? 'Other');
        $supplierName = htmlspecialchars($item['supplier_name'] ?? 'Unknown Supplier');
        $purchasedDate = date('M j, Y', strtotime($item['purchased_at'] ?? 'now'));
        $totalCost = number_format((float)($item['total_cost'] ?? 0), 2);
    ?>
    <div class="col-md-6 col-lg-4 equipment-item" data-category="<?= $categoryClass ?>">
        <div class="card h-100 border-0 shadow-sm hover-shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1"><?= $equipmentName ?></h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            <?= $category ?>
                        </span>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-success">
                            <?= $quantity ?> Available
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="bi bi-building me-2"></i>
                        <span>Supplier: <?= $supplierName ?></span>
                    </div>
                    <div class="d-flex align-items-center text-muted small mb-2">
                        <i class="bi bi-calendar3 me-2"></i>
                        <span>Added: <?= $purchasedDate ?></span>
                    </div>
                    <div class="d-flex align-items-center text-muted small">
                        <i class="bi bi-cash me-2"></i>
                        <span>Value: ₱<?= $totalCost ?></span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-muted small">
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Ready to use
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('[data-filter]');
    const equipmentItems = document.querySelectorAll('.equipment-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');

            // Update active button
            filterButtons.forEach(btn => {
                btn.classList.remove('btn-primary', 'active');
                btn.classList.add('btn-outline-primary');
            });
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary', 'active');

            // Filter equipment items
            equipmentItems.forEach(item => {
                if (filter === 'all' || item.getAttribute('data-category') === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });

    // Add transition styles
    equipmentItems.forEach(item => {
        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
    });
});
</script>

<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
