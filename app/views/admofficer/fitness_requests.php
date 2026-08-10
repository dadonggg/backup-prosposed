<?php
declare(strict_types=1);
$pageTitle = 'Fitness Training Oversight';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-hearts text-success me-2"></i>Fitness Training Requests — Oversight</h1>
    <p class="text-muted">Read-only view of all training connections on the platform. Direct coaching is now managed between Fitness Enthusiasts and Fitness Trainers directly.</p>
</div>

<div class="alert alert-info d-flex align-items-start gap-2 mb-4">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        <strong>New Direct Booking Flow:</strong> Fitness Enthusiasts browse the <em>Trainer Directory</em> and request sessions directly with a trainer. 
        The Trainer accepts or declines. No admin approval is required in this flow. 
        This dashboard is for platform-wide monitoring and dispute resolution only.
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card text-center p-3 border-success">
            <div class="fw-bold display-6 text-success mb-1"><?= number_format((int)($stats['total_requests'] ?? 0)) ?></div>
            <div class="small text-muted">Total Requests</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold display-6 text-warning mb-1"><?= number_format((int)($stats['pending'] ?? 0)) ?></div>
            <div class="small text-muted">Pending</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold display-6 text-info mb-1"><?= number_format((int)($stats['assigned'] ?? 0)) ?></div>
            <div class="small text-muted">Accepted</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card text-center p-3">
            <div class="fw-bold display-6 text-success mb-1"><?= number_format((int)($stats['completed'] ?? 0)) ?></div>
            <div class="small text-muted">Completed</div>
        </div>
    </div>
</div>

<!-- All Requests Table (read-only) -->
<div class="card border-success">
    <div class="card-header px-3 py-2 d-flex justify-content-between align-items-center">
        <h2 class="h6 mb-0">All Coaching Requests (Platform-wide)</h2>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Trainer</th>
                        <th>Booking Date</th>
                        <th>Training Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allRequests)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No fitness requests found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allRequests as $r): ?>
                            <?php
                            $status = $r['status'] ?? 'pending';
                            $statusClasses = [
                                'pending'   => 'warning',
                                'assigned'  => 'info',
                                'completed' => 'success',
                                'cancelled' => 'secondary',
                            ];
                            $badgeClass = $statusClasses[$status] ?? 'secondary';
                            ?>
                            <tr>
                                <td class="small text-muted">#<?= $r['id'] ?></td>
                                <td>
                                    <div class="small fw-semibold"><?= htmlspecialchars($r['member_name'] ?? 'N/A') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($r['member_email'] ?? '') ?></div>
                                </td>
                                <td class="small">
                                    <?= !empty($r['trainer_name']) ? htmlspecialchars($r['trainer_name']) : '<span class="text-muted">Unassigned</span>' ?>
                                </td>
                                <td class="small">
                                    <?= !empty($r['booking_date']) ? date('M d, Y', strtotime($r['booking_date'])) : '—' ?>
                                    <?= !empty($r['booking_time']) ? '<br><span class="text-muted">' . htmlspecialchars($r['booking_time']) . '</span>' : '' ?>
                                </td>
                                <td class="small"><?= htmlspecialchars(str_replace(['_',','], [' ', ', '], $r['training_type'] ?? '—')) ?></td>
                                <td><span class="badge bg-<?= $badgeClass ?> py-1"><?= ucfirst($status) ?></span></td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
