<?php
// api/achievements/claim-achievement.php
session_start();
header('Content-Type: application/json');

require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$achievement_id = $input['achievement_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if (!$achievement_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid achievement ID']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Check if achievement is completed and not claimed
$sql = "SELECT ua.*, am.points 
        FROM user_achievements ua
        JOIN achievements_master am ON ua.achievement_id = am.id
        WHERE ua.user_id = ? AND ua.achievement_id = ? AND ua.is_completed = 1 AND ua.is_claimed = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $achievement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Achievement cannot be claimed']);
    $conn->close();
    exit();
}

$achievement = $result->fetch_assoc();

// Mark as claimed
$update_sql = "UPDATE user_achievements SET is_claimed = 1, claimed_at = NOW() WHERE user_id = ? AND achievement_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $user_id, $achievement_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'অ্যাচিভমেন্ট ক্লেইম করা হয়েছে!',
        'points' => $achievement['points']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Claim failed']);
}

$conn->close();
?>