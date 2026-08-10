<?php
declare(strict_types=1);
$isEdit = !empty($promotion);
$pageTitle = $isEdit ? 'Edit Promotion' : 'Create Promotion';
require __DIR__ . '/../partials/header.php';
$p = $promotion;
?>

<style>
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2 text-purple"></i><?= $pageTitle ?></h1>
        <p class="text-muted mb-0"><?= $isEdit ? 'Update promotion details and settings.' : 'Set up a new discount or promo code for your members.' ?></p>
    </div>
    <a href="index.php?r=marketing/promotions" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Promotions</a>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-bold">Promotion Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. 15% Off Annual Membership" value="<?= htmlspecialchars($p['title'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Explain the promo details, terms and conditions..."><?= htmlspecialchars($p['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="promo_image" class="form-label fw-bold">Promotion Banner / Image</label>
                        <?php if ($isEdit && !empty($p['image_path'])): ?>
                            <div class="mb-2">
                                <img src="public/<?= htmlspecialchars($p['image_path']) ?>" alt="Current promo image" class="img-thumbnail" style="max-height:120px;">
                                <small class="d-block text-muted mt-1">Current image. Upload a new image to replace it.</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="promo_image" id="promo_image" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="promo_code" class="form-label fw-bold">Promo Code <span class="text-danger">*</span></label>
                        <input type="text" name="promo_code" id="promo_code" class="form-control" placeholder="e.g. FIT15" value="<?= htmlspecialchars($p['promo_code'] ?? '') ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label for="discount_type" class="form-label fw-bold">Type</label>
                            <select name="discount_type" id="discount_type" class="form-select">
                                <option value="percentage" <?= ($p['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                <option value="fixed" <?= ($p['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed (₱)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="discount_value" class="form-label fw-bold">Value <span class="text-danger">*</span></label>
                            <input type="number" name="discount_value" id="discount_value" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($p['discount_value'] ?? '0.00') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="valid_from" class="form-label fw-bold">Valid From <span class="text-danger">*</span></label>
                        <input type="date" name="valid_from" id="valid_from" class="form-control" value="<?= htmlspecialchars($p['valid_from'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="valid_until" class="form-label fw-bold">Valid Until <span class="text-danger">*</span></label>
                        <input type="date" name="valid_until" id="valid_until" class="form-control" value="<?= htmlspecialchars($p['valid_until'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="draft" <?= ($p['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= ($p['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        </select>
                        <small class="form-text text-muted">Setting to "Active" will notify all gym members immediately.</small>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?r=marketing/promotions" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-purple"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Promotion' : 'Create Promotion' ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
