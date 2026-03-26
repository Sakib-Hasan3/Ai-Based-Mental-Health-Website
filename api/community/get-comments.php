<?php
// api/community/get-comments.php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT c.*, u.full_name as author_name, u.profile_image as author_avatar,
        p.is_anonymous as post_is_anonymous
        FROM community_comments c
        JOIN users u ON c.user_id = u.id
        JOIN community_posts p ON c.post_id = p.id
        WHERE c.post_id = ? AND c.is_approved = 1
        ORDER BY c.created_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];
while ($row = $result->fetch_assoc()) {
    // If post is anonymous, hide commenter name too
    if ($row['post_is_anonymous']) {
        $row['author_name'] = 'বেনামী';
        $row['author_avatar'] = 'anonymous.png';
    }
    $comments[] = $row;
}

echo json_encode(['success' => true, 'data' => $comments]);

$stmt->close();
$conn->close();
?>