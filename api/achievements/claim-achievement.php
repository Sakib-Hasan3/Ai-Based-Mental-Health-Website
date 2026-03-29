<?php
// api/achievements/claim-achievement.php - SIMPLIFIED VERSION
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$achievement_id = isset($input['achievement_id']) ? (int)$input['achievement_id'] : 0;
$user_id = $_SESSION['user_id'];

if (!$achievement_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid achievement ID']);
    exit();
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if achievement is completed and not claimed
$sql = "SELECT ua.*, am.points 
        FROM user_achievements ua
        JOIN achievements_master am ON ua.achievement_id = am.id
        WHERE ua.user_id = $user_id AND ua.achievement_id = $achievement_id 
        AND ua.is_completed = 1 AND ua.is_claimed = 0";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Achievement cannot be claimed']);
    $conn->close();
    exit();
}

$achievement = $result->fetch_assoc();

// Mark as claimed
$update_sql = "UPDATE user_achievements SET is_claimed = 1, claimed_at = NOW() 
               WHERE user_id = $user_id AND achievement_id = $achievement_id";

if ($conn->query($update_sql)) {
    echo json_encode([
        'success' => true,
        'message' => 'অ্যাচিভমেন্ট ক্লেইম করা হয়েছে!',
        'points' => $achievement['points']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Claim failed: ' . $conn->error]);
}

$conn->close();
?>