<?php
declare(strict_types=1);
$pageTitle = 'Fitness Enthusiast Dashboard';
require __DIR__ . '/../partials/header.php';
$displayName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? ''));
if ($displayName === '') $displayName = $user['fullname'] ?? 'User';
?>

<div class="mb-4">
    <h1 class="h3 mb-1">Welcome, <?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?>!</h1>
    <p class="text-muted mb-0">Fitness Enthusiast Dashboard</p>
</div>

<?php
// Gym owner app status helpers
$gymDocLabels = [
    'cert_registration'  => 'Certificate of Registration',
    'mayors_permit'      => "Mayor's Permit",
    'business_name_cert' => 'Business Name Certificate',
    'fire_safety_cert'   => 'Fire Safety Certificate',
];
$gymAppStatus = $legalDoc['status'] ?? null;
$gymStatusColor = match($gymAppStatus) {
    'verified'  => 'success',
    'resubmit'  => 'danger',
    'rejected'  => 'danger',
    'pending'   => 'warning',
    default     => 'secondary',
};
?>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <a href="index.php?r=gymowner/apply" class="text-decoration-none">
        <div class="stat-card p-3 h-100" style="<?= $legalDoc ? 'border-left:3px solid var(--bs-'.$gymStatusColor.')!important;' : '' ?>">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-25 text-primary"><i class="bi bi-building"></i></div>
                <div>
                    <div class="text-muted small">Gym Owner App</div>
                    <div class="fw-bold text-<?= $gymStatusColor ?>"><?= $legalDoc ? ucfirst($gymAppStatus) : 'Not Applied' ?></div>
                    <?php if ($legalDoc): ?>
                    <div class="text-muted" style="font-size:.68rem"><i class="bi bi-eye me-1"></i>View document status</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </a>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-25 text-warning"><i class="bi bi-person-badge"></i></div>
                <div>
                    <div class="text-muted small">Staff Application</div>
                    <div class="fw-bold"><?= $staffApp ? ucfirst($staffApp['status']) . ' (' . ucfirst($staffApp['application_type']) . ')' : 'Not Applied' ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-25 text-success"><i class="bi bi-card-checklist"></i></div>
                <div>
                    <div class="text-muted small">Membership</div>
                    <div class="fw-bold"><?= $memberApp ? ucfirst($memberApp['status']) : 'Not Applied' ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3">
        <div class="stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-25 text-info"><i class="bi bi-qr-code"></i></div>
                <div>
                    <div class="text-muted small">Member Code</div>
                    <div class="fw-bold"><?= $gymMember ? htmlspecialchars($gymMember['membership_code']) : '—' ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notifications / Feedback Section -->
<?php
$notifications = [];

// ── Gym Owner Application Notifications ──────────────────────────────────────
if ($legalDoc && $legalDoc['status'] === 'verified') {
    $notifications[] = ['type' => 'success', 'icon' => 'bi-building-check',
        'title' => 'Gym Owner Application — All Documents Approved!',
        'msg'   => 'All your legal documents have been verified. The admin will finalize your gym owner account shortly.',
        'link'  => 'index.php?r=gymowner/apply', 'linkText' => 'View Status'];
}
if ($legalDoc && $legalDoc['status'] === 'resubmit') {
    // Build per-doc detail for feedback
    $flaggedDocs = [];
    foreach ($gymDocLabels as $key => $label) {
        if (($legalDoc[$key.'_status'] ?? 'pending') === 'flagged') {
            $comment = $legalDoc[$key.'_comment'] ?? '';
            $flaggedDocs[] = '• ' . $label . ($comment !== '' ? ': ' . $comment : '');
        }
    }
    $msg = !empty($flaggedDocs)
        ? 'The following document(s) were flagged — ' . implode(' | ', $flaggedDocs)
        : ($legalDoc['admin_feedback'] ?? 'Please resubmit your documents.');
    $notifications[] = ['type' => 'warning', 'icon' => 'bi-exclamation-triangle',
        'title' => 'Gym Owner Application — Action Required: Documents Flagged',
        'msg'   => $msg,
        'link'  => 'index.php?r=gymowner/apply', 'linkText' => 'Resubmit Now'];
}
if ($legalDoc && $legalDoc['status'] === 'rejected') {
    $notifications[] = ['type' => 'danger', 'icon' => 'bi-building-x',
        'title' => 'Gym Owner Application — Rejected',
        'msg'   => $legalDoc['admin_feedback'] ?? 'Your application has been rejected.',
        'link'  => 'index.php?r=gymowner/apply', 'linkText' => 'Re-apply'];
}
if ($legalDoc && $legalDoc['status'] === 'pending') {
    // Check if any individual docs are already approved or flagged
    $anyIndivAction = false;
    foreach ($gymDocLabels as $key => $label) {
        $s = $legalDoc[$key.'_status'] ?? 'pending';
        if ($s === 'approved' || $s === 'flagged') { $anyIndivAction = true; break; }
    }
    if ($anyIndivAction) {
        $notifications[] = ['type' => 'info', 'icon' => 'bi-building',
            'title' => 'Gym Owner Application — Documents Being Reviewed',
            'msg'   => 'The admin is reviewing your documents. Some have been marked already. Click to see the full status.',
            'link'  => 'index.php?r=gymowner/apply', 'linkText' => 'View Document Status'];
    }
}

