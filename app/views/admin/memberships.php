<?php
declare(strict_types=1);
$pageTitle = 'Membership Applications';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-plus me-2"></i>Membership Applications</h1>
    <p class="text-muted">Review and manage membership registrations.</p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr><th>ID</th><th>Applicant</th><th>Name</th><th>Phone</th><th>Status</th><th>Date</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($apps)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">No membership applications.</td></tr>
                    <?php else: ?>
                        <?php foreach ($apps as $a): ?>
                        <tr>
                            <td><?= $a['id'] ?></td>
                            <td><?= htmlspecialchars($a['fullname']) ?></td>
                            <td><?= htmlspecialchars($a['first_name'] . ' ' . ($a['middle_initial'] ? $a['middle_initial'] . '. ' : '') . $a['last_name']) ?></td>
                            <td class="small"><?= htmlspecialchars($a['phone_number']) ?></td>
                            <td>
                                <?php $badge = match($a['status']) { 'pending'=>'bg-warning text-dark','verified'=>'bg-info','approved'=>'bg-success','rejected'=>'bg-danger','resubmit'=>'bg-secondary',default=>'bg-secondary' }; ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($a['created_at']) ?></td>
                            <td>
                                <a href="index.php?r=admin/reviewmembership&id=<?= $a['id'] ?>" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i> Review</a>
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
