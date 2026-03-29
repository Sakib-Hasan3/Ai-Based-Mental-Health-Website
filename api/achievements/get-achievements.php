<?php
// api/achievements/get-achievements.php - SIMPLIFIED VERSION
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Direct database connection (no class dependency)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

$conn->set_charset("utf8mb4");

// Get category filter
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$recent = isset($_GET['recent']) ? true : false;

// Get user stats for progress calculation
$stats = [
    'mood_count' => 0,
    'journal_count' => 0,
    'session_count' => 0,
    'assessment_count' => 0,
    'streak_days' => 0,
    'community_posts' => 0,
    'resource_views' => 0
];

// Get mood entries count
$result = $conn->query("SELECT COUNT(*) as count FROM mood_entries WHERE user_id = $user_id");
if ($result) {
    $stats['mood_count'] = $result->fetch_assoc()['count'];
}

// Get journal entries count
$result = $conn->query("SELECT COUNT(*) as count FROM journal_entries WHERE user_id = $user_id");
if ($result) {
    $stats['journal_count'] = $result->fetch_assoc()['count'];
}

// Get mentor sessions count
$result = $conn->query("SELECT COUNT(*) as count FROM mentor_enrollments WHERE user_id = $user_id AND status = 'completed'");
if ($result) {
    $stats['session_count'] = $result->fetch_assoc()['count'];
}

// Get assessments count
$result = $conn->query("SELECT COUNT(*) as count FROM assessment_results WHERE user_id = $user_id");
if ($result) {
    $stats['assessment_count'] = $result->fetch_assoc()['count'];
}

// Get community posts count
$result = $conn->query("SELECT COUNT(*) as count FROM community_posts WHERE user_id = $user_id");
if ($result) {
    $stats['community_posts'] = $result->fetch_assoc()['count'];
}

// Simple streak calculation (days with mood entries)
$result = $conn->query("SELECT COUNT(DISTINCT entry_date) as days FROM mood_entries WHERE user_id = $user_id");
if ($result) {
    $stats['streak_days'] = $result->fetch_assoc()['days'];
}

// Get resource views (simplified)
$stats['resource_views'] = 0; // You can implement this later

// If recent achievements requested
if ($recent) {
    $sql = "SELECT ua.*, am.name, am.description, am.badge_icon, am.badge_color, am.points
            FROM user_achievements ua
            JOIN achievements_master am ON ua.achievement_id = am.id
            WHERE ua.user_id = $user_id AND ua.is_claimed = 1
            ORDER BY ua.claimed_at DESC
            LIMIT 10";
    
    $result = $conn->query($sql);
    $recent_achievements = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $row['claimed_at_formatted'] = date('d M Y', strtotime($row['claimed_at']));
            $recent_achievements[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'recent' => $recent_achievements]);
    $conn->close();
    exit();
}

// Get all achievements
$sql = "SELECT am.*, 
        COALESCE(ua.progress_current, 0) as progress_current,
        COALESCE(ua.is_completed, 0) as is_completed,
        COALESCE(ua.is_claimed, 0) as is_claimed
        FROM achievements_master am
        LEFT JOIN user_achievements ua ON am.id = ua.achievement_id AND ua.user_id = $user_id
        WHERE am.is_active = 1";

if ($category !== 'all') {
    $sql .= " AND am.requirement_type = '$category'";
}

$sql .= " ORDER BY am.points ASC";

$result = $conn->query($sql);

$achievements = [];
$completed_count = 0;
$claimed_count = 0;
$total_points = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Calculate progress based on user stats
        $type = $row['requirement_type'];
        $progress = isset($stats[$type]) ? $stats[$type] : 0;
        $row['progress_current'] = min($progress, $row['requirement_value']);
        
        // Check if completed and not marked
        if ($row['progress_current'] >= $row['requirement_value'] && !$row['is_completed']) {
            // Insert or update user achievement
            $target = $row['requirement_value'];
            $insert_sql = "INSERT INTO user_achievements (user_id, achievement_id, progress_current, progress_target, is_completed, completed_at)
                           VALUES ($user_id, {$row['id']}, $target, $target, 1, NOW())
                           ON DUPLICATE KEY UPDATE 
                           is_completed = 1, 
                           completed_at = NOW(),
                           progress_current = $target";
            $conn->query($insert_sql);
            $row['is_completed'] = 1;
        }
        
        if ($row['is_completed']) $completed_count++;
        if ($row['is_claimed']) {
            $claimed_count++;
            $total_points += $row['points'];
        }
        
        $achievements[] = $row;
    }
}

echo json_encode([
    'success' => true,
    'data' => $achievements,
    'stats' => [
        'total_points' => $total_points,
        'completed_count' => $completed_count,
        'claimed_count' => $claimed_count,
        'locked_count' => count($achievements) - $claimed_count
    ]
]);

$conn->close();
?>