<?php
// api/community/react-post.php
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
$user_id = $_SESSION['user_id'];

if (!$post_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid post']);
    exit();
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Check if already reacted
$check_sql = "SELECT id FROM community_reactions WHERE post_id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $post_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Remove reaction
    $delete_sql = "DELETE FROM community_reactions WHERE post_id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $post_id, $user_id);
    $delete_stmt->execute();
    $reacted = false;
    $delete_stmt->close();
} else {
    // Add reaction
    $insert_sql = "INSERT INTO community_reactions (post_id, user_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $post_id, $user_id);
    $insert_stmt->execute();
    $reacted = true;
    $insert_stmt->close();
}

// Get updated support count
$count_sql = "SELECT COUNT(*) as count FROM community_reactions WHERE post_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $post_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count = $count_result->fetch_assoc()['count'];

// Update post support count
$update_sql = "UPDATE community_posts SET support_count = ? WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ii", $count, $post_id);
$update_stmt->execute();
$update_stmt->close();

echo json_encode(['success' => true, 'reacted' => $reacted, 'support_count' => $count]);

$check_stmt->close();
$count_stmt->close();
$conn->close();
?>