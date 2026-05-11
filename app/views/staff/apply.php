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
            <h1 class="h3 mb-1"><i class="bi bi-person-badge me-2"></i>Apply as Staff</h1>
            <p class="text-muted">Submit your application to join <?= htmlspecialchars($gym['gym_name']) ?>.</p>
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
<div class="card mb-4 border-primary">
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
            <p class="text-muted">Your application as <strong><?= ucfirst($staffApp['application_type']) ?></strong> is being reviewed.</p>
        </div>
    </div>

    <!-- Show per-document status with resubmit option -->
    <?php
    $docFields = ['medical_certificate' => 'Medical Certificate', 'resume' => 'Resume / CV'];
    $hasDocStatus = isset($staffApp['medical_certificate_status']);
    $hasFlaggedDocs = false;
    
    if ($hasDocStatus) {
        foreach ($docFields as $key => $label) {
            if (($staffApp[$key . '_status'] ?? 'pending') === 'flagged') {
                $hasFlaggedDocs = true;
                break;
            }
        }
    }
    ?>

    <?php if ($hasDocStatus): ?>
    <div class="card">
        <div class="card-header px-3 py-2">
            <h3 class="h6 mb-0"><i class="bi bi-file-earmark-check me-2"></i>Document Status</h3>
        </div>
        <div class="card-body">
            <?php if ($hasFlaggedDocs): ?>
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-exclamation-triangle me-1"></i><strong>Some documents need attention.</strong>
                    Please review the feedback below and resubmit the flagged documents.
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <?php foreach ($docFields as $key => $label):
                    $docStatus = $staffApp[$key . '_status'] ?? 'pending';
                    $docComment = $staffApp[$key . '_comment'] ?? '';

                    $statusBadge = match($docStatus) {
                        'approved' => 'bg-success',
                        'flagged' => 'bg-danger',
                        default => 'bg-warning text-dark'
                    };
                    $statusIcon = match($docStatus) {
                        'approved' => 'bi-check-circle-fill text-success',
                        'flagged' => 'bi-exclamation-triangle-fill text-danger',
                        default => 'bi-clock-fill text-warning'
                    };
                    $statusLabel = match($docStatus) {
                        'approved' => 'Approved',
                        'flagged' => 'Flagged',
                        default => 'Pending'
                    };
                ?>
                <div class="col-md-6">
                    <div class="card h-100" style="border-color:<?= $docStatus === 'flagged' ? 'rgba(220,53,69,.4)' : ($docStatus === 'approved' ? 'rgba(25,135,84,.3)' : 'rgba(255,193,7,.3)') ?>!important">
                        <div class="card-header px-3 py-2 d-flex align-items-center justify-content-between">
                            <h4 class="h6 mb-0"><i class="<?= $statusIcon ?> me-1"></i><?= $label ?></h4>
                            <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($staffApp[$key])): ?>
                                <a href="public/<?= htmlspecialchars($staffApp[$key]) ?>" target="_blank" class="btn btn-outline-info btn-sm mb-2">
                                    <i class="bi bi-file-earmark"></i> View Current Document
                                </a>
                            <?php endif; ?>

                            <?php if ($docComment !== ''): ?>
                                <div class="alert alert-danger small py-2 px-3 mb-2">
                                    <i class="bi bi-chat-left-text me-1"></i><strong>Issue:</strong> <?= htmlspecialchars($docComment) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($docStatus === 'flagged'): ?>
                                <!-- Resubmit form for this specific document -->
                                <form method="post" enctype="multipart/form-data" class="mt-2">
                                    <input type="hidden" name="action" value="resubmit_doc">
                                    <input type="hidden" name="doc_field" value="<?= $key ?>">
                                    <div class="mb-2">
                                        <label class="form-label small fw-bold">Upload corrected <?= $label ?></label>
                                        <input class="form-control form-control-sm" type="file" name="<?= $key ?>" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                    </div>
                                    <button class="btn btn-warning btn-sm" type="submit">
                                        <i class="bi bi-arrow-repeat me-1"></i>Resubmit <?= $label ?>
                                    </button>
                                </form>
                            <?php elseif ($docStatus === 'approved'): ?>
                                <div class="small text-success mt-2">
                                    <i class="bi bi-check-circle me-1"></i>This document has been approved. No action needed.
                                </div>
                            <?php else: ?>
                                <div class="small text-muted mt-2">
                                    <i class="bi bi-clock me-1"></i>Awaiting review by gym owner.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php elseif ($staffApp && $staffApp['status'] === 'approved'): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle display-3 text-success mb-3"></i>
            <h2 class="h5">Application Approved!</h2>
            <p class="text-muted">You have been approved as a <strong><?= ucfirst($staffApp['application_type']) ?></strong>.</p>
        </div>
    </div>

