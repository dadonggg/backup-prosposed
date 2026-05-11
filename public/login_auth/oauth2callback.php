<?php

declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__, 2));

require BASE_PATH . '/app/config/config.php';
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}
require BASE_PATH . '/app/core/Autoload.php';

// Forward Google OAuth callback into the existing router.
$_GET['r'] = 'auth/googlecallback';

$app = new App\Core\App();
$app->run();
