<?php
// api/community/create-post.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['content'])) {
    echo json_encode(['success' => false, 'message' => 'পোস্ট কন্টেন্ট দিন']);
    exit();
}

$user_id = $_SESSION['user_id'];
$content = trim($input['content']);
$category = $input['category'] ?? 'general';
$is_anonymous = $input['is_anonymous'] ?? 0;

if (strlen($content) < 5) {
    echo json_encode(['success' => false, 'message' => 'কমপক্ষে ৫ অক্ষর লিখুন']);
    exit();
}

if (strlen($content) > 5000) {
    echo json_encode(['success' => false, 'message' => 'পোস্ট খুব বড় (সর্বোচ্চ ৫০০০ অক্ষর)']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "INSERT INTO community_posts (user_id, content, category, is_anonymous) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issi", $user_id, $content, $category, $is_anonymous);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'পোস্ট করা হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'পোস্ট করতে ব্যর্থ হয়েছে: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>