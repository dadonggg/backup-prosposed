<?php
declare(strict_types=1);
$pageTitle = 'Register';
require __DIR__ . '/../partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Create account</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?r=auth/register" class="vstack gap-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="firstname">First name</label>
                            <input class="form-control" id="firstname" type="text" name="firstname" autocomplete="given-name" maxlength="50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="lastname">Last name</label>
                            <input class="form-control" id="lastname" type="text" name="lastname" autocomplete="family-name" maxlength="50" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4 col-lg-3">
                            <label class="form-label" for="middle_initial">Middle initial</label>
                            <input class="form-control" id="middle_initial" type="text" name="middle_initial" autocomplete="additional-name" maxlength="5" placeholder="e.g. M">
                            <div class="form-text">Optional</div>
                        </div>
                        <div class="col-md-4 col-lg-5">
                            <label class="form-label" for="birth_date">Birth Date</label>
                            <input class="form-control" id="birth_date" type="date" name="birth_date" max="<?= date('Y-m-d') ?>" required>
                            <div class="form-text">Age will be computed automatically</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="height_cm">Height (cm)</label>
                            <input class="form-control" id="height_cm" type="number" name="height_cm" inputmode="decimal" min="50" max="272" step="0.1" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="weight_kg">Weight (kg)</label>
                            <input class="form-control" id="weight_kg" type="number" name="weight_kg" inputmode="decimal" min="20" max="400" step="0.1" required>
                        </div>
                    </div>

                    <hr class="text-secondary opacity-25 my-1">

                    <div>
                        <label class="form-label" for="reg-email">Email</label>
                        <input class="form-control" id="reg-email" type="email" name="email" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="form-label" for="reg-password">Password</label>
                        <input class="form-control" id="reg-password" type="password" name="password" autocomplete="new-password" required minlength="8">
                    </div>
                    <div>
                        <label class="form-label" for="password_confirm">Confirm password</label>
                        <input class="form-control" id="password_confirm" type="password" name="password_confirm" autocomplete="new-password" required minlength="8">
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Create account</button>
                </form>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="index.php?r=auth/login">Back to login</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
