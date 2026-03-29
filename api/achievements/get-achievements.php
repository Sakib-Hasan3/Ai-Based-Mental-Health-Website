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
    
    $stmt->close();
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
    $row['progress_current'] = min($progress, $row['requirement_value'] ?? 0);
    
    // Check if completed (not already marked)
    if (($row['progress_current'] ?? 0) >= ($row['requirement_value'] ?? 0) && !($row['is_completed'] ?? false)) {
        // Auto-mark as completed
        markAchievementCompleted($conn, $user_id, $row['id'], $row);
        $row['is_completed'] = 1;
    }
    
    if ($row['is_completed'] ?? false) $completed_count++;
    if ($row['is_claimed'] ?? false) {
        $claimed_count++;
        $total_points += $row['points'] ?? 0;
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
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM mood_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['mood_count'] = $row ? $row['count'] : 0;
    $stmt->close();
    
    // Get journal entries count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM journal_entries WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['journal_count'] = $row ? $row['count'] : 0;
    $stmt->close();
    
    // Get mentor sessions count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM mentor_enrollments WHERE user_id = ? AND status = 'completed'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['session_count'] = $row ? $row['count'] : 0;
    $stmt->close();
    
    // Get assessments count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM assessment_results WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['assessment_count'] = $row ? $row['count'] : 0;
    $stmt->close();
    
    // Get community posts count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM community_posts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['community_posts'] = $row ? $row['count'] : 0;
    $stmt->close();
    
    // Calculate streak days (simplified)
    $stats['streak_days'] = calculateStreak($conn, $user_id);
    
    return $stats;
}

function calculateStreak($conn, $user_id) {
    // Simple streak calculation based on mood entries
    $stmt = $conn->prepare("SELECT COUNT(DISTINCT entry_date) as days 
                            FROM mood_entries 
                            WHERE user_id = ? 
                            AND entry_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['days'] ?? 0;
}

function calculateProgress($achievement, $stats) {
    $type = $achievement['requirement_type'] ?? '';
    return isset($stats[$type]) ? $stats[$type] : 0;
}

function markAchievementCompleted($conn, $user_id, $achievement_id, $achievement) {
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
        $target = $achievement['requirement_value'] ?? 0;
        $progress = $achievement['progress_current'] ?? 0;
        $stmt->bind_param("iiii", $user_id, $achievement_id, $progress, $target);
        $stmt->execute();
    }
    $stmt->close();
}
?>