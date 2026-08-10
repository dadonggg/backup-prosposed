<?php
// Debug file - DELETE THIS after fixing the error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>PHP Version: " . phpversion() . "</h2>";
echo "<h3>Checking files...</h3>";

$checks = [
    'public/index.php'        => __DIR__ . '/public/index.php',
    'app/config/config.php'   => __DIR__ . '/app/config/config.php',
    'app/core/App.php'        => __DIR__ . '/app/core/App.php',
    'app/core/Database.php'   => __DIR__ . '/app/core/Database.php',
    'app/core/Autoload.php'   => __DIR__ . '/app/core/Autoload.php',
    'vendor/autoload.php'     => __DIR__ . '/vendor/autoload.php',
];

foreach ($checks as $label => $path) {
    $exists = file_exists($path) ? '✅' : '❌ MISSING';
    echo "<p>$exists — $label</p>";
}

echo "<h3>Loading app now...</h3>";
try {
    require __DIR__ . '/public/index.php';
} catch (Throwable $e) {
    echo "<div style='color:red; background:#ffe; padding:10px; border:2px solid red;'>";
    echo "<strong>ERROR:</strong> " . get_class($e) . "<br>";
    echo "<strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "<strong>File:</strong> " . $e->getFile() . " (line " . $e->getLine() . ")<br>";
    echo "<strong>Trace:</strong><pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
}