<?php elseif ($staffApp && $staffApp['status'] === 'resubmit'): ?>
    <!-- RESUBMIT: Show what's wrong and allow per-document resubmission -->
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i><strong>Some documents need attention.</strong>
        Please review the feedback below and resubmit the flagged documents.
    </div>

    <?php if (!empty($staffApp['feedback'])): ?>
        <div class="alert alert-info">
            <i class="bi bi-chat-left-text me-1"></i><strong>Reviewer Feedback:</strong><br>
            <?= htmlspecialchars($staffApp['feedback']) ?>
        </div>
    <?php endif; ?>

    <?php
    $docFields = ['medical_certificate' => 'Medical Certificate', 'resume' => 'Resume / CV'];
    ?>

    <div class="row g-4">
        <?php foreach ($docFields as $key => $label):
            $docStatus = $staffApp[$key . '_status'] ?? 'pending';
            $docComment = $staffApp[$key . '_comment'] ?? '';

            $statusBadge = match($docStatus) {
                'approved' => 'bg-success',
                'flagged' => 'bg-danger',
                default => 'bg-warning text-dark'
            };
            $statusIcon = match($docStatus) {
                'approved' => 'bi-check-circle-fill text-success',
                'flagged' => 'bi-exclamation-triangle-fill text-danger',
                default => 'bi-clock-fill text-warning'
            };
        ?>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header px-3 py-2 d-flex align-items-center justify-content-between">
                    <h3 class="h6 mb-0"><i class="<?= $statusIcon ?> me-1"></i><?= $label ?></h3>
                    <span class="badge <?= $statusBadge ?>"><?= ucfirst($docStatus) ?></span>
                </div>
                <div class="card-body">
                    <?php if (!empty($app[$key] ?? $staffApp[$key])): ?>
                        <a href="public/<?= htmlspecialchars($staffApp[$key]) ?>" target="_blank" class="btn btn-outline-info btn-sm mb-2">
                            <i class="bi bi-file-earmark"></i> View Current Document
                        </a>
                    <?php endif; ?>

                    <?php if ($docComment !== ''): ?>
                        <div class="alert alert-danger small py-2 px-3 mb-2">
                            <i class="bi bi-chat-left-text me-1"></i><strong>Issue:</strong> <?= htmlspecialchars($docComment) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($docStatus === 'flagged'): ?>
                        <!-- Resubmit form for this specific document -->
                        <form method="post" enctype="multipart/form-data" class="mt-2">
                            <input type="hidden" name="action" value="resubmit_doc">
                            <input type="hidden" name="doc_field" value="<?= $key ?>">
                            <div class="mb-2">
                                <label class="form-label small fw-bold">Upload corrected <?= $label ?></label>
                                <input class="form-control form-control-sm" type="file" name="<?= $key ?>" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            </div>
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-cloud-upload me-1"></i>Resubmit <?= $label ?>
                            </button>
                        </form>
                    <?php elseif ($docStatus === 'approved'): ?>
                        <div class="small text-success mt-2"><i class="bi bi-check-circle me-1"></i>This document has been approved. No action needed.</div>
                    <?php else: ?>
                        <div class="small text-muted mt-2"><i class="bi bi-clock me-1"></i>Awaiting review.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Option to resubmit all documents -->
    <div class="card mt-4">
        <div class="card-header px-3 py-2"><h3 class="h6 mb-0">Or Resubmit All Documents</h3></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="vstack gap-3">
                <input type="hidden" name="action" value="submit">
                <div>
                    <label class="form-label" for="application_type">Position</label>
                    <select class="form-select" name="application_type" id="application_type" required>
                        <option value="maintenance" <?= $staffApp['application_type'] === 'maintenance' ? 'selected' : '' ?>>Maintenance Officer</option>
                        <option value="trainer" <?= $staffApp['application_type'] === 'trainer' ? 'selected' : '' ?>>Fitness Trainer</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="medical_certificate">Medical Certificate <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="medical_certificate" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
                <div>
                    <label class="form-label" for="resume">Resume / CV <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="resume" name="resume" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-cloud-upload me-1"></i>Resubmit All Documents</button>
            </form>
        </div>
    </div>