// ── Staff Application Notifications ──────────────────────────────────────────
if ($staffApp && $staffApp['status'] === 'approved') {
    $notifications[] = ['type' => 'success', 'icon' => 'bi-person-check',
        'title' => 'Staff Application Approved — You are Hired!',
        'msg'   => 'Congratulations! You have been approved. Check your dashboard for your new role.',
        'link'  => 'index.php?r=home/index', 'linkText' => 'Go to Dashboard'];
}
if ($staffApp && $staffApp['status'] === 'resubmit') {
    // Build per-doc details for the message
    $flaggedStaffDocs = [];
    foreach (['medical_certificate' => 'Medical Certificate', 'resume' => 'Resume / CV'] as $sKey => $sLabel) {
        if (($staffApp[$sKey.'_status'] ?? 'pending') === 'flagged') {
            $sc = $staffApp[$sKey.'_comment'] ?? '';
            $flaggedStaffDocs[] = '• ' . $sLabel . ($sc !== '' ? ': ' . $sc : '');
        }
    }
    $staffMsg = !empty($flaggedStaffDocs)
        ? 'Flagged documents — ' . implode(' | ', $flaggedStaffDocs)
        : ($staffApp['feedback'] ?? 'Please resubmit flagged documents.');
    $notifications[] = ['type' => 'warning', 'icon' => 'bi-person-badge',
        'title' => 'Staff Application — Action Required: Documents Flagged',
        'msg'   => $staffMsg,
        'link'  => 'index.php?r=staff/apply&gym_id=' . ($staffApp['gym_owner_id'] ?? 0), 'linkText' => 'Resubmit Now'];
}
if ($staffApp && $staffApp['status'] === 'rejected') {
    $notifications[] = ['type' => 'danger', 'icon' => 'bi-person-badge',
        'title' => 'Staff Application — Rejected',
        'msg'   => $staffApp['feedback'] ?? 'Your staff application has been rejected.',
        'link'  => 'index.php?r=staff/gyms', 'linkText' => 'Re-apply'];
}
if ($staffApp && $staffApp['status'] === 'pending') {
    // Check if any individual docs have been acted on
    $anyStaffAction = false;
    foreach (['medical_certificate_status', 'resume_status'] as $sf) {
        $sv = $staffApp[$sf] ?? 'pending';
        if ($sv === 'approved' || $sv === 'flagged') { $anyStaffAction = true; break; }
    }
    if ($anyStaffAction) {
        $notifications[] = ['type' => 'info', 'icon' => 'bi-person-badge',
            'title' => 'Staff Application — Documents Being Reviewed',
            'msg'   => 'The gym owner is reviewing your documents. Some have already been marked. Click to see the full status.',
            'link'  => 'index.php?r=staff/apply&gym_id=' . ($staffApp['gym_owner_id'] ?? 0), 'linkText' => 'View Document Status'];
    }
}

