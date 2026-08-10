<?php
$pdo = new PDO("mysql:host=localhost;dbname=webdev;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Checking membership_applications table structure:\n\n";

$stmt = $pdo->query("DESCRIBE membership_applications");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($columns as $col) {
    echo $col['Field'] . " - " . $col['Type'] . " - " . ($col['Null'] === 'YES' ? 'NULL' : 'NOT NULL') . "\n";
}

echo "\n\nChecking if membership_plan_id exists: ";
$stmt = $pdo->query("SHOW COLUMNS FROM membership_applications LIKE 'membership_plan_id'");
echo $stmt->rowCount() > 0 ? "YES\n" : "NO\n";

echo "Checking if training_package_id exists: ";
$stmt = $pdo->query("SHOW COLUMNS FROM membership_applications LIKE 'training_package_id'");
echo $stmt->rowCount() > 0 ? "YES\n" : "NO\n";
