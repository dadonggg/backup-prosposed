<?php
declare(strict_types=1);
$pageTitle = 'Apply for Membership';
require __DIR__ . '/../partials/header.php';

$autoFirstName = $user['firstname'] ?? '';
$autoLastName  = $user['lastname'] ?? '';
$autoMI        = $user['middle_initial'] ?? '';
?>

<div class="mb-4">
    <h1 class="h3 mb-1"><i class="bi bi-card-checklist me-2"></i>Apply for Gym Membership</h1>
    <p class="text-muted">Fill out the form below to apply for gym membership.</p>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-1"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($memberApp && $memberApp['status'] === 'pending'): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-hourglass-split display-3 text-warning mb-3"></i>
            <h2 class="h5">Membership Application Pending</h2>
            <p class="text-muted">Your application is waiting for approval.</p>
            <?php if (!empty($memberApp['payment_type'])): ?>
                <p class="small">
                    <strong>Plan:</strong> <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $memberApp['payment_type']))) ?>
                    — <strong>Amount:</strong> ₱<?= number_format((float)($memberApp['payment_amount'] ?? 0), 2) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($memberApp && $memberApp['status'] === 'verified'): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle display-3 text-info mb-3"></i>
            <h2 class="h5">Application Verified – Awaiting Payment</h2>
            
            <?php if (($memberApp['payment_mode'] ?? 'cash') === 'online'): ?>
                <!-- Online Payment Mode -->
                <p class="text-muted">Your application has been verified. Please complete your payment online.</p>
                <div class="alert alert-info d-inline-block text-start mt-2">
                    <strong>Payment Details:</strong><br>
                    Service: <strong><?= htmlspecialchars($memberApp['payment_type'] ?? 'N/A') ?></strong><br>
                    Amount Due: <strong>₱<?= number_format((float)($memberApp['payment_amount'] ?? 0), 2) ?></strong><br>
                    Payment Mode: <strong class="text-primary">Online Payment (PayMongo)</strong>
                </div>
                
                <?php if (!empty($paymongoLink)): ?>
                    <!-- PayMongo Payment Link -->
                    <?php if (empty($memberApp['payment_submitted_at'])): ?>
                    <div class="mt-4">
                        <a href="<?= htmlspecialchars($paymongoLink) ?>" target="_blank" class="btn btn-primary btn-lg mb-3">
                            <i class="bi bi-credit-card me-2"></i>Pay Now via PayMongo
                        </a>
                        <form method="post" action="index.php?r=membership/notifypayment" class="d-inline-block ms-2">
                            <input type="hidden" name="id" value="<?= $memberApp['id'] ?>">
                            <button type="submit" class="btn btn-success btn-lg mb-3" onclick="return confirm('Only click this IF you have successfully completed the PayMongo payment. Proceed?')">
                                <i class="bi bi-check-circle me-1"></i>I Have Paid
                            </button>
                        </form>
                        <p class="small text-muted">
                            <i class="bi bi-shield-check"></i> Secure payment powered by PayMongo
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 alert alert-success d-inline-block text-start">
                        <i class="bi bi-check-circle-fill me-2"></i><strong>Payment Submitted!</strong><br>
                        The Administrative Officer is verifying your payment and will generate your membership code shortly.
                    </div>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- PayMongo Not Configured -->
                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Online payment is being set up.</strong><br>
                        The gym owner is configuring PayMongo. Please check back soon or contact the gym.
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <!-- Cash Payment Mode -->
                <p class="text-muted">Please complete your payment at the gym.</p>
                <div class="alert alert-info d-inline-block text-start mt-2">
                    <strong>Payment Details:</strong><br>
                    Service: <strong><?= htmlspecialchars($memberApp['payment_type'] ?? 'N/A') ?></strong><br>
                    Amount Due: <strong>₱<?= number_format((float)($memberApp['payment_amount'] ?? 0), 2) ?></strong><br>
                    Payment Mode: <strong class="text-success">Cash (Pay at Gym)</strong>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($memberApp['preferred_trainer_id'])): ?>
                <p class="mt-2 small text-muted"><i class="bi bi-person-check"></i> A trainer has been assigned to you.</p>
            <?php endif; ?>
            <?php if (!empty($memberApp['admin_feedback'])): ?>
                <div class="alert alert-secondary mt-3 text-start d-inline-block">
                    <i class="bi bi-chat-left-text me-1"></i><strong>Note:</strong> <?= htmlspecialchars($memberApp['admin_feedback']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($memberApp && $memberApp['status'] === 'approved'): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-check-circle display-3 text-success mb-3"></i>
            <h2 class="h5">Membership Approved!</h2>
            <p class="text-muted">Check your membership code on the dashboard or verify it below.</p>
            <a href="index.php?r=membership/verifycode" class="btn btn-primary btn-sm mt-2"><i class="bi bi-qr-code me-1"></i>Verify Membership</a>
            <?php if (!empty($memberApp['admin_feedback'])): ?>
                <div class="alert alert-success mt-3 text-start d-inline-block">
                    <i class="bi bi-chat-left-text me-1"></i> <?= htmlspecialchars($memberApp['admin_feedback']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($memberApp && $memberApp['status'] === 'resubmit'): ?>
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i><strong>Resubmission Required.</strong>
        Please review the feedback and resubmit.
    </div>
    <?php if (!empty($memberApp['admin_feedback'])): ?>
        <div class="alert alert-info">
            <i class="bi bi-chat-left-text me-1"></i><strong>Feedback:</strong><br>
            <?= htmlspecialchars($memberApp['admin_feedback']) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Resubmit Membership Application</h2></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="vstack gap-3">
                <input type="hidden" name="action" value="resubmit">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="first_name" type="text" name="first_name"
                               value="<?= htmlspecialchars($memberApp['first_name'] ?? $autoFirstName) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="last_name" type="text" name="last_name"
                               value="<?= htmlspecialchars($memberApp['last_name'] ?? $autoLastName) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="middle_initial">M.I.</label>
                        <input class="form-control" id="middle_initial" type="text" name="middle_initial" maxlength="5"
                               value="<?= htmlspecialchars($memberApp['middle_initial'] ?? $autoMI) ?>">
                    </div>
                </div>
                <div>
                    <label class="form-label" for="phone_number">Phone Number <span class="text-danger">*</span></label>
                    <input class="form-control" id="phone_number" type="tel" name="phone_number"
                           value="<?= htmlspecialchars($memberApp['phone_number'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="form-label" for="service_id_resubmit">Select Service <span class="text-danger">*</span></label>
                    <select class="form-select" name="service_id" id="service_id_resubmit" required onchange="updateServicePriceResubmit(this)">
                        <option value="">— Select a service —</option>
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>" 
                                        data-price="<?= $s['member_price'] ?>"
                                        <?= ($memberApp['service_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name']) ?> — ₱<?= number_format((float)$s['member_price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div id="price_display_resubmit" class="alert alert-info" style="<?= !empty($memberApp['service_id']) ? '' : 'display:none;' ?>">
                    <strong>Amount to Pay:</strong> <span id="price_amount_resubmit">₱<?= number_format((float)($memberApp['payment_amount'] ?? 0), 2) ?></span>
                </div>
                <div>
                    <label class="form-label" for="payment_mode_resubmit">Payment Mode <span class="text-danger">*</span></label>
                    <select class="form-select" name="payment_mode" id="payment_mode_resubmit" required>
                        <?php $pm = $memberApp['payment_mode'] ?? 'cash'; ?>
                        <option value="cash" <?= $pm === 'cash' ? 'selected' : '' ?>>Cash Payment (Pay at Gym)</option>
                        <option value="online" <?= $pm === 'online' ? 'selected' : '' ?>>Online Payment (PayMongo)</option>
                    </select>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-arrow-repeat me-1"></i>Resubmit Application</button>
            </form>
        </div>
    </div>

<?php else: ?>
    <?php if ($memberApp && $memberApp['status'] === 'rejected'): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i><strong>Application Rejected:</strong>
            <?= htmlspecialchars($memberApp['admin_feedback'] ?? 'You may re-apply.') ?>
        </div>
    <?php endif; ?>

    <!-- Available Services Card -->
    <?php if (!empty($services)): ?>
    <div class="card mb-4">
        <div class="card-header px-3 py-2"><h2 class="h6 mb-0"><i class="bi bi-tags me-1"></i>Available Services & Pricing</h2></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Service</th><th>Member Price</th><th>Non-Member Price</th></tr></thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($s['name']) ?></strong>
                                <?php if ($s['description']): ?><br><small class="text-muted"><?= htmlspecialchars($s['description']) ?></small><?php endif; ?>
                            </td>
                            <td class="fw-bold text-success">₱<?= number_format((float)$s['member_price'], 2) ?></td>
                            <td>₱<?= number_format((float)$s['non_member_price'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header px-3 py-2"><h2 class="h6 mb-0">Membership Application Form</h2></div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" class="vstack gap-3">
                <input type="hidden" name="action" value="<?= ($memberApp && $memberApp['status'] === 'rejected') ? 'resubmit' : 'submit' ?>">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label" for="first_name">First Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="first_name" type="text" name="first_name"
                               value="<?= htmlspecialchars($autoFirstName) ?>" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label" for="last_name">Last Name <span class="text-danger">*</span></label>
                        <input class="form-control" id="last_name" type="text" name="last_name"
                               value="<?= htmlspecialchars($autoLastName) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label" for="middle_initial">M.I.</label>
                        <input class="form-control" id="middle_initial" type="text" name="middle_initial" maxlength="5"
                               value="<?= htmlspecialchars($autoMI) ?>">
                    </div>
                </div>
                <div>
                    <label class="form-label" for="phone_number">Phone Number <span class="text-danger">*</span></label>
                    <input class="form-control" id="phone_number" type="tel" name="phone_number" required>
                </div>
                <div>
                    <label class="form-label" for="service_id">Select Service <span class="text-danger">*</span></label>
                    <select class="form-select" name="service_id" id="service_id" required onchange="updateServicePrice(this)">
                        <option value="">— Select a service —</option>
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= $s['id'] ?>" 
                                        data-price="<?= $s['member_price'] ?>"
                                        data-name="<?= htmlspecialchars($s['name']) ?>"
                                        data-with-trainer="<?= stripos($s['name'], 'trainer') !== false ? '1' : '0' ?>">
                                    <?= htmlspecialchars($s['name']) ?> — ₱<?= number_format((float)$s['member_price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No services available</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">
                        <i class="bi bi-info-circle me-1"></i>If you select a service with <strong>"Personal Trainer"</strong>, the Administrative Officer will assign a trainer to you after approval.
                    </div>
                </div>
                <div id="price_display" class="alert alert-info" style="display:none;">
                    <strong>Amount to Pay:</strong> <span id="price_amount">₱0.00</span>
                </div>
                <div>
                    <label class="form-label" for="payment_mode">Payment Mode <span class="text-danger">*</span></label>
                    <select class="form-select" name="payment_mode" id="payment_mode" required>
                        <option value="cash">Cash Payment (Pay at Gym)</option>
                        <option value="online">Online Payment (PayMongo)</option>
                    </select>
                    <div class="form-text">
                        <i class="bi bi-credit-card me-1"></i>Choose <strong>Cash</strong> to pay at the gym, or <strong>Online</strong> to pay now via PayMongo.
                    </div>
                </div>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i>Submit Application</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function updateServicePrice(select) {
    var priceDisplay = document.getElementById('price_display');
    var priceAmount = document.getElementById('price_amount');
    
    if (select.value) {
        var option = select.options[select.selectedIndex];
        var price = parseFloat(option.getAttribute('data-price'));
        priceAmount.textContent = '₱' + price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        priceDisplay.style.display = '';
    } else {
        priceDisplay.style.display = 'none';
    }
}

function updateServicePriceResubmit(select) {
    var priceDisplay = document.getElementById('price_display_resubmit');
    var priceAmount = document.getElementById('price_amount_resubmit');
    
    if (select.value) {
        var option = select.options[select.selectedIndex];
        var price = parseFloat(option.getAttribute('data-price'));
        priceAmount.textContent = '₱' + price.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        priceDisplay.style.display = '';
    } else {
        priceDisplay.style.display = 'none';
    }
}
</script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