<?php else: ?>
    <?php if ($staffApp && $staffApp['status'] === 'rejected'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i><strong>Application Rejected:</strong>
            <?= htmlspecialchars($staffApp['feedback'] ?? 'You may resubmit with updated documents.') ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Staff Application Form</h2></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="vstack gap-3">
                <input type="hidden" name="action" value="submit">
                <div>
                    <label class="form-label" for="application_type">Position <span class="text-danger">*</span></label>
                    <select class="form-select" name="application_type" id="application_type" required>
                        <option value="">— Select position —</option>
                        <option value="maintenance">Maintenance Officer</option>
                        <option value="trainer">Fitness Trainer</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="medical_certificate">Medical Certificate <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="medical_certificate" name="medical_certificate" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
                <div>
                    <label class="form-label" for="resume">Resume / CV <span class="text-danger">*</span></label>
                    <input class="form-control" type="file" id="resume" name="resume" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-cloud-upload me-1"></i>Submit Application</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
// Auto-refresh functionality for staff application page
let lastCheck = Date.now();

// Check if we should refresh based on URL parameter
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('refresh') === '1') {
    // Remove the refresh parameter and reload
    const cleanUrl = window.location.pathname + '?r=staff/apply&gym_id=<?= $gym['gym_owner_id'] ?? '' ?>';
    window.history.replaceState({}, document.title, cleanUrl);
}

// Listen for notification clicks
document.addEventListener('click', function(e) {
    const notifLink = e.target.closest('a[href*="staff/apply"]');
    if (notifLink) {
        // Add refresh parameter to force reload
        e.preventDefault();
        window.location.href = notifLink.href + (notifLink.href.includes('?') ? '&' : '?') + 'refresh=1';
    }
});

// Auto-refresh when page regains focus (user comes back from another tab)
let pageHidden = false;
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        pageHidden = true;
    } else if (pageHidden) {
        // Page became visible again, check if we should refresh
        const timeSinceLastCheck = Date.now() - lastCheck;
        if (timeSinceLastCheck > 5000) { // 5 seconds
            // Silently reload the page to get fresh data
            window.location.reload();
        }
        pageHidden = false;
    }
});

// Periodic check for updates (every 30 seconds)
setInterval(function() {
    // Check if there are unread notifications
    const unreadBadge = document.querySelector('.notification-badge');
    if (unreadBadge && parseInt(unreadBadge.textContent) > 0) {
        // Show a subtle notification that updates are available
        const existingBanner = document.getElementById('update-banner');
        if (!existingBanner) {
            const banner = document.createElement('div');
            banner.id = 'update-banner';
            banner.className = 'alert alert-info alert-dismissible fade show position-fixed';
            banner.style.cssText = 'top: 80px; right: 20px; z-index: 1050; max-width: 350px;';
            banner.innerHTML = `
                <i class="bi bi-info-circle me-2"></i>
                <strong>Updates Available</strong><br>
                <small>Your document status may have changed. <a href="javascript:window.location.reload()" class="alert-link">Refresh page</a></small>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(banner);
        }
    }
}, 30000); // Check every 30 seconds
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
