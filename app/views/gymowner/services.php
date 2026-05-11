<?php
declare(strict_types=1);
$pageTitle = 'Gym Services';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-tags me-2"></i>Gym Services</h1>
    <p class="text-muted">Manage additional services (Personal Training, Fitness Sessions, etc.) with separate member/non-member pricing.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Add New Service -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-plus-circle me-1"></i>Add Service</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <input type="hidden" name="action" value="create">
                    <div>
                        <label class="form-label" for="svc_name">Service Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="svc_name" type="text" name="svc_name" placeholder="e.g. Personal Training" required>
                    </div>
                    <div>
                        <label class="form-label" for="svc_desc">Description</label>
                        <textarea class="form-control" id="svc_desc" name="svc_desc" rows="2" placeholder="Service details..."></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label" for="svc_member_price">Member Price (₱) <span class="text-danger">*</span></label>
                            <input class="form-control" id="svc_member_price" type="number" step="0.01" min="1" name="svc_member_price" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="svc_nonmember_price">Non-Member Price (₱)</label>
                            <input class="form-control" id="svc_nonmember_price" type="number" step="0.01" min="0" name="svc_nonmember_price" value="0">
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-plus-circle me-1"></i>Add Service</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Existing Services -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-list-ul me-1"></i>Your Services</h2></div>
            <div class="card-body p-0">
                <?php if (empty($services)): ?>
                    <div class="text-center text-muted py-4">No services yet. Add one to get started!</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Service</th><th>Member ₱</th><th>Non-Member ₱</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($services as $s): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($s['name']) ?></strong>
                                        <?php if ($s['description']): ?><br><small class="text-muted"><?= htmlspecialchars($s['description']) ?></small><?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="svc_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="svc_name" value="<?= htmlspecialchars($s['name']) ?>">
                                            <input type="hidden" name="svc_desc" value="<?= htmlspecialchars($s['description'] ?? '') ?>">
                                            <input type="hidden" name="svc_nonmember_price" value="<?= $s['non_member_price'] ?>">
                                            <input class="form-control form-control-sm d-inline-block" style="width:90px" type="number" step="0.01" name="svc_member_price" value="<?= $s['member_price'] ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-check"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="svc_id" value="<?= $s['id'] ?>">
                                            <input type="hidden" name="svc_name" value="<?= htmlspecialchars($s['name']) ?>">
                                            <input type="hidden" name="svc_desc" value="<?= htmlspecialchars($s['description'] ?? '') ?>">
                                            <input type="hidden" name="svc_member_price" value="<?= $s['member_price'] ?>">
                                            <input class="form-control form-control-sm d-inline-block" style="width:90px" type="number" step="0.01" name="svc_nonmember_price" value="<?= $s['non_member_price'] ?>">
                                            <button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-check"></i></button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="post" class="d-inline">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="svc_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this service?')"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
