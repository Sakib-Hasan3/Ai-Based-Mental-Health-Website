<?php
// api/community/report-post.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = $input['post_id'] ?? 0;
$reason = $input['reason'] ?? 'অনুপযুক্ত কন্টেন্ট';
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if already reported by this user
$check_sql = "SELECT id FROM community_reports WHERE post_id = ? AND reported_by = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $post_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'আপনি ইতিমধ্যে এই পোস্ট রিপোর্ট করেছেন']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Insert report
$sql = "INSERT INTO community_reports (post_id, reported_by, reason) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $post_id, $user_id, $reason);

if ($stmt->execute()) {
    // Mark post as reported
    $update_sql = "UPDATE community_posts SET is_reported = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $post_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'রিপোর্ট পাঠানো হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'রিপোর্ট করতে ব্যর্থ হয়েছে']);
}

$stmt->close();
$conn->close();
?>