// ── Membership Notifications ──────────────────────────────────────────────────
if ($memberApp && $memberApp['status'] === 'resubmit') {
    $notifications[] = ['type' => 'warning', 'icon' => 'bi-card-checklist',
        'title' => 'Membership Application — Resubmission Required',
        'msg'   => $memberApp['admin_feedback'] ?? 'Please resubmit your membership application.',
        'link'  => 'index.php?r=membership/apply', 'linkText' => 'Resubmit'];
}
if ($memberApp && $memberApp['status'] === 'rejected') {
    $notifications[] = ['type' => 'danger', 'icon' => 'bi-card-checklist',
        'title' => 'Membership Application — Rejected',
        'msg'   => $memberApp['admin_feedback'] ?? 'Your membership application has been rejected.',
        'link'  => 'index.php?r=membership/apply', 'linkText' => 'Re-apply'];
}
if ($memberApp && $memberApp['status'] === 'verified') {
    $notifications[] = ['type' => 'info', 'icon' => 'bi-card-checklist',
        'title' => 'Membership — Verified, Awaiting Payment',
        'msg'   => 'Your application has been verified. Please complete your payment. Amount: ₱' . number_format((float)($memberApp['payment_amount'] ?? 0), 2),
        'link'  => 'index.php?r=membership/apply', 'linkText' => 'View Details'];
}
if ($memberApp && $memberApp['status'] === 'approved' && !empty($memberApp['admin_feedback'])) {
    $notifications[] = ['type' => 'success', 'icon' => 'bi-check-circle',
        'title' => 'Membership Approved!',
        'msg'   => $memberApp['admin_feedback'],
        'link'  => 'index.php?r=membership/verifycode', 'linkText' => 'Check In'];
}
if ($memberApp && !empty($memberApp['preferred_trainer_id'])) {
    $notifications[] = ['type' => 'info', 'icon' => 'bi-person-check',
        'title' => 'Trainer Assigned to Your Membership',
        'msg'   => 'A fitness trainer has been assigned to you. Check your membership details for more info.',
        'link'  => 'index.php?r=membership/apply', 'linkText' => 'View'];
}
?>

<?php if (!empty($notifications)): ?>
<div class="mb-4">
    <h2 class="h6 mb-3"><i class="bi bi-bell me-1"></i>Notifications</h2>
    <?php foreach ($notifications as $n): ?>
    <div class="alert alert-<?= $n['type'] ?> d-flex align-items-start gap-2 mb-2">
        <i class="bi <?= $n['icon'] ?> mt-1"></i>
        <div class="flex-grow-1">
            <strong><?= $n['title'] ?></strong><br>
            <span class="small"><?= htmlspecialchars($n['msg']) ?></span>
        </div>
        <a href="<?= $n['link'] ?>" class="btn btn-sm btn-outline-<?= $n['type'] ?> text-nowrap"><?= $n['linkText'] ?></a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php
// ─── Gym Owner Application Document Status Panel ─────────────────────────────
// Show whenever there's an active application with per-doc data available
if ($legalDoc && in_array($legalDoc['status'], ['pending', 'resubmit', 'verified', 'rejected'], true)):
    $docStatuses = [];
    foreach ($gymDocLabels as $key => $label) {
        $s       = $legalDoc[$key.'_status'] ?? 'pending';
        $comment = $legalDoc[$key.'_comment'] ?? '';
        $docStatuses[] = compact('key', 'label', 's', 'comment');
    }
