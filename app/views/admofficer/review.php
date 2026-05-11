<?php
declare(strict_types=1);
$pageTitle = 'Review Membership';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <a href="index.php?r=admofficer/memberships" class="btn btn-outline-secondary btn-sm mb-2"><i class="bi bi-arrow-left"></i> Back</a>
    <h1 class="h3 mb-1">Review Membership #<?= $app['id'] ?></h1>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Application Details</h2></div>
            <div class="card-body">
                <dl class="row small mb-0">
                    <dt class="col-sm-4">Full Name</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($app['first_name'] . ' ' . ($app['middle_initial'] ? $app['middle_initial'] . '. ' : '') . $app['last_name']) ?></dd>
                    <dt class="col-sm-4">Phone</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($app['phone_number']) ?></dd>
                    <dt class="col-sm-4">Account</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($app['fullname']) ?> (<?= htmlspecialchars($app['email']) ?>)</dd>
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        <?php $badge = match($app['status']) {
                            'pending'=>'bg-warning text-dark','verified'=>'bg-info','approved'=>'bg-success',
                            'rejected'=>'bg-danger','resubmit'=>'bg-secondary',default=>'bg-secondary'
                        }; ?>
                        <span class="badge <?= $badge ?>"><?= ucfirst($app['status']) ?></span>
                    </dd>
                    <dt class="col-sm-4">Plan</dt>
                    <dd class="col-sm-8">
                        <?= ucfirst(str_replace('_', ' ', $app['payment_type'] ?? 'N/A')) ?>
                        — <strong>₱<?= number_format((float)($app['payment_amount'] ?? 0), 2) ?></strong>
                    </dd>
                    <?php if (!empty($app['student_proof'])): ?>
                    <dt class="col-sm-4">Student Proof</dt>
                    <dd class="col-sm-8">
                        <a href="public/<?= htmlspecialchars($app['student_proof']) ?>" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-file-earmark"></i> View
                        </a>
                    </dd>
                    <?php endif; ?>
                    <?php if (!empty($app['preferred_trainer_id'])): ?>
                    <dt class="col-sm-4">Assigned Trainer</dt>
                    <dd class="col-sm-8">
                        <?php
                        $assignedTrainer = null;
                        foreach ($employees as $e) {
                            if ((int)$e['id'] === (int)$app['preferred_trainer_id']) { $assignedTrainer = $e; break; }
                        }
                        ?>
                        <?= $assignedTrainer ? htmlspecialchars($assignedTrainer['fullname']) : 'ID#' . $app['preferred_trainer_id'] ?>
                    </dd>
                    <?php endif; ?>
                    <dt class="col-sm-4">Applied</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($app['created_at']) ?></dd>
                </dl>
                <?php if ($app['admin_feedback']): ?>
                    <div class="mt-2 small p-2 rounded" style="background:rgba(27,107,42,.05)">
                        <span class="text-muted fw-bold">Previous Feedback:</span><br>
                        <?= htmlspecialchars($app['admin_feedback']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <?php if (in_array($app['status'], ['pending', 'resubmit'], true)): ?>
        <div class="card mb-3">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Review Actions</h2></div>
            <div class="card-body">
                <form method="post" class="vstack gap-3">
                    <div>
                        <label class="form-label" for="trainer_id">Assign Fitness Trainer</label>
                        <select class="form-select" name="trainer_id" id="trainer_id">
                            <option value="">— Select trainer to assign —</option>
                            <?php foreach ($trainers as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">The trainer will be assigned when you verify or approve.</div>
                    </div>
                    <div>
                        <label class="form-label" for="feedback">Feedback</label>
                        <textarea class="form-control" id="feedback" name="feedback" rows="3" placeholder="Provide feedback..."><?= htmlspecialchars($app['admin_feedback'] ?? '') ?></textarea>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" name="action" value="verify" class="btn btn-info btn-sm text-white"><i class="bi bi-check"></i> Verify</button>
                        <button type="submit" name="action" value="resubmit" class="btn btn-warning btn-sm text-dark"><i class="bi bi-arrow-repeat"></i> Resubmit</button>
                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Reject</button>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($app['status'] === 'verified'): ?>
        <div class="card mb-3">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-cash-coin me-1"></i>Payment Confirmation</h2></div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <strong>Amount Due:</strong> ₱<?= number_format((float)($app['payment_amount'] ?? 0), 2) ?><br>
                    <strong>Plan:</strong> <?= ucfirst(str_replace('_', ' ', $app['payment_type'] ?? 'N/A')) ?>
                </div>
                <form method="post" class="vstack gap-3">
                    <div>
                        <label class="form-label" for="trainer_id">Assign Fitness Trainer</label>
                        <select class="form-select" name="trainer_id" id="trainer_id">
                            <option value="">— No trainer —</option>
                            <?php foreach ($trainers as $t): ?>
                                <option value="<?= $t['id'] ?>" <?= ($app['preferred_trainer_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['fullname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="action" value="paid" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Confirm Payment & Generate Code</button>
                </form>
            </div>
        </div>

        <?php elseif ($app['status'] === 'approved'): ?>
        <div class="card mb-3">
            <div class="card-body text-center py-4">
                <i class="bi bi-check-circle display-4 text-success"></i>
                <p class="mt-2 mb-0">This membership has been <strong>approved</strong>.</p>
                <?php if ($app['admin_feedback']): ?>
                    <p class="small text-muted mt-2"><?= htmlspecialchars($app['admin_feedback']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <!-- Assign trainer to already-approved member -->
        <?php if (empty($app['preferred_trainer_id'])): ?>
        <div class="card">
            <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-person-check me-1"></i>Assign Trainer</h2></div>
            <div class="card-body">
                <form method="post">
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" name="trainer_id" required>
                            <option value="">— Select trainer —</option>
                            <?php foreach ($trainers as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="action" value="assign_trainer" class="btn btn-primary btn-sm"><i class="bi bi-person-check"></i> Assign</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-4">
                <i class="bi bi-x-circle display-4 text-danger"></i>
                <p class="mt-2 mb-0">This membership was <strong>rejected</strong>.</p>
                <?php if ($app['admin_feedback']): ?>
                    <p class="small text-muted mt-2"><?= htmlspecialchars($app['admin_feedback']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
