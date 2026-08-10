<?php
// sql/run_trainer_directory_migration.php
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

    // 1. Create trainer_profiles table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_profiles` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT NOT NULL UNIQUE,
            `bio` TEXT NULL,
            `expertise` VARCHAR(500) NULL,
            `certifications` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "Created table trainer_profiles successfully.\n";

    // 2. Create trainer_schedules table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_schedules` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `trainer_id` INT NOT NULL,
            `session_date` DATE NOT NULL,
            `session_time` VARCHAR(50) NOT NULL,
            `status` ENUM('available', 'booked') DEFAULT 'available',
            `request_id` INT DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY `idx_trainer_date_time` (`trainer_id`, `session_date`, `session_time`),
            KEY `idx_trainer_schedules_trainer` (`trainer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "Created table trainer_schedules successfully.\n";

    // 3. Create trainer_reviews table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_reviews` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `trainer_id` INT NOT NULL,
            `member_id` INT NOT NULL,
            `rating` INT NOT NULL,
            `review_text` TEXT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            KEY `idx_trainer_reviews_trainer` (`trainer_id`),
            KEY `idx_trainer_reviews_member` (`member_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "Created table trainer_reviews successfully.\n";

    // 4. Alter fitness_service_requests to support booking date, time, and assigned_trainer_id
    $alterations = [
        'booking_date' => "DATE DEFAULT NULL AFTER status",
        'booking_time' => "VARCHAR(50) DEFAULT NULL AFTER booking_date",
        'assigned_trainer_id' => "INT DEFAULT NULL AFTER booking_time",
        'assigned_by' => "INT DEFAULT NULL AFTER assigned_trainer_id",
        'assigned_at' => "DATETIME DEFAULT NULL AFTER assigned_by"
    ];

    foreach ($alterations as $col => $def) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `fitness_service_requests` LIKE '$col'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE `fitness_service_requests` ADD COLUMN `$col` $def");
            echo "Added column $col to fitness_service_requests.\n";
        }
    }

    // 5. Ensure trainer_assignments table exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `trainer_assignments` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `client_id` INT NOT NULL,
            `trainer_id` INT NOT NULL,
            `assigned_by` INT DEFAULT NULL,
            `assigned_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `status` VARCHAR(50) DEFAULT 'active',
            UNIQUE KEY `uniq_client_trainer` (`client_id`, `trainer_id`),
            KEY `idx_ta_client` (`client_id`),
            KEY `idx_ta_trainer` (`trainer_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    ");
    echo "Ensured table trainer_assignments exists.\n";

    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
