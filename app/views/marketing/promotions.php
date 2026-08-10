<?php
declare(strict_types=1);
$pageTitle = 'Gym Promotions';
require __DIR__ . '/../partials/header.php';
?>

<style>
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
.btn-outline-purple { border-color: #7c3aed !important; color: #7c3aed !important; }
.btn-outline-purple:hover { background-color: #7c3aed !important; color: #fff !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-tags me-2 text-purple"></i>Gym Promotions</h1>
        <p class="text-muted mb-0">Create and manage discounts, promo codes, and special membership offers.</p>
    </div>
    <a href="index.php?r=marketing/createpromotion" class="btn btn-purple">
        <i class="bi bi-plus-circle me-1"></i> Create Promotion
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Title / Code</th>
                        <th>Discount</th>
                        <th>Valid From</th>
                        <th>Valid Until</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($promotions)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">No promotions yet. Click <strong>Create Promotion</strong> to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($promotions as $p): ?>
                        <?php
                            $__st = $p['status'];
                            if ($__st === 'active') { $statusBadge = 'bg-success'; }
                            elseif ($__st === 'expired') { $statusBadge = 'bg-danger'; }
                            else { $statusBadge = 'bg-secondary'; }
                            
                            // Check if expired based on date
                            if ($p['status'] === 'active' && !empty($p['valid_until']) && strtotime($p['valid_until']) < strtotime(date('Y-m-d'))) {
                                $statusBadge = 'bg-danger';
                                $p['status'] = 'expired (auto)';
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($p['title']) ?></div>
                                <code class="small text-purple fw-bold bg-light px-2 py-1 rounded"><?= htmlspecialchars($p['promo_code']) ?></code>
                            </td>
                            <td>
                                <?php if ($p['discount_type'] === 'percentage'): ?>
                                    <span class="fw-bold text-success"><?= number_format((float)$p['discount_value'], 0) ?>% Off</span>
                                <?php else: ?>
                                    <span class="fw-bold text-success">₱<?= number_format((float)$p['discount_value'], 2) ?> Off</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= htmlspecialchars($p['valid_from']) ?></td>
                            <td class="small"><?= htmlspecialchars($p['valid_until']) ?></td>
                            <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($p['status']) ?></span></td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1 flex-wrap">
                                    <a href="index.php?r=marketing/editpromotion&id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i></a>
                                    
                                    <?php if ($p['status'] === 'draft'): ?>
                                        <form method="POST" action="index.php?r=marketing/promotions" class="d-inline" onsubmit="return confirm('Activate this promotion and notify all gym members?')">
                                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="btn btn-sm btn-success" title="Activate"><i class="bi bi-play-fill"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" action="index.php?r=marketing/promotions" class="d-inline" onsubmit="return confirm('Delete this promotion permanently?')">
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
