<?php
declare(strict_types=1);
$pageTitle = 'Available Gyms - Staff Application';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-building me-2"></i>Apply as Staff</h1>
    <p class="text-muted">Choose a gym to apply as Maintenance Officer or Fitness Trainer.</p>
</div>

<?php if (empty($gyms)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>No gyms available at the moment.</strong>
        <p class="mb-0 mt-2">Please check back later. Gyms will appear here once they have been verified by the admin.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($gyms as $gym): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm hover-shadow">
                <div class="card-body d-flex flex-column">
                    <!-- Gym Logo -->
                    <div class="text-center mb-3">
                        <?php if (!empty($gym['gym_logo'])): ?>
                            <img src="public/<?= htmlspecialchars($gym['gym_logo']) ?>" 
                                 alt="<?= htmlspecialchars($gym['gym_name']) ?>" 
                                 class="img-fluid rounded" 
                                 style="max-height: 120px; object-fit: contain;">
                        <?php else: ?>
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 120px;">
                                <i class="bi bi-building display-4 text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Gym Name -->
                    <h3 class="h5 mb-2 text-center">
                        <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                        <?= htmlspecialchars($gym['gym_name']) ?>
                    </h3>

                    <!-- Gym Address -->
                    <p class="text-muted small text-center mb-3">
                        <i class="bi bi-pin-map me-1"></i>
                        <?= htmlspecialchars($gym['gym_address']) ?>
                    </p>

                    <!-- Owner Info -->
                    <p class="small mb-3">
                        <i class="bi bi-person-badge me-1"></i>
                        <strong>Owner:</strong> <?= htmlspecialchars($gym['owner_name']) ?>
                    </p>

                    <!-- Staff Count -->
                    <div class="d-flex justify-content-around mb-3 p-2 bg-light rounded">
                        <div class="text-center">
                            <i class="bi bi-tools text-warning"></i>
                            <div class="small"><strong><?= $gym['maintenance_count'] ?></strong></div>
                            <div class="small text-muted">Maintenance</div>
                        </div>
                        <div class="text-center">
                            <i class="bi bi-person-arms-up text-success"></i>
                            <div class="small"><strong><?= $gym['trainer_count'] ?></strong></div>
                            <div class="small text-muted">Trainers</div>
                        </div>
                    </div>

                    <!-- Apply Button -->
                    <div class="mt-auto">
                        <a href="index.php?r=staff/apply&gym_id=<?= $gym['gym_owner_id'] ?>" 
                           class="btn btn-primary w-100">
                            <i class="bi bi-file-earmark-person me-1"></i>Apply to This Gym
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<?php require __DIR__ . '/../partials/footer.php'; ?>
