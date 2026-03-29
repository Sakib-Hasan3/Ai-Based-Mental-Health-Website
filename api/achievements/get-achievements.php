<?php
// api/achievements/get-achievements.php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$recent = isset($_GET['recent']) ? true : false;

$db = new Database();
$conn = $db->getConnection();

// Get user's current stats for progress calculation
$stats = getUserStats($conn, $user_id);

// Build query for achievements
if ($recent) {
    // Get recent claimed achievements
    $sql = "SELECT ua.*, am.name, am.description, am.badge_icon, am.badge_color, am.points
            FROM user_achievements ua
            JOIN achievements_master am ON ua.achievement_id = am.id
            WHERE ua.user_id = ? AND ua.is_claimed = 1
            ORDER BY ua.claimed_at DESC
            LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $recent_achievements = [];
    while ($row = $result->fetch_assoc()) {
        $row['claimed_at_formatted'] = date('d M Y', strtotime($row['claimed_at']));
        $recent_achievements[] = $row;
    }
    
    echo json_encode(['success' => true, 'recent' => $recent_achievements]);
    $conn->close();
    exit();
}

// Get all achievements with user progress
$sql = "SELECT am.*, 
        COALESCE(ua.progress_current, 0) as progress_current,
        COALESCE(ua.is_completed, 0) as is_completed,
        COALESCE(ua.is_claimed, 0) as is_claimed
        FROM achievements_master am
        LEFT JOIN user_achievements ua ON am.id = ua.achievement_id AND ua.user_id = ?
        WHERE am.is_active = 1";

$params = [$user_id];
$types = "i";

if ($category !== 'all') {
    $sql .= " AND am.requirement_type = ?";
    $params[] = $category;
    $types .= "s";
}

$sql .= " ORDER BY am.points ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$achievements = [];
$completed_count = 0;
$claimed_count = 0;
$total_points = 0;

while ($row = $result->fetch_assoc()) {
    // Calculate progress based on requirement type
    $progress = calculateProgress($row, $stats);
    $row['progress_current'] = min($progress, $row['requirement_value']);
    
    // Check if completed (not already marked)
    if ($row['progress_current'] >= $row['requirement_value'] && !$row['is_completed']) {
        // Auto-mark as completed
        markAchievementCompleted($conn, $user_id, $row['id']);
        $row['is_completed'] = 1;
    }
    
    if ($row['is_completed']) $completed_count++;
    if ($row['is_claimed']) {
        $claimed_count++;
        $total_points += $row['points'];
    }
    
    $achievements[] = $row;
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

// ==================== HELPER FUNCTIONS ====================

function getUserStats($conn, $user_id) {
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
    $stats['mood_count'] = $result->fetch_assoc()['count'];
    
    // Get journal entries count
    $result = $conn->query("SELECT COUNT(*) as count FROM journal_entries WHERE user_id = $user_id");
    $stats['journal_count'] = $result->fetch_assoc()['count'];
    
    // Get mentor sessions count
    $result = $conn->query("SELECT COUNT(*) as count FROM mentor_enrollments WHERE user_id = $user_id AND status = 'completed'");
    $stats['session_count'] = $result->fetch_assoc()['count'];
    
    // Get assessments count
    $result = $conn->query("SELECT COUNT(*) as count FROM assessment_results WHERE user_id = $user_id");
    $stats['assessment_count'] = $result->fetch_assoc()['count'];
    
    // Get community posts count
    $result = $conn->query("SELECT COUNT(*) as count FROM community_posts WHERE user_id = $user_id");
    $stats['community_posts'] = $result->fetch_assoc()['count'];
    
    // Calculate streak days (simplified)
    $stats['streak_days'] = calculateStreak($conn, $user_id);
    
    return $stats;
}

function calculateStreak($conn, $user_id) {
    // Simple streak calculation based on mood entries
    $result = $conn->query("SELECT COUNT(DISTINCT entry_date) as days 
                            FROM mood_entries 
                            WHERE user_id = $user_id 
                            AND entry_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    return $result->fetch_assoc()['days'];
}

function calculateProgress($achievement, $stats) {
    $type = $achievement['requirement_type'];
    return isset($stats[$type]) ? $stats[$type] : 0;
}

function markAchievementCompleted($conn, $user_id, $achievement_id) {
    $sql = "UPDATE user_achievements 
            SET is_completed = 1, completed_at = NOW() 
            WHERE user_id = ? AND achievement_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $achievement_id);
    $stmt->execute();
    
    if ($stmt->affected_rows == 0) {
        // Insert if not exists
        $sql = "INSERT INTO user_achievements (user_id, achievement_id, progress_current, progress_target, is_completed, completed_at)
                VALUES (?, ?, ?, ?, 1, NOW())";
        $stmt = $conn->prepare($sql);
        $target = $achievement['requirement_value'];
        $stmt->bind_param("iiii", $user_id, $achievement_id, $target, $target);
        $stmt->execute();
    }
    $stmt->close();
}
?>