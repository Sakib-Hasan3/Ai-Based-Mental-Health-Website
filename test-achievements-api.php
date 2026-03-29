<?php
// test-achievements-api.php - Debug script to test the API

session_start();

// Simulate a logged-in user for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Test User';

require_once 'config/database.php';

echo "<h2>Testing Achievements API</h2>";

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        die("<h3 style='color:red'>❌ Database connection failed</h3>");
    }
    
    echo "<h3 style='color:green'>✅ Database connected</h3>";
    
    // Check if tables exist
    $tables = ['achievements_master', 'user_achievements'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color:green'>✅ Table '$table' exists</p>";
            
            // Count rows
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM $table");
            $row = $count_result->fetch_assoc();
            echo "<p>   → Has {$row['cnt']} rows</p>";
        } else {
            echo "<p style='color:red'>❌ Table '$table' NOT FOUND</p>";
            echo "<p style='color:orange'>   📝 Run: SETUP_ACHIEVEMENTS_TABLE.sql</p>";
        }
    }
    
    // Try to fetch achievements
    echo "<h3>Testing Query:</h3>";
    $user_id = 1;
    $sql = "SELECT COUNT(*) as cnt FROM achievements_master WHERE is_active = 1";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p style='color:green'>✅ Query successful. Found {$row['cnt']} active achievements</p>";
    } else {
        echo "<p style='color:red'>❌ Query failed: " . $conn->error . "</p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<h3 style='color:red'>❌ Exception: " . $e->getMessage() . "</h3>";
}
?>

<hr>
<h3>Next Steps:</h3>
<ol>
    <li>If tables don't exist, run: <strong>SETUP_ACHIEVEMENTS_TABLE.sql</strong> in phpMyAdmin</li>
    <li>Refresh this page to verify</li>
    <li>Then go to: <a href="dashboard/achievements.php">dashboard/achievements.php</a></li>
</ol>
