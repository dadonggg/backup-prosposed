<?php
declare(strict_types=1);
$pageTitle = 'Login';
require __DIR__ . '/../partials/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3 mb-3">Login</h1>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post" action="index.php?r=auth/login" class="vstack gap-3">
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" type="email" name="email" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Sign in</button>
                </form>

                <hr class="my-4">

                <a class="btn btn-outline-secondary w-100" href="index.php?r=auth/google">Sign in with Google</a>

                <p class="text-center text-muted small mt-3 mb-0">
                    <a href="index.php?r=auth/register">Create an account</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
