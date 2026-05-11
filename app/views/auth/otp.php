<?php
declare(strict_types=1);
$pageTitle = 'OTP verification';
require __DIR__ . '/../partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3 mb-2">Check your email</h1>
                <p class="text-muted small mb-3">Enter the 6-digit code we sent you. It expires in 5 minutes.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?r=auth/otp" class="vstack gap-3">
                    <div>
                        <label class="form-label" for="otp">One-time code</label>
                        <input class="form-control form-control-lg text-center otp-input" id="otp" type="text" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="000000" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Verify</button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="index.php?r=auth/login">Back to login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
