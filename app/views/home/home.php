<?php
declare(strict_types=1);
$pageTitle = 'Home';
require __DIR__ . '/../partials/header.php';

$displayName = trim(
    (string)($user['firstname'] ?? '') . ' ' .
    (string)($user['lastname'] ?? '')
);
if ($displayName === '') {
    $displayName = (string)($user['fullname'] ?? '');
}
$age = $user['age'] ?? null;
$heightCm = $user['height_cm'] ?? null;
$weightKg = $user['weight_kg'] ?? null;
$middleInitial = trim((string)($user['middle_initial'] ?? ''));
$showProfile = $middleInitial !== ''
    || $age !== null
    || $heightCm !== null
    || $weightKg !== null;
?>

<div class="row g-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <div>
                <h1 class="h2 mb-1">Welcome</h1>
                <p class="text-muted mb-0">
                    Signed in as <strong><?= htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8') ?></strong>
                    <?php if (!empty($user['email'])): ?>
                        <span class="d-block small"><?= htmlspecialchars((string)$user['email'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <a class="btn btn-outline-secondary" href="index.php?r=home/logout">Logout</a>
        </div>
    </div>
    <?php if ($showProfile): ?>
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Profile</h2>
                <dl class="row mb-0 small">
                    <?php if ($middleInitial !== ''): ?>
                        <dt class="col-sm-3">Middle initial</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars($middleInitial, ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if ($age !== null): ?>
                        <dt class="col-sm-3">Age</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars((string)$age, ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if ($heightCm !== null): ?>
                        <dt class="col-sm-3">Height</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars((string)$heightCm, ENT_QUOTES, 'UTF-8') ?> cm</dd>
                    <?php endif; ?>
                    <?php if ($weightKg !== null): ?>
                        <dt class="col-sm-3">Weight</dt>
                        <dd class="col-sm-9"><?= htmlspecialchars((string)$weightKg, ENT_QUOTES, 'UTF-8') ?> kg</dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h2 class="h5 card-title">Nutrify Dashboard</h2>
                <p class="card-text text-muted mb-0">
                    Welcome to the Nutrify Management System. Use this area as the entry point for dashboards,
                    inventory management, staff, and gym membership modules.
                </p>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h2 class="h6 text-uppercase text-muted mb-3">Next steps</h2>
                <ul class="list-unstyled mb-0 small vstack gap-2">
                    <li class="d-flex gap-2"><span class="text-primary">•</span> Add role-based features</li>
                    <li class="d-flex gap-2"><span class="text-primary">•</span> Connect inventory or synthesis modules</li>
                    <li class="d-flex gap-2"><span class="text-primary">•</span> Expose secure API endpoints</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
