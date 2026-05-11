<?php
declare(strict_types=1);
$pageTitle = 'Notifications';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="h3 mb-1"><i class="bi bi-bell me-2"></i>Notifications</h1>
            <p class="text-muted">All your system notifications.</p>
        </div>
        <a href="index.php?r=notification/markallread" class="btn btn-outline-primary btn-sm"><i class="bi bi-check-all me-1"></i>Mark All as Read</a>
    </div>
</div>

<?php if (empty($notifications)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bell-slash display-3 text-muted mb-3"></i>
            <p class="text-muted">No notifications yet.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <?php foreach ($notifications as $n):
                $icon = match($n['type'] ?? 'info') {
                    'success'=>'bi-check-circle-fill text-success',
                    'warning'=>'bi-exclamation-triangle-fill text-warning',
                    'danger'=>'bi-x-circle-fill text-danger',
                    default=>'bi-info-circle-fill text-info'
                };
                $bgStyle = $n['is_read'] ? '' : 'background:rgba(27,107,42,.04);';
                $link = $n['link'] ? 'index.php?r=notification/markread&id='.$n['id'].'&link='.urlencode($n['link']) : '#';
            ?>
            <a href="<?= $link ?>" class="d-flex gap-3 p-3 text-decoration-none border-bottom" style="<?= $bgStyle ?>transition:background .15s">
                <div class="flex-shrink-0 mt-1">
                    <i class="bi <?= $icon ?>" style="font-size:1.2rem"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-bold small" style="color:var(--nf-text)">
                        <?= htmlspecialchars($n['title']) ?>
                        <?php if (!$n['is_read']): ?><span class="badge bg-primary ms-1" style="font-size:.6rem">NEW</span><?php endif; ?>
                    </div>
                    <div class="small" style="color:var(--nf-text-secondary)"><?= htmlspecialchars($n['message']) ?></div>
                    <div class="small mt-1" style="color:var(--nf-muted);font-size:.75rem">
                        <i class="bi bi-clock me-1"></i><?= htmlspecialchars($n['created_at']) ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../partials/footer.php'; ?>