?>
<div class="card mb-4">
    <div class="card-header px-3 py-2 d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0"><i class="bi bi-building me-2 text-primary"></i>Gym Owner Application — Document Status</h2>
        <span class="badge bg-<?= $gymStatusColor ?>"><?= ucfirst($gymAppStatus) ?></span>
    </div>
    <div class="card-body">
        <?php if (!empty($legalDoc['admin_feedback']) && $legalDoc['status'] !== 'pending'): ?>
        <div class="alert alert-<?= $gymStatusColor === 'warning' ? 'warning' : ($gymStatusColor === 'success' ? 'success' : 'danger') ?> py-2 mb-3 small">
            <i class="bi bi-chat-left-text me-1"></i><strong>Admin Feedback:</strong> <?= htmlspecialchars($legalDoc['admin_feedback']) ?>
        </div>
        <?php endif; ?>
        <div class="row g-2">
        <?php foreach ($docStatuses as $doc):
            $iconClass  = match($doc['s']) { 'approved' => 'bi-check-circle-fill text-success', 'flagged' => 'bi-x-circle-fill text-danger', default => 'bi-hourglass-split text-warning' };
            $badgeClass = match($doc['s']) { 'approved' => 'bg-success', 'flagged' => 'bg-danger', default => 'bg-warning text-dark' };
            $borderStyle = match($doc['s']) { 'approved' => 'border-success', 'flagged' => 'border-danger', default => 'border-warning' };
        ?>
        <div class="col-md-6">
            <div class="border rounded p-2 d-flex align-items-start gap-2 <?= $borderStyle ?>" style="border-color:currentColor!important">
                <i class="bi <?= $iconClass ?> mt-1 flex-shrink-0" style="font-size:1.1rem"></i>
                <div class="flex-grow-1 min-width-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small"><?= htmlspecialchars($doc['label']) ?></span>
                        <span class="badge <?= $badgeClass ?> ms-1" style="font-size:.65rem;white-space:nowrap"><?= ucfirst($doc['s']) ?></span>
                    </div>
                    <?php if ($doc['comment'] !== ''): ?>
                    <div class="text-danger small mt-1"><i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($doc['comment']) ?></div>
                    <?php elseif ($doc['s'] === 'flagged'): ?>
                    <div class="text-danger small mt-1">Flagged — please resubmit this document.</div>
                    <?php elseif ($doc['s'] === 'approved'): ?>
                    <div class="text-success small mt-1">Accepted by admin ✓</div>
                    <?php else: ?>
                    <div class="text-muted small mt-1">Awaiting review...</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <div class="mt-3">
            <a href="index.php?r=gymowner/apply" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-arrow-right me-1"></i><?= $legalDoc['status'] === 'resubmit' ? 'Resubmit Flagged Documents' : 'View Full Application' ?>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// ─── Staff Application Document Status Panel ─────────────────────────────────
if ($staffApp && in_array($staffApp['status'], ['pending', 'resubmit', 'approved', 'rejected'], true)):
    $staffDocLabels = [
        'medical_certificate' => 'Medical Certificate',
        'resume'              => 'Resume / CV',
    ];
    $staffStatusColor = match($staffApp['status']) {
        'approved'  => 'success',
        'resubmit'  => 'danger',
        'rejected'  => 'danger',
        'pending'   => 'warning',
        default     => 'secondary',
    };
    $staffDocStatuses = [];
    foreach ($staffDocLabels as $key => $label) {
        $s       = $staffApp[$key.'_status'] ?? 'pending';
        $comment = $staffApp[$key.'_comment'] ?? '';
        $staffDocStatuses[] = compact('key', 'label', 's', 'comment');
    }
