<?php
declare(strict_types=1);
$isEdit = !empty($campaign);
$pageTitle = $isEdit ? 'Edit Campaign' : 'Create Campaign';
require __DIR__ . '/../partials/header.php';
$c = $campaign;
?>

<style>
.text-purple { color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1"><i class="bi bi-<?= $isEdit ? 'pencil-square' : 'plus-circle' ?> me-2 text-purple"></i><?= $pageTitle ?></h1>
        <p class="text-muted mb-0"><?= $isEdit ? 'Update campaign details and settings.' : 'Set up a new advertising campaign for your gym.' ?></p>
    </div>
    <a href="index.php?r=marketing/campaigns" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Back to Campaigns</a>
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
                        <label for="title" class="form-label fw-bold">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Summer Fitness Challenge" value="<?= htmlspecialchars($c['title'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-bold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="5" placeholder="Campaign details and message for members..."><?= htmlspecialchars($c['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="banner_image" class="form-label fw-bold">Banner Image</label>
                        <?php if ($isEdit && !empty($c['image_path'])): ?>
                            <div class="mb-2">
                                <img src="public/<?= htmlspecialchars($c['image_path']) ?>" alt="Current banner" class="img-thumbnail" style="max-height:120px;">
                                <small class="d-block text-muted mt-1">Current banner. Upload a new image to replace it.</small>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="banner_image" id="banner_image" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Accepted formats: JPG, PNG, GIF. Recommended size: 1200×400px.</small>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="target_audience" class="form-label fw-bold">Target Audience</label>
                        <select name="target_audience" id="target_audience" class="form-select">
                            <option value="all_members" <?= ($c['target_audience'] ?? '') === 'all_members' ? 'selected' : '' ?>>All Members</option>
                            <option value="active_members" <?= ($c['target_audience'] ?? '') === 'active_members' ? 'selected' : '' ?>>Active Members</option>
                            <option value="inactive_members" <?= ($c['target_audience'] ?? '') === 'inactive_members' ? 'selected' : '' ?>>Inactive Members</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label fw-bold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="<?= htmlspecialchars($c['start_date'] ?? date('Y-m-d')) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="end_date" class="form-label fw-bold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="<?= htmlspecialchars($c['end_date'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="draft" <?= ($c['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="active" <?= ($c['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        </select>
                        <small class="form-text text-muted">Setting to "Active" will immediately notify all gym members.</small>
                    </div>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-end gap-2">
                <a href="index.php?r=marketing/campaigns" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-purple"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Update Campaign' : 'Create Campaign' ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
