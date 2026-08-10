<?php
/**
 * Quick Database Table Checker
 * Run this file in your browser: http://localhost/webdev/check_tables.php
 * This will verify if the custom_exercises and custom_equipment tables exist
 */

// Database configuration
$host = 'localhost';
$dbname = 'webdev';
$username = 'root';
$password = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Table Checker</h1>";
    echo "<p>Database: <strong>$dbname</strong></p>";
    echo "<hr>";
    
    // Check custom_exercises table
    echo "<h2>1. Checking custom_exercises table...</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE custom_exercises");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color: green; font-weight: bold;'>✅ Table EXISTS!</p>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count records
        $count = $pdo->query("SELECT COUNT(*) FROM custom_exercises")->fetchColumn();
        echo "<p>Total custom exercises: <strong>$count</strong></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ Table DOES NOT EXIST!</p>";
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        echo "<p style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
        echo "<strong>⚠️ ACTION REQUIRED:</strong><br>";
        echo "You need to run <code>FIX_CUSTOM_EXERCISES_AND_EQUIPMENT.sql</code> in phpMyAdmin!";
        echo "</p>";
    }
    
    echo "<hr>";
    
    // Check custom_equipment table
    echo "<h2>2. Checking custom_equipment table...</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE custom_equipment");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p style='color: green; font-weight: bold;'>✅ Table EXISTS!</p>";
        echo "<table border='1' cellpadding='5' cellspacing='0'>";
        echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count records
        $count = $pdo->query("SELECT COUNT(*) FROM custom_equipment")->fetchColumn();
        echo "<p>Total custom equipment: <strong>$count</strong></p>";
        
    } catch (PDOException $e) {
        echo "<p style='color: red; font-weight: bold;'>❌ Table DOES NOT EXIST!</p>";
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        echo "<p style='background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;'>";
        echo "<strong>⚠️ ACTION REQUIRED:</strong><br>";
        echo "You need to run <code>FIX_CUSTOM_EXERCISES_AND_EQUIPMENT.sql</code> in phpMyAdmin!";
        echo "</p>";
    }
    
    echo "<hr>";
    echo "<h2>Summary</h2>";
    
    // Final check
    $customExercisesExists = false;
    $customEquipmentExists = false;
    
    try {
        $pdo->query("SELECT 1 FROM custom_exercises LIMIT 1");
        $customExercisesExists = true;
    } catch (PDOException $e) {
        // Table doesn't exist
    }
    
    try {
        $pdo->query("SELECT 1 FROM custom_equipment LIMIT 1");
        $customEquipmentExists = true;
    } catch (PDOException $e) {
        // Table doesn't exist
    }
    
    if ($customExercisesExists && $customEquipmentExists) {
        echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✅ ALL TABLES EXIST! You're good to go!</p>";
        echo "<p>You can now:</p>";
        echo "<ul>";
        echo "<li>Create custom exercises from the trainer dashboard</li>";
        echo "<li>Add custom equipment to the dropdown</li>";
        echo "<li>Build workout plans with sets and reps</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red; font-size: 18px; font-weight: bold;'>❌ TABLES MISSING!</p>";
        echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
        echo "<h3>🔧 What to do:</h3>";
        echo "<ol>";
        echo "<li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>";
        echo "<li>Select the <strong>webdev</strong> database</li>";
        echo "<li>Click the <strong>SQL</strong> tab</li>";
        echo "<li>Open the file: <code>FIX_CUSTOM_EXERCISES_AND_EQUIPMENT.sql</code></li>";
        echo "<li>Copy all the SQL code and paste it into phpMyAdmin</li>";
        echo "<li>Click <strong>Go</strong> to execute</li>";
        echo "<li>Refresh this page to verify</li>";
        echo "</ol>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<h1 style='color: red;'>Database Connection Error</h1>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration:</p>";
    echo "<ul>";
    echo "<li>Host: $host</li>";
    echo "<li>Database: $dbname</li>";
    echo "<li>Username: $username</li>";
    echo "<li>Make sure XAMPP/MySQL is running</li>";
    echo "</ul>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h1 {
    color: #333;
    border-bottom: 3px solid #007bff;
    padding-bottom: 10px;
}
h2 {
    color: #555;
    margin-top: 20px;
}
table {
    background: white;
    width: 100%;
    margin: 10px 0;
}
th {
    background: #007bff;
    color: white;
    padding: 8px;
    text-align: left;
}
td {
    padding: 8px;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
code {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: monospace;
}
</style>