?>
<div class="card mb-4">
    <div class="card-header px-3 py-2 d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0"><i class="bi bi-person-badge me-2 text-warning"></i>Staff Application &mdash; Document Status</h2>
        <span class="badge bg-<?= $staffStatusColor ?>"><?= ucfirst($staffApp['status']) ?></span>
    </div>
    <div class="card-body">
        <?php if (!empty($staffApp['feedback']) && $staffApp['status'] !== 'pending'): ?>
        <div class="alert alert-<?= $staffStatusColor === 'success' ? 'success' : 'warning' ?> py-2 mb-3 small">
            <i class="bi bi-chat-left-text me-1"></i><strong>Gym Owner Feedback:</strong> <?= htmlspecialchars($staffApp['feedback']) ?>
        </div>
        <?php endif; ?>

        <?php
        // Summary counters
        $sCntApproved = $sCntFlagged = $sCntPending = 0;
        foreach ($staffDocStatuses as $sd) {
            if ($sd['s'] === 'approved') $sCntApproved++;
            elseif ($sd['s'] === 'flagged') $sCntFlagged++;
            else $sCntPending++;
        }
        ?>
        <div class="d-flex gap-2 mb-3">
            <span class="badge bg-success"><?= $sCntApproved ?> Approved</span>
            <span class="badge bg-danger"><?= $sCntFlagged ?> Flagged</span>
            <span class="badge bg-warning text-dark"><?= $sCntPending ?> Pending</span>
        </div>

        <div class="row g-2">
        <?php foreach ($staffDocStatuses as $sd):
            $sIconClass  = match($sd['s']) { 'approved' => 'bi-check-circle-fill text-success', 'flagged' => 'bi-x-circle-fill text-danger', default => 'bi-hourglass-split text-warning' };
            $sBadgeClass = match($sd['s']) { 'approved' => 'bg-success', 'flagged' => 'bg-danger', default => 'bg-warning text-dark' };
            $sBorder     = match($sd['s']) { 'approved' => 'border-success', 'flagged' => 'border-danger', default => 'border-warning' };
        ?>
        <div class="col-md-6">
            <div class="border rounded p-2 d-flex align-items-start gap-2 <?= $sBorder ?>" style="border-color:currentColor!important">
                <i class="bi <?= $sIconClass ?> mt-1 flex-shrink-0" style="font-size:1.1rem"></i>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small"><?= htmlspecialchars($sd['label']) ?></span>
                        <span class="badge <?= $sBadgeClass ?> ms-1" style="font-size:.65rem;white-space:nowrap"><?= ucfirst($sd['s']) ?></span>
                    </div>
                    <?php if ($sd['comment'] !== ''): ?>
                    <div class="text-danger small mt-1"><i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($sd['comment']) ?></div>
                    <?php elseif ($sd['s'] === 'flagged'): ?>
                    <div class="text-danger small mt-1">Flagged &mdash; please resubmit this document.</div>
                    <?php elseif ($sd['s'] === 'approved'): ?>
                    <div class="text-success small mt-1">Accepted by gym owner &#10003;</div>
                    <?php else: ?>
                    <div class="text-muted small mt-1">Awaiting gym owner review...</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <div class="mt-3">
            <a href="index.php?r=staff/apply&gym_id=<?= (int)($staffApp['gym_owner_id'] ?? 0) ?>" class="btn btn-sm btn-outline-warning">
                <i class="bi bi-arrow-right me-1"></i><?= $staffApp['status'] === 'resubmit' ? 'Resubmit Flagged Documents' : 'View Application Details' ?>
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-building me-2 text-primary"></i>Become a Gym Owner</h2></div>
            <div class="card-body">
                <p class="small text-muted">Submit legal documents to apply as a gym owner. Required: Certificate of Registration, Mayor's Permit, Business Name Certificate, Fire Safety Certificate.</p>
                <a href="index.php?r=gymowner/apply" class="btn btn-primary btn-sm"><i class="bi bi-arrow-right"></i> Apply Now</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-badge me-2 text-warning"></i>Apply as Staff</h2></div>
            <div class="card-body">
                <p class="small text-muted">Apply as a Maintenance Officer or Fitness Trainer at available gyms. Submit your medical certificate and resume.</p>
                <a href="index.php?r=staff/gyms" class="btn btn-warning btn-sm text-dark"><i class="bi bi-arrow-right"></i> View Available Gyms</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-card-checklist me-2 text-success"></i>Gym Membership</h2></div>
            <div class="card-body">
                <p class="small text-muted">Apply for gym membership and get assigned a fitness trainer.</p>
                <a href="index.php?r=membership/apply" class="btn btn-success btn-sm"><i class="bi bi-arrow-right"></i> Apply</a>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-qr-code me-2 text-info"></i>Verify Membership Code</h2></div>
            <div class="card-body">
                <p class="small text-muted">Enter your membership code to verify your identity and log attendance.</p>
                <a href="index.php?r=membership/verifycode" class="btn btn-info btn-sm"><i class="bi bi-arrow-right"></i> Verify</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
