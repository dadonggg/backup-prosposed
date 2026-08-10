<?php
// sql/update_schema.php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
$config = require BASE_PATH . '/app/config/config.php';
$db = $config['db'];
$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

try {
    $pdo = null;
    try {
        $pdo = new PDO($dsn, $db['user'], $db['pass']);
        echo "Connected using config.php database credentials.\n";
    } catch (PDOException $e) {
        echo "Config connection failed: " . $e->getMessage() . "\n";
        echo "Attempting local fallback connection (127.0.0.1, dbname=webdev, user=root, pass='')...\n";
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=webdev;charset=utf8mb4", 'root', '');
        echo "Connected to local database successfully.\n";
    }

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columns = [
        'street_address' => "VARCHAR(255) DEFAULT NULL AFTER gym_address",
        'province' => "VARCHAR(100) DEFAULT NULL AFTER street_address",
        'city_municipality' => "VARCHAR(100) DEFAULT NULL AFTER province",
        'barangay' => "VARCHAR(100) DEFAULT NULL AFTER city_municipality",
        'other_staff_needed' => "TEXT DEFAULT NULL AFTER trainer_count"
    ];

    foreach ($columns as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM legal_documents LIKE '$col'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE legal_documents ADD COLUMN $col $def");
            echo "Added column: $col\n";
        } else {
            echo "Column $col already exists.\n";
        }
    }
    echo "Schema update completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
