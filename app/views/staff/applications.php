<?php
declare(strict_types=1);
$pageTitle = 'Staff Applications';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-people me-2"></i>Staff Applications</h1>
    <p class="text-muted">Review staff applications and manage employees.</p>
</div>

<div class="card mb-4">
    <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Pending Applications</h2></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Applicant</th><th>Email</th><th>Position</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
                <tbody>
                    <?php if (empty($apps)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No applications found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($apps as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td><?= htmlspecialchars($a['fullname']) ?></td>
                            <td class="small"><?= htmlspecialchars($a['email']) ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($a['application_type']) ?></span></td>
                            <td>
                                <?php $badge = match($a['status']) { 'pending'=>'bg-warning text-dark','approved'=>'bg-success','rejected'=>'bg-danger',default=>'bg-secondary' }; ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($a['created_at']) ?></td>
                            <td>
                                <a href="index.php?r=staff/review&id=<?= $a['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i> Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Current Employees</h2></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Position</th><th>Available</th><th>Hired</th></tr></thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No employees yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($employees as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['fullname']) ?></td>
                            <td class="small"><?= htmlspecialchars($e['email']) ?></td>
                            <td><span class="badge bg-info"><?= ucfirst($e['position']) ?></span></td>
                            <td><span class="badge <?= $e['is_available'] ? 'bg-success' : 'bg-secondary' ?>"><?= $e['is_available'] ? 'Yes' : 'No' ?></span></td>
                            <td class="small"><?= htmlspecialchars($e['hired_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
