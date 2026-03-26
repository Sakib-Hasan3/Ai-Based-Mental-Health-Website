<?php
// api/community/comment-post.php
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
$comment = trim($input['comment'] ?? '');
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post']);
    exit();
}

if (empty($comment)) {
    echo json_encode(['success' => false, 'message' => 'মন্তব্য দিন']);
    exit();
}

if (strlen($comment) > 1000) {
    echo json_encode(['success' => false, 'message' => 'মন্তব্য খুব বড় (সর্বোচ্চ ১০০০ অক্ষর)']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Insert comment
$sql = "INSERT INTO community_comments (post_id, user_id, comment) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $post_id, $user_id, $comment);

if ($stmt->execute()) {
    // Update comment count
    $count_sql = "SELECT COUNT(*) as count FROM community_comments WHERE post_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $post_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count = $count_result->fetch_assoc()['count'];
    
    $update_sql = "UPDATE community_posts SET comment_count = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $count, $post_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    echo json_encode(['success' => true, 'message' => 'মন্তব্য যোগ হয়েছে', 'comment_count' => $count]);
} else {
    echo json_encode(['success' => false, 'message' => 'মন্তব্য করতে ব্যর্থ হয়েছে']);
}

$stmt->close();
$conn->close();
?>