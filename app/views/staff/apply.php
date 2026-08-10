<?php
declare(strict_types=1);
$pageTitle = 'Apply as Staff';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <a href="index.php?r=staff/gyms" class="btn btn-outline-secondary btn-sm mb-2">
                <i class="bi bi-arrow-left me-1"></i>Back to Gyms
            </a>
            <h1 class="h3 mb-1"><i class="bi bi-person-badge me-2"></i>Apply for Position</h1>
            <p class="text-muted">Apply for a position at <?= htmlspecialchars($gym['gym_name']) ?>.</p>
        </div>
        <div class="text-end">
            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-sm" title="Refresh page">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">
                Last updated: <?= date('g:i A') ?>
            </small>
        </div>
    </div>
</div>

<!-- Gym Info Card -->
<div class="card mb-4 border-success">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <?php if (!empty($gym['gym_logo'])): ?>
                    <img src="public/<?= htmlspecialchars($gym['gym_logo']) ?>" 
                         alt="<?= htmlspecialchars($gym['gym_name']) ?>" 
                         class="rounded" 
                         style="max-height: 80px; max-width: 80px; object-fit: contain;">
                <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 80px; width: 80px;">
                        <i class="bi bi-building display-6 text-muted"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col">
                <h2 class="h5 mb-1"><?= htmlspecialchars($gym['gym_name']) ?></h2>
                <p class="text-muted small mb-1">
                    <i class="bi bi-pin-map me-1"></i><?= htmlspecialchars($gym['gym_address']) ?>
                </p>
                <p class="text-muted small mb-0">
                    <i class="bi bi-person-badge me-1"></i>Owner: <?= htmlspecialchars($gym['owner_name']) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($staffApp && $staffApp['status'] === 'pending'): ?>
    <div class="card mb-4">
        <div class="card-body text-center py-5">
            <i class="bi bi-hourglass-split display-3 text-warning mb-3"></i>
            <h2 class="h5">Application Under Review</h2>
            <p class="text-muted">Your application for the <strong><?= $staffApp['application_type'] === 'trainer' ? 'Fitness Trainer' : 'Maintenance Officer' ?></strong> position is currently being reviewed by the gym owner.</p>
            <div class="alert alert-info d-inline-block mt-3 mb-0 small">
                <i class="bi bi-info-circle me-1"></i> Ensure you upload your certifications and credentials in your <a href="index.php?r=account/settings" class="alert-link">Profile & Settings</a> so the owner can review them.
            </div>
        </div>
    </div>

<?php elseif ($staffApp && $staffApp['status'] === 'approved'): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle display-3 text-success mb-3"></i>
            <h2 class="h5">Application Approved!</h2>
            <p class="text-muted">You have been approved as a <strong><?= $staffApp['application_type'] === 'trainer' ? 'Fitness Trainer' : 'Maintenance Officer' ?></strong>.</p>
        </div>
    </div>

<?php else: ?>
    <?php if ($staffApp && $staffApp['status'] === 'rejected'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i><strong>Application Rejected:</strong>
            <?= htmlspecialchars($staffApp['feedback'] ?? 'You may submit a new application.') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Apply for Role</h2></div>
        <div class="card-body">
            <form method="post" class="vstack gap-3">
                <div>
                    <label class="form-label" for="application_type">Position <span class="text-danger">*</span></label>
                    <select class="form-select" name="application_type" id="application_type" required onchange="updateButtonLabel()">
                        <option value="">— Select position —</option>
                        <option value="trainer">Fitness Trainer</option>
                        <option value="maintenance">Maintenance Officer</option>
                    </select>
                </div>
                
                <button class="btn btn-primary" type="submit" id="submitBtn">
                    <i class="bi bi-send me-1"></i>Submit Application
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function updateButtonLabel() {
    const select = document.getElementById('application_type');
    const btn = document.getElementById('submitBtn');
    if (!select || !btn) return;
    
    const val = select.value;
    const gymName = <?= json_encode($gym['gym_name']) ?>;
    
    if (val === 'trainer') {
        btn.innerHTML = `<i class="bi bi-send me-1"></i>Apply for Fitness Trainer role at ${gymName}`;
    } else if (val === 'maintenance') {
        btn.innerHTML = `<i class="bi bi-send me-1"></i>Apply for Maintenance Officer role at ${gymName}`;
    } else {
        btn.innerHTML = `<i class="bi bi-send me-1"></i>Submit Application`;
    }
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
