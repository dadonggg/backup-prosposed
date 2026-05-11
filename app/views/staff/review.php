<?php
declare(strict_types=1);
$pageTitle = 'Review Staff Application';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <a href="index.php?r=staff/applications" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back</a>
    <h1 class="h3 mb-1">Review Application #<?= $app['id'] ?></h1>
    <p class="text-muted">Applicant: <strong><?= htmlspecialchars($app['fullname'] ?? '') ?></strong> (<?= htmlspecialchars($app['email'] ?? '') ?>)</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php
$docFields = [
    'medical_certificate' => 'Medical Certificate',
    'resume' => 'Resume / CV',
];
?>

<div class="row g-4">
    <!-- Left: Application Details + Per-document review -->
    <div class="col-lg-8">
        <!-- Application Info -->
        <div class="card mb-4">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-info-circle me-1"></i>Application Details</h2></div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-4">Position</dt>
                    <dd class="col-sm-8"><span class="badge bg-info"><?= ucfirst($app['application_type']) ?></span></dd>
                    <dt class="col-sm-4">Applied</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($app['created_at']) ?></dd>
                </dl>
            </div>
        </div>

        <!-- Per-document verification cards -->
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-file-earmark-check me-1"></i>Document Verification Checklist</h2></div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($docFields as $key => $label):
                        $statusKey = $key . '_status';
                        $commentKey = $key . '_comment';
                        $checkedKey = $key . '_checked';
                        $docStatus = $app[$statusKey] ?? 'pending';
                        $docComment = $app[$commentKey] ?? '';
                        $docChecked = (int)($app[$checkedKey] ?? 0);

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
                        <div class="border rounded p-3" style="border-color:rgba(27,107,42,.15)!important">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-bold small"><i class="<?= $statusIcon ?> me-1"></i><?= $label ?></div>
                                <span class="badge <?= $statusBadge ?>" style="font-size:.7rem"><?= ucfirst($docStatus) ?></span>
                            </div>

                            <?php if (!empty($app[$key])): ?>
                                <a href="public/<?= htmlspecialchars($app[$key]) ?>" target="_blank" class="btn btn-outline-info btn-sm mb-2">
                                    <i class="bi bi-file-earmark"></i> View Document
                                </a>
                            <?php else: ?>
                                <span class="text-danger small d-block mb-2">Not uploaded</span>
                            <?php endif; ?>

                            <!-- Per-document action form -->
                            <form method="post" class="mt-2">
                                <input type="hidden" name="action" value="update_doc_status">
                                <input type="hidden" name="doc_field" value="<?= $key ?>">

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="doc_checked" id="check_<?= $key ?>" value="1" <?= $docChecked ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="check_<?= $key ?>">Verified / Reviewed</label>
                                </div>

                                <div class="mb-2">
                                    <textarea class="form-control form-control-sm" name="doc_comment" rows="2" placeholder="Comment for this document..."><?= htmlspecialchars($docComment) ?></textarea>
                                </div>

                                <div class="d-flex gap-1">
                                    <button type="submit" name="doc_status" value="approved" class="btn btn-success btn-sm" style="font-size:.75rem">
                                        <i class="bi bi-check"></i> Approve
                                    </button>
                                    <button type="submit" name="doc_status" value="flagged" class="btn btn-danger btn-sm" style="font-size:.75rem">
                                        <i class="bi bi-flag"></i> Flag Issue
                                    </button>
                                    <button type="submit" name="doc_status" value="pending" class="btn btn-outline-secondary btn-sm" style="font-size:.75rem">
                                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Overall status & final actions -->
    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-clipboard-check me-1"></i>Overall Status</h2></div>
            <div class="card-body">
                <?php
                $badge = match($app['status']) {
                    'pending' => 'bg-warning text-dark',
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                    'resubmit' => 'bg-info',
                    default => 'bg-secondary'
                };
                ?>
                <div class="text-center mb-3">
                    <span class="badge <?= $badge ?>" style="font-size:.9rem;padding:8px 16px"><?= ucfirst($app['status']) ?></span>
                </div>

                <!-- Summary of per-document statuses -->
                <div class="small mb-3">
                    <?php foreach ($docFields as $key => $label):
                        $s = $app[$key . '_status'] ?? 'pending';
                        $icon = match($s) { 'approved'=>'bi-check-circle text-success', 'flagged'=>'bi-x-circle text-danger', default=>'bi-dash-circle text-warning' };
                    ?>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi <?= $icon ?>"></i>
                        <span><?= $label ?>: <strong><?= ucfirst($s) ?></strong></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($app['feedback'])): ?>
                    <div class="mt-2 small p-2 rounded" style="background:rgba(27,107,42,.05)">
                        <span class="text-muted fw-bold">Feedback:</span><br>
                        <?= htmlspecialchars($app['feedback']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($app['status'] !== 'approved'): ?>
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-lightning me-1"></i>Final Actions</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <div>
                        <label class="form-label" for="feedback">Overall Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Optional feedback..."><?= htmlspecialchars($app['feedback'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve & Hire</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-check-circle display-4 text-success"></i>
                <p class="mt-2 mb-0">This application has been <strong>approved</strong>.</p>
                <?php if (!empty($app['feedback'])): ?>
                    <p class="small text-muted mt-2">Feedback: <?= htmlspecialchars($app['feedback']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
