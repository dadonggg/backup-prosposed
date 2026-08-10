<?php
// Quick diagnostic to check campaigns in database
require_once 'app/core/Database.php';

$pdo = \App\Core\Database::pdo();

echo "<h2>Campaign Diagnostic</h2>";

// Check if ad_campaigns table exists
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM ad_campaigns");
    $count = $stmt->fetchColumn();
    echo "<p>✅ ad_campaigns table exists with {$count} campaigns</p>";
} catch (PDOException $e) {
    echo "<p>❌ ad_campaigns table doesn't exist: " . $e->getMessage() . "</p>";
    exit;
}

// List all campaigns
echo "<h3>All Campaigns:</h3>";
$stmt = $pdo->query("SELECT id, title, status, start_date, end_date, target_audience FROM ad_campaigns ORDER BY created_at DESC");
$campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($campaigns)) {
    echo "<p>No campaigns found in database.</p>";
} else {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Status</th><th>Start Date</th><th>End Date</th><th>Target</th><th>Visible?</th></tr>";
    foreach ($campaigns as $c) {
        $today = date('Y-m-d');
        $isInDateRange = ($today >= $c['start_date'] && $today <= $c['end_date']);
        $visible = ($c['status'] === 'published' && $isInDateRange) ? '✅ YES' : '❌ NO';
        
        echo "<tr>";
        echo "<td>{$c['id']}</td>";
        echo "<td>{$c['title']}</td>";
        echo "<td>{$c['status']}</td>";
        echo "<td>{$c['start_date']}</td>";
        echo "<td>{$c['end_date']}</td>";
        echo "<td>{$c['target_audience']}</td>";
        echo "<td>{$visible}</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Show what should be visible today
echo "<h3>Currently Active Campaigns (published + in date range):</h3>";
$stmt = $pdo->prepare("
    SELECT id, title, service_description, start_date, end_date 
    FROM ad_campaigns 
    WHERE status = 'published' 
    AND CURDATE() BETWEEN start_date AND end_date
");
$stmt->execute();
$active = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($active)) {
    echo "<p>❌ No active campaigns found. Either:</p>";
    echo "<ul>";
    echo "<li>No campaigns have status='published', OR</li>";
    echo "<li>All campaigns are outside their date range</li>";
    echo "</ul>";
    echo "<p><strong>Solution:</strong> Go to Marketing Officer dashboard and either:</p>";
    echo "<ol>";
    echo "<li>Change campaign status to 'published', OR</li>";
    echo "<li>Adjust the date range to include today</li>";
    echo "</ol>";
} else {
    echo "<p>✅ Found " . count($active) . " active campaigns:</p>";
    echo "<ul>";
    foreach ($active as $a) {
        echo "<li><strong>{$a['title']}</strong> - Valid from {$a['start_date']} to {$a['end_date']}</li>";
    }
    echo "</ul>";
    echo "<p>These should appear on the member dashboard!</p>";
}

echo "<hr>";
echo "<p><a href='index.php?r=member/dashboard'>→ Go to Member Dashboard</a></p>";
echo "<p><a href='index.php?r=marketing/dashboard'>→ Go to Marketing Dashboard</a></p>";
?>
