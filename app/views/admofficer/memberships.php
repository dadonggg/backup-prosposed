<?php
declare(strict_types=1);
$pageTitle = 'Membership Applications';
require __DIR__ . '/../partials/header.php';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-person-plus me-2"></i>Membership Applications</h1>
    <p class="text-muted">Review membership forms submitted by fitness enthusiasts.</p>
</div>

<?php
$paymentPending = array_filter($apps ?? [], fn($a) => !empty($a['payment_submitted_at']) && $a['status'] === 'verified');
if (count($paymentPending) > 0):
?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
    <i class="bi bi-cash-coin fs-5"></i>
    <div>
        <strong><?= count($paymentPending) ?> applicant(s) have submitted online payment</strong>
        and are waiting for confirmation &amp; code generation.
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th><th>Account</th><th>Full Name</th><th>Phone</th>
                        <th>Plan / Amount</th><th>Status</th><th>Payment</th><th>Date</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($apps)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-4">No membership applications.</td></tr>
                    <?php else: ?>
                        <?php foreach ($apps as $a):
                            $paySubmitted = !empty($a['payment_submitted_at']);
                            $rowClass = ($paySubmitted && $a['status'] === 'verified') ? 'table-warning' : '';
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td><?= $a['id'] ?></td>
                            <td class="small"><?= htmlspecialchars($a['fullname']) ?></td>
                            <td><?= htmlspecialchars($a['first_name'] . ' ' . ($a['middle_initial'] ? $a['middle_initial'] . '. ' : '') . $a['last_name']) ?></td>
                            <td class="small"><?= htmlspecialchars($a['phone_number']) ?></td>
                            <td class="small">
                                <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $a['payment_type'] ?? 'N/A'))) ?><br>
                                <strong>&#8369;<?= number_format((float)($a['payment_amount'] ?? 0), 2) ?></strong>
                                <span class="badge bg-<?= ($a['payment_mode'] ?? 'cash') === 'online' ? 'info' : 'secondary' ?> ms-1">
                                    <?= ucfirst($a['payment_mode'] ?? 'cash') ?>
                                </span>
                            </td>
                            <td>
                                <?php $badge = match($a['status']) {
                                    'pending'  => 'bg-warning text-dark',
                                    'verified' => 'bg-info',
                                    'approved' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'resubmit' => 'bg-secondary',
                                    default    => 'bg-secondary'
                                }; ?>
                                <span class="badge <?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                            </td>
                            <td>
                                <?php if ($paySubmitted): ?>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-cash-coin me-1"></i>Paid</span>
                                    <div class="text-muted" style="font-size:.65rem"><?= date('M j g:i A', strtotime($a['payment_submitted_at'])) ?></div>
                                <?php elseif (($a['payment_mode'] ?? 'cash') === 'online' && $a['status'] === 'verified'): ?>
                                    <span class="badge bg-secondary">Awaiting Payment</span>
                                <?php else: ?>
                                    <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= htmlspecialchars($a['created_at']) ?></td>
                            <td>
                                <a href="index.php?r=admofficer/review&id=<?= $a['id'] ?>"
                                   class="btn btn-<?= ($paySubmitted && $a['status'] === 'verified') ? 'warning' : 'outline-primary' ?> btn-sm">
                                    <i class="bi bi-<?= ($paySubmitted && $a['status'] === 'verified') ? 'cash-coin' : 'eye' ?>"></i>
                                    <?= ($paySubmitted && $a['status'] === 'verified') ? 'Confirm &amp; Code' : 'Review' ?>
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
