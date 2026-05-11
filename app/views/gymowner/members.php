<?php
declare(strict_types=1);
$pageTitle = 'Gym Members';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-people-fill me-2"></i>Gym Members</h1>
    <p class="text-muted">All verified gym members, membership codes, and payment details.</p>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th><th>Member</th><th>Email</th><th>Code</th>
                        <th>Plan</th><th>Amount</th><th>Start</th><th>Expires</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($members)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No members yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($members as $m): ?>
                        <?php
                            $expired = !empty($m['expiration_date']) && $m['expiration_date'] < date('Y-m-d');
                            $isActive = $m['is_active'] && !$expired;
                        ?>
                        <tr class="<?= $expired ? 'table-light' : '' ?>">
                            <td><?= $m['id'] ?></td>
                            <td><?= htmlspecialchars($m['fullname']) ?></td>
                            <td class="small"><?= htmlspecialchars($m['email']) ?></td>
                            <td><code style="color:#1B6B2A"><?= htmlspecialchars($m['membership_code']) ?></code></td>
                            <td class="small"><?= ucfirst(str_replace('_', ' ', $m['payment_type'] ?? 'N/A')) ?></td>
                            <td class="small">₱<?= number_format((float)($m['payment_amount'] ?? 0), 2) ?></td>
                            <td class="small"><?= htmlspecialchars($m['start_date'] ?? '—') ?></td>
                            <td class="small">
                                <?php if (!empty($m['expiration_date'])): ?>
                                    <span class="<?= $expired ? 'text-danger fw-bold' : '' ?>"><?= htmlspecialchars($m['expiration_date']) ?></span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $isActive ? 'Active' : ($expired ? 'Expired' : 'Inactive') ?>
                                </span>
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
