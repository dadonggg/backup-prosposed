<?php
declare(strict_types=1);
$pageTitle = 'Available Gyms';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-building me-2"></i>Available Gyms</h1>
    <p class="text-muted mb-0">Choose a gym to apply for membership</p>
</div>

<?php if (empty($gyms)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>No gyms available at this time. Please check back later.
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($gyms as $gym): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm gym-card"
                 style="cursor:pointer; transition: transform .18s, box-shadow .18s;"
                 onclick="window.location='index.php?r=membership/gymprofile&gym_id=<?= (int)$gym['id'] ?>'">

                <?php if (!empty($gym['gym_logo'])): ?>
                <img src="public/<?= htmlspecialchars($gym['gym_logo']) ?>"
                     class="card-img-top"
                     alt="<?= htmlspecialchars($gym['gym_name']) ?>"
                     style="height: 200px; object-fit: cover;">
                <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                     style="height: 200px;">
                    <i class="bi bi-building display-1 text-muted"></i>
                </div>
                <?php endif; ?>

                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($gym['gym_name'] ?? 'Unnamed Gym') ?></h5>

                    <div class="mb-3 flex-grow-1">
                        <p class="card-text text-muted mb-2">
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= htmlspecialchars($gym['gym_address'] ?? 'Address not provided') ?>
                        </p>
                        <?php if (!empty($gym['fullname'])): ?>
                        <p class="card-text text-muted mb-0">
                            <i class="bi bi-person me-1"></i>
                            Owner: <?= htmlspecialchars($gym['fullname']) ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="index.php?r=membership/gymprofile&gym_id=<?= (int)$gym['id'] ?>"
                           class="btn btn-outline-success flex-fill"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-eye me-1"></i>View Details
                        </a>
                        <a href="index.php?r=membership/apply&gym_id=<?= (int)$gym['id'] ?>"
                           class="btn btn-primary flex-fill"
                           onclick="event.stopPropagation()">
                            <i class="bi bi-person-plus me-1"></i>Apply Membership
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.gym-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(27,107,42,.18) !important;
}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
