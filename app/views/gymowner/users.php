<?php
declare(strict_types=1);
$pageTitle = 'Manage Users';
require __DIR__ . '/../partials/header.php';
?>

<style>
.bg-purple { background-color: #7c3aed !important; }
.btn-purple { background-color: #7c3aed !important; border-color: #7c3aed !important; color: #fff !important; }
.btn-purple:hover { background-color: #6d28d9 !important; border-color: #6d28d9 !important; color: #fff !important; }
.btn-outline-purple { border-color: #7c3aed !important; color: #7c3aed !important; }
.btn-outline-purple:hover { background-color: #7c3aed !important; color: #fff !important; }

/* Clean single dropdown button layout */
.role-dropdown-btn {
    min-width: 140px;
}
.role-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.dropdown-item-role {
    cursor: pointer;
    font-size: 0.85rem;
    padding: 0.5rem 0.85rem;
}
.dropdown-item-role:hover {
    background-color: #f8f9fa;
}
</style>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-gear me-2"></i>Manage Users</h1>
    <p class="text-muted">Assign or revoke staff roles for registered users on your platform.</p>
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
                        <th class="text-center" style="width: 180px;">Action</th>
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
                            $userRole = $u['role'];
                            $roleConfig = [
                                'administrative_officer' => ['label' => 'Administrative Officer', 'badge' => 'bg-primary text-white', 'dot' => 'bg-primary'],
                                'marketing_officer'      => ['label' => 'Marketing Officer',      'badge' => 'bg-purple text-white',  'dot' => 'bg-purple'],
                                'gym_owner'              => ['label' => 'Gym Owner',              'badge' => 'bg-info text-dark',     'dot' => 'bg-info'],
                                'fitness_enthusiast'     => ['label' => 'Fitness Enthusiast',     'badge' => 'bg-secondary',          'dot' => 'bg-secondary'],
                                'fitness_trainer'        => ['label' => 'Fitness Trainer',        'badge' => 'bg-success',            'dot' => 'bg-success'],
                                'trainer'                => ['label' => 'Fitness Trainer',        'badge' => 'bg-success',            'dot' => 'bg-success'],
                                'maintenance_officer'    => ['label' => 'Maintenance Officer',    'badge' => 'bg-warning text-dark',  'dot' => 'bg-warning'],
                                'maintenance'            => ['label' => 'Maintenance Officer',    'badge' => 'bg-warning text-dark',  'dot' => 'bg-warning'],
                                'customer'               => ['label' => 'Fitness Enthusiast',     'badge' => 'bg-secondary',          'dot' => 'bg-secondary'],
                            ];

                            $currentLabel = $roleConfig[$userRole]['label'] ?? ucfirst(str_replace('_', ' ', $userRole));
                            $currentBadge = $roleConfig[$userRole]['badge'] ?? 'bg-secondary';
                            $isProtected  = in_array($userRole, ['admin', 'gym_owner'], true);

                            // Available staff roles to manage
                            $assignableRoles = [
                                [
                                    'key'    => 'administrative_officer',
                                    'label'  => 'Administrative Officer',
                                    'action' => 'index.php?r=gymowner/assignofficer',
                                    'active' => ($userRole === 'administrative_officer'),
                                    'dot'    => 'bg-primary',
                                ],
                                [
                                    'key'    => 'marketing_officer',
                                    'label'  => 'Marketing Officer',
                                    'action' => 'index.php?r=gymowner/assignmarketingofficer',
                                    'active' => ($userRole === 'marketing_officer'),
                                    'dot'    => 'bg-purple',
                                ],
                                [
                                    'key'    => 'fitness_trainer',
                                    'label'  => 'Fitness Trainer',
                                    'action' => 'index.php?r=gymowner/assignfitnesstrainer',
                                    'active' => ($userRole === 'trainer' || $userRole === 'fitness_trainer'),
                                    'dot'    => 'bg-success',
                                ],
                                [
                                    'key'    => 'maintenance_officer',
                                    'label'  => 'Maintenance Officer',
                                    'action' => 'index.php?r=gymowner/assignmaintenanceofficer',
                                    'active' => ($userRole === 'maintenance' || $userRole === 'maintenance_officer'),
                                    'dot'    => 'bg-warning',
                                ],
                            ];
                        ?>
                        <tr>
                            <td><?= (int)$u['id'] ?></td>
                            <td class="fw-medium"><?= htmlspecialchars($u['fullname']) ?></td>
                            <td class="small text-muted"><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="badge <?= $currentBadge ?>"><?= $currentLabel ?></span>
                            </td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                            <td class="text-center">
                                <?php if ($isProtected): ?>
                                    <span class="text-muted small"><i class="bi bi-lock-fill"></i> Protected</span>
                                <?php else: ?>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle role-dropdown-btn" 
                                                type="button" 
                                                data-bs-toggle="dropdown" 
                                                aria-expanded="false">
                                            <i class="bi bi-person-gear me-1"></i> Manage Role
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width: 230px;">
                                            <li class="dropdown-header text-uppercase small fw-bold px-2 py-1">Select / Toggle Role</li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            
                                            <?php foreach ($assignableRoles as $rItem): ?>
                                                <li>
                                                    <form method="POST" action="<?= $rItem['action'] ?>" class="m-0 p-0">
                                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                                        <input type="hidden" name="action" value="<?= $rItem['active'] ? 'revoke' : 'assign' ?>">
                                                        
                                                        <button type="submit" 
                                                                class="dropdown-item dropdown-item-role rounded d-flex align-items-center justify-content-between my-1"
                                                                onclick="return confirmRoleChange('<?= htmlspecialchars(addslashes($u['fullname'])) ?>', '<?= $rItem['label'] ?>', <?= $rItem['active'] ? 'true' : 'false' ?>)">
                                                            
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="role-dot <?= $rItem['dot'] ?>"></span>
                                                                <span><?= $rItem['label'] ?></span>
                                                            </div>
                                                            
                                                            <?php if ($rItem['active']): ?>
                                                                <i class="bi bi-check-circle-fill text-success ms-2"></i>
                                                            <?php else: ?>
                                                                <i class="bi bi-circle text-muted ms-2 opacity-50"></i>
                                                            <?php endif; ?>
                                                        </button>
                                                    </form>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
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

<script>
function confirmRoleChange(fullname, roleLabel, isActive) {
    if (isActive) {
        return confirm('Are you sure you want to REVOKE the "' + roleLabel + '" role from ' + fullname + '?');
    } else {
        return confirm('Assign ' + fullname + ' as ' + roleLabel + '?');
    }
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
