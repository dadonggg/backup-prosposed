<?php
declare(strict_types=1);
$pageTitle = 'Email verification';
require __DIR__ . '/../partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 text-center">
                <h1 class="h3 mb-3">Email verification</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-start" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success text-start" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <a class="btn btn-primary" href="index.php?r=auth/login">Go to login</a>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
