<?php
// sql/run_messaging_and_profile_migration.php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/app/config/config.php';
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}
require BASE_PATH . '/app/core/Autoload.php';

use App\Core\Container;
use App\Core\Database;

$config = require BASE_PATH . '/app/config/config.php';
Container::set('config', $config);

try {
    $pdo = Database::pdo();
    echo "Connected to database successfully.\n";

    // 1. Add profile_picture_url to users table if missing
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'profile_picture_url'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture_url VARCHAR(255) DEFAULT NULL AFTER email");
        echo "Added column 'profile_picture_url' to 'users' table.\n";
    } else {
        echo "Column 'profile_picture_url' already exists in 'users' table.\n";
    }

    // 2. Create messages table if missing
    $sqlMessages = "CREATE TABLE IF NOT EXISTS `messages` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `sender_id` INT NOT NULL,
      `receiver_id` INT NOT NULL,
      `request_id` INT DEFAULT NULL,
      `message_text` TEXT NOT NULL,
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
      `read_at` DATETIME DEFAULT NULL,
      INDEX `idx_sender_receiver` (`sender_id`, `receiver_id`),
      INDEX `idx_receiver_read` (`receiver_id`, `read_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlMessages);
    echo "Table 'messages' created or already exists.\n";

    echo "Migration completed successfully!\n";
} catch (Throwable $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
}
