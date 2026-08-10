<?php
declare(strict_types=1);
$pageTitle = 'Manage Users';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i>Manage Users</h1>
    <p class="text-muted">Assign or revoke the <strong>Administrative Officer</strong> role for any registered user.</p>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="bi bi-people-fill"></i>
        <span class="fw-semibold">All Registered Users</span>
        <span class="badge bg-secondary ms-auto"><?= count($allUsers) ?> users</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th>Registered</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($allUsers)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($allUsers as $u): ?>
                        <?php
                            $roleLabel = [
                                'administrative_officer' => ['Administrative Officer', 'bg-primary'],
                                'gym_owner'              => ['Gym Owner',              'bg-info text-dark'],
                                'fitness_enthusiast'     => ['Fitness Enthusiast',     'bg-secondary'],
                                'fitness_trainer'        => ['Fitness Trainer',        'bg-success'],
                                'maintenance_officer'    => ['Maintenance Officer',    'bg-warning text-dark'],
                            ][$u['role']] ?? [ucfirst(str_replace('_', ' ', $u['role'])), 'bg-secondary'];
                            $isOfficer = ($u['role'] === 'administrative_officer');
                        ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($u['fullname']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $roleLabel[1] ?>"><?= $roleLabel[0] ?></span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($u['created_at']) ?></td>
                            <td class="text-center">
                                <?php if ($isOfficer): ?>
                                    <!-- Revoke button -->
                                    <form method="POST" action="index.php?r=admin/assignofficer"
                                          onsubmit="return confirm('Revoke Administrative Officer role from <?= htmlspecialchars(addslashes($u['fullname'])) ?>?')">
                                        <input type="hidden" name="id"     value="<?= (int)$u['id'] ?>">
                                        <input type="hidden" name="action" value="revoke">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-person-dash me-1"></i>Revoke Officer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <!-- Assign button -->
                                    <form method="POST" action="index.php?r=admin/assignofficer"
                                          onsubmit="return confirm('Assign <?= htmlspecialchars(addslashes($u['fullname'])) ?> as Administrative Officer?')">
                                        <input type="hidden" name="id"     value="<?= (int)$u['id'] ?>">
                                        <input type="hidden" name="action" value="assign">
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-person-check me-1"></i>Assign as Officer
                                        </button>
                                    </form>
                                <?php endif; ?>
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
