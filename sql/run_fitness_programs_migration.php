<?php
/**
 * Run this script ONCE in the browser or CLI to create the fitness_programs table.
 * URL: http://localhost/webdev/sql/run_fitness_programs_migration.php
 */
declare(strict_types=1);

$config = require __DIR__ . '/../app/config/config.php';
$db = $config['db'];
$dsn = "mysql:host={$db['host']};dbname={$db['name']};charset={$db['charset']}";

try {
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die('<b style="color:red">DB connection failed:</b> ' . htmlspecialchars($e->getMessage()));
}

$sql = "
CREATE TABLE IF NOT EXISTS `fitness_programs` (
  `id`                  INT           NOT NULL AUTO_INCREMENT,
  `member_id`           INT           NOT NULL COMMENT 'gym_members.id',
  `user_id`             INT           NOT NULL COMMENT 'users.id',
  `goal`                VARCHAR(100)  NOT NULL,
  `experience_level`    VARCHAR(50)   NOT NULL,
  `available_days`      TINYINT       NOT NULL DEFAULT 3,
  `list_of_weekdays`    VARCHAR(200)  NOT NULL,
  `session_length`      SMALLINT      NOT NULL DEFAULT 60,
  `equipment`           VARCHAR(1000) NOT NULL,
  `injuries_limitations` TEXT          DEFAULT NULL,
  `gym_name`            VARCHAR(200)  NOT NULL,
  `split_name`          VARCHAR(100)  DEFAULT NULL,
  `program_json`        LONGTEXT      NOT NULL COMMENT 'Full Gemini JSON response',
  `generated_at`        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME      DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_member` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    $pdo->exec($sql);
    echo '<div style="font-family:monospace;padding:20px;background:#d4edda;border:1px solid #28a745;border-radius:8px;margin:20px;">';
    echo '<h2 style="color:#155724;margin:0 0 8px">✅ Migration Successful</h2>';
    echo '<p style="margin:0;color:#155724">Table <code>fitness_programs</code> created (or already exists).</p>';
    echo '<p style="margin:8px 0 0;"><a href="../index.php?r=membership/fitnessprogram">→ Go to My Fitness Program</a></p>';
    echo '</div>';
} catch (PDOException $e) {
    echo '<div style="font-family:monospace;padding:20px;background:#f8d7da;border:1px solid #dc3545;border-radius:8px;margin:20px;">';
    echo '<h2 style="color:#721c24;margin:0 0 8px">❌ Migration Failed</h2>';
    echo '<p style="margin:0;">' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}
