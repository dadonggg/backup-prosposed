<?php
declare(strict_types=1);
$pageTitle = 'Legal Document Reviews';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-file-earmark-check me-2"></i>Legal Document Reviews</h1>
    <p class="text-muted">Review gym owner applications and their submitted legal documents.</p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($docs)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">No applications found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($docs as $doc): ?>
                        <tr>
                            <td><?= $doc['id'] ?></td>
                            <td><?= htmlspecialchars($doc['fullname']) ?></td>
                            <td class="small"><?= htmlspecialchars($doc['email']) ?></td>
                            <td>
                                <?php
                                $badge = [
                                    'pending' => 'bg-warning text-dark',
                                    'verified' => 'bg-success',
                                    'resubmit' => 'bg-info',
                                    'rejected' => 'bg-danger',
                                ][$doc['status']] ?? 'bg-secondary';
                                ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($doc['status']) ?></span>
                            </td>
                            <td class="small"><?= htmlspecialchars($doc['created_at']) ?></td>
                            <td>
                                <a href="index.php?r=admin/reviewlegal&id=<?= $doc['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Review
                                </a>
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
