<?php
declare(strict_types=1);
$pageTitle = 'Coaching Requests Inbox';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-inbox text-success me-2"></i>Coaching Requests</h1>
    <p class="text-muted">Review and accept or decline incoming requests from fitness enthusiasts for scheduled training sessions.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card border-success">
    <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Incoming Requests</h2></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Enthusiast Name</th>
                        <th>Email</th>
                        <th>Preferred Session Date / Time</th>
                        <th>Training Interest</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No pending coaching requests found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($r['member_name'] ?? 'N/A') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($r['email'] ?? 'N/A') ?></td>
                                <td>
                                    <div class="small fw-semibold"><i class="bi bi-calendar-event me-1"></i><?= date('M d, Y', strtotime($r['booking_date'])) ?></div>
                                    <div class="text-muted small"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($r['booking_time'] ?? '') ?></div>
                                </td>
                                <td class="small">
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20">
                                        <?= htmlspecialchars(str_replace('_', ' ', $r['training_type'] ?? 'General')) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <form action="index.php?r=trainer/decisionrequest" method="POST" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="decision" value="accept">
                                            <button type="submit" class="btn btn-xs btn-success py-1 px-3" style="font-size:0.75rem;">
                                                <i class="bi bi-check-circle"></i> Accept
                                            </button>
                                        </form>
                                        <form action="index.php?r=trainer/decisionrequest" method="POST" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?= $r['id'] ?>">
                                            <input type="hidden" name="decision" value="decline">
                                            <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-3" style="font-size:0.75rem;">
                                                <i class="bi bi-x-circle"></i> Decline
                                            </button>
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
