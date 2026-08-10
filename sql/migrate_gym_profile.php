<?php
/**
 * Migration: Add gym profile columns to legal_documents
 * Run via: php sql/migrate_gym_profile.php
 */
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config/config.php';
$db     = $config['db'];
$dsn    = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected.\n";

    $columns = [
        'gym_description' => "TEXT DEFAULT NULL COMMENT 'Short description/overview of the gym' AFTER gym_address",
        'opening_hours'   => "JSON DEFAULT NULL COMMENT 'JSON array of {day, open_time, close_time, is_closed}' AFTER gym_description",
    ];

    foreach ($columns as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM legal_documents LIKE '$col'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE legal_documents ADD COLUMN $col $def");
            echo "Added column: $col\n";
        } else {
            echo "Column $col already exists – skipped.\n";
        }
    }

    echo "\nMigration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
