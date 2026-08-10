<?php
declare(strict_types=1);
$pageTitle = 'Fitness Trainer Pricing';
require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">
            <i class="bi bi-currency-dollar me-2"></i>Fitness Trainer Pricing
        </h1>
        <p class="text-muted mb-0">Manage training packages and pricing for your gym</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
        <i class="bi bi-plus-circle me-1"></i>Add Package
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="bi bi-list-ul me-2"></i>Training Packages
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($packages)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h5 class="text-muted mt-3">No packages yet</h5>
            <p class="text-muted">Create your first training package to get started</p>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                <i class="bi bi-plus-circle me-1"></i>Add First Package
            </button>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>Training Type</th>
                        <th>Sessions</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($packages as $package): 
                        $trainingTypeLabel = [
                            'personal_training' => 'Personal Training',
                            'pilates' => 'Pilates',
                            'yoga' => 'Yoga',
                        ][$package['training_type']] ?? 'All Types';
                        $statusColor = $package['is_active'] ? 'success' : 'secondary';
                        $statusLabel = $package['is_active'] ? 'Active' : 'Inactive';
                    ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($package['package_name']) ?></strong>
                            <?php if ($package['description']): ?>
                            <br><small class="text-muted"><?= htmlspecialchars($package['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $trainingTypeLabel ?></span>
                        </td>
                        <td>
                            <strong><?= $package['session_count'] ?></strong> sessions
                        </td>
                        <td>
                            <?= $package['duration_minutes'] ?> min
                        </td>
                        <td>
                            <strong class="text-success">₱<?= number_format((float)$package['price'], 2) ?></strong>
                            <br><small class="text-muted">₱<?= number_format((float)$package['price'] / $package['session_count'], 2) ?>/session</small>
                        </td>
                        <td>
                            <span class="badge bg-<?= $statusColor ?>"><?= $statusLabel ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editPackage(<?= htmlspecialchars(json_encode($package)) ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="post" class="d-inline" onsubmit="return confirm('Toggle package status?')">
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                    <button type="submit" class="btn btn-outline-warning">
                                        <i class="bi bi-toggle-<?= $package['is_active'] ? 'on' : 'off' ?>"></i>
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Delete this package? This cannot be undone.')">
                                    <input type="hidden" name="action" value="delete_package">
                                    <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Package Modal -->
<div class="modal fade" id="addPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-plus-circle me-2"></i>Add Training Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_package">
                    
                    <div class="mb-3">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="package_name" class="form-control" required 
                               placeholder="e.g., Starter Package, Premium Package">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Training Type</label>
                        <select name="training_type" class="form-select">
                            <option value="all">All Training Types</option>
                            <option value="personal_training">Personal Training Only</option>
                            <option value="pilates">Pilates Only</option>
                            <option value="yoga">Yoga Only</option>
                        </select>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Number of Sessions <span class="text-danger">*</span></label>
                            <input type="number" name="session_count" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" class="form-control" value="60" min="30" max="180">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="price" class="form-control" step="0.01" min="0" required 
                               placeholder="0.00">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Describe what's included in this package..."></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="is_active_add" checked>
                        <label class="form-check-label" for="is_active_add">
                            Active (visible to members)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Add Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Training Package
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_package">
                    <input type="hidden" name="package_id" id="edit_package_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" name="package_name" id="edit_package_name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Training Type</label>
                        <select name="training_type" id="edit_training_type" class="form-select">
                            <option value="all">All Training Types</option>
                            <option value="personal_training">Personal Training Only</option>
                            <option value="pilates">Pilates Only</option>
                            <option value="yoga">Yoga Only</option>
                        </select>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Number of Sessions <span class="text-danger">*</span></label>
                            <input type="number" name="session_count" id="edit_session_count" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" id="edit_duration_minutes" class="form-control" min="30" max="180">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Price (₱) <span class="text-danger">*</span></label>
                        <input type="number" name="price" id="edit_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" id="edit_is_active">
                        <label class="form-check-label" for="edit_is_active">
                            Active (visible to members)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Update Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPackage(pkg) {
    document.getElementById('edit_package_id').value = pkg.id;
    document.getElementById('edit_package_name').value = pkg.package_name;
    document.getElementById('edit_training_type').value = pkg.training_type;
    document.getElementById('edit_session_count').value = pkg.session_count;
    document.getElementById('edit_duration_minutes').value = pkg.duration_minutes;
    document.getElementById('edit_price').value = pkg.price;
    document.getElementById('edit_description').value = pkg.description || '';
    document.getElementById('edit_is_active').checked = pkg.is_active == 1;
    
    new bootstrap.Modal(document.getElementById('editPackageModal')).show();
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
