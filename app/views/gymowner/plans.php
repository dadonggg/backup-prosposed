<?php
declare(strict_types=1);
$pageTitle = 'Membership Plans';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-card-list me-2"></i>Membership Plans</h1>
    <p class="text-muted">Create and manage membership plans that fitness enthusiasts can choose from.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add New Plan -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-plus-circle me-1"></i>Add New Plan</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label class="form-label" for="plan_name">Plan Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="plan_name" type="text" name="plan_name" placeholder="e.g. Premium Monthly" required>
                    </div>
                    <div>
                        <label class="form-label" for="plan_desc">Description</label>
                        <textarea class="form-control" id="plan_desc" name="plan_desc" rows="2" placeholder="What's included..."></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="plan_price">Price (₱) <span class="text-danger">*</span></label>
                            <input class="form-control" id="plan_price" type="number" step="0.01" min="1" name="plan_price" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="plan_duration">Duration (days)</label>
                            <input class="form-control" id="plan_duration" type="number" min="1" name="plan_duration" value="30">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle me-1"></i>Create Plan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Existing Plans -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-list-ul me-1"></i>Your Plans</h2></div>
            <div class="card-body p-0">
                <?php if (empty($plans)): ?>
                    <div class="text-center text-muted py-4">No plans yet. Create your first plan!</div>
                <?php else: ?>
                    <?php foreach ($plans as $p): ?>
                    <div class="border-bottom p-3">
                        <form method="post" class="row g-2 align-items-end">
                            <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                            <div class="col-md-4">
                                <label class="form-label small">Name</label>
                                <input class="form-control form-control-sm" type="text" name="plan_name" value="<?= htmlspecialchars($p['name']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Price ₱</label>
                                <input class="form-control form-control-sm" type="number" step="0.01" name="plan_price" value="<?= $p['price'] ?>" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Days</label>
                                <input class="form-control form-control-sm" type="number" name="plan_duration" value="<?= $p['duration_days'] ?>">
                            </div>
                            <div class="col-md-3 d-flex gap-1">
                                <input type="hidden" name="plan_desc" value="<?= htmlspecialchars($p['description'] ?? '') ?>">
                                <button type="submit" name="action" value="update" class="btn btn-outline-primary btn-sm flex-grow-1"><i class="bi bi-pencil"></i> Save</button>
                                <button type="submit" name="action" value="delete" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this plan?')"><i class="bi bi-trash"></i></button>
                            </div>
                        </form>
                        <?php if ($p['description']): ?>
                            <div class="small text-muted mt-1"><?= htmlspecialchars($p['description']) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
