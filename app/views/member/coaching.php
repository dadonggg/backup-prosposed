<?php
declare(strict_types=1);
$pageTitle = 'My Coach & Plan';
require __DIR__ . '/../partials/header.php';
?>

<style>
.coaching-hero {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #f1f5f9;
    border-radius: 12px;
    overflow: hidden;
}
.coaching-hero .header-bar {
    background: rgba(34, 197, 94, 0.1);
    border-bottom: 1px solid rgba(34, 197, 94, 0.15);
}
.stat-pill {
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 10px 16px;
    text-align: center;
}
.stat-pill small { color: #94a3b8; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; }
.stat-pill strong { color: #22c55e; }
</style>

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-person-arms-up me-2 text-success"></i>My Coach &amp; Plan</h1>
            <p class="text-muted mb-0">View your assigned coach, training plan, and coaching history.</p>
        </div>
        <?php if (!$activeFitnessRequest): ?>
        <a href="index.php?r=fitness/request" class="btn btn-success" style="border-radius: 20px;">
            <i class="bi bi-plus-circle me-1"></i> Request Training
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!$activeFitnessRequest): ?>
<!-- No Active Request -->
<div class="coaching-hero p-0 mb-4 shadow-sm">
    <div class="header-bar px-4 py-3">
        <h5 class="mb-0 text-white fw-bold" style="font-size: 1.05rem;"><i class="bi bi-heart-pulse me-2 text-success"></i>Get Started</h5>
    </div>
    <div class="text-center py-5 px-4">
        <div class="mb-3"><i class="bi bi-heart-pulse text-success fs-1"></i></div>
        <h5 class="text-white">Unlock Your Fitness Potential</h5>
        <p class="text-muted small mx-auto" style="max-width: 480px;">
            Get a fully customized workout and nutrition plan created by our expert fitness coaches.
            Track your consistency and get direct professional feedback.
        </p>
        <a href="index.php?r=fitness/request" class="btn btn-success btn-sm mt-2 px-4" style="border-radius: 20px; font-weight: 600;">
            Request Training Now
        </a>
    </div>
</div>

<?php elseif ($activeFitnessRequest['status'] === 'pending'): ?>
<!-- Request Pending -->
<div class="coaching-hero p-0 mb-4 shadow-sm">
    <div class="header-bar px-4 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white fw-bold" style="font-size: 1.05rem;"><i class="bi bi-hourglass-split me-2 text-warning"></i>Coaching Request</h5>
        <span class="badge bg-warning">Pending</span>
    </div>
    <div class="p-4">
        <div class="d-flex align-items-center p-3 rounded" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.15);">
            <div class="bg-warning bg-opacity-15 p-3 rounded-circle me-3">
                <i class="bi bi-hourglass-split text-warning fs-3"></i>
            </div>
            <div>
                <h6 class="mb-1 text-white">Coaching Request Submitted</h6>
                <p class="text-muted small mb-0">
                    Your training request is received and currently awaiting trainer assignment by our Administrative Officer.
                </p>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeFitnessRequest['status'] === 'assigned'): ?>
<!-- Trainer Assigned -->
<div class="coaching-hero p-0 mb-4 shadow-sm">
    <div class="header-bar px-4 py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-white fw-bold" style="font-size: 1.05rem;"><i class="bi bi-person-badge me-2 text-success"></i>Active Coaching</h5>
        <span class="badge bg-success">Assigned</span>
    </div>
    <div class="p-4">
        <div class="row g-4">
            <!-- Coach Info -->
            <div class="col-md-5 border-end border-secondary border-opacity-20">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-success bg-opacity-15 p-3 rounded-circle me-3">
                        <i class="bi bi-person-badge text-success fs-3"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem; letter-spacing: 0.08em;">Assigned Coach</small>
                        <h5 class="mb-0 text-white"><?= htmlspecialchars($activeFitnessRequest['trainer_name'] ?? 'Your Fitness Coach') ?></h5>
                        <?php if (!empty($activeFitnessRequest['trainer_specialization'])): ?>
                        <small class="text-muted"><?= htmlspecialchars($activeFitnessRequest['trainer_specialization']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <a href="index.php?r=fitness/status" class="btn btn-outline-light btn-sm text-start">
                        <i class="bi bi-clipboard-check me-2"></i>My Training Overview
                    </a>
                    <a href="index.php?r=fitness/progress&request_id=<?= $activeFitnessRequest['id'] ?>" class="btn btn-outline-success btn-sm text-start">
                        <i class="bi bi-graph-up me-2"></i>Track Daily Progress
                    </a>
                </div>
            </div>

            <!-- Plan Details -->
            <div class="col-md-7">
                <?php if (!$activePlan || $activePlan['status'] === 'draft'): ?>
                <div class="text-center py-4">
                    <i class="bi bi-journal-text text-muted fs-3 mb-2 d-block"></i>
                    <h6 class="text-white">Plan in Preparation</h6>
                    <p class="text-muted small mb-0">
                        Your coach is currently tailoring your workout and nutrition schedules. Check back soon!
                    </p>
                </div>
                <?php else: ?>
                <h6 class="text-white mb-3" style="font-size: 0.95rem;"><i class="bi bi-card-checklist text-success me-2"></i>Personalized Fitness & Diet Plan</h6>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="stat-pill">
                            <small class="d-block">Target Calories</small>
                            <strong><?= $activePlan['target_calories'] ? $activePlan['target_calories'] . ' kcal' : '—' ?></strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-pill">
                            <small class="d-block">Weekly Sessions</small>
                            <strong><?= $activePlan['recommended_sessions_per_week'] ?? '—' ?> sessions</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="index.php?r=fitness/plan&request_id=<?= $activeFitnessRequest['id'] ?>" class="btn btn-success btn-sm flex-grow-1">
                        <i class="bi bi-file-text me-1"></i>View Workouts
                    </a>
                    <a href="index.php?r=fitness/plan&request_id=<?= $activeFitnessRequest['id'] ?>#nutrition-section" class="btn btn-outline-info btn-sm flex-grow-1">
                        <i class="bi bi-egg-fried me-1"></i>View Diet
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Training Request History -->
<?php if (!empty($allRequests)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history me-2 text-muted"></i>Training Request History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Request Date</th>
                        <th>Training Type</th>
                        <th>Trainer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRequests as $req): ?>
                    <?php
                        $__st = $req['status'];
                        if ($__st === 'assigned') { $statusBadge = 'bg-success'; }
                        elseif ($__st === 'pending') { $statusBadge = 'bg-warning'; }
                        elseif ($__st === 'completed') { $statusBadge = 'bg-info'; }
                        elseif ($__st === 'declined') { $statusBadge = 'bg-danger'; }
                        else { $statusBadge = 'bg-secondary'; }
                    ?>
                    <tr>
                        <td class="small"><?= date('M j, Y', strtotime($req['created_at'])) ?></td>
                        <td class="small"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $req['training_type'] ?? 'General'))) ?></td>
                        <td class="small"><?= htmlspecialchars($req['trainer_name'] ?? '—') ?></td>
                        <td><span class="badge <?= $statusBadge ?>"><?= ucfirst($req['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
