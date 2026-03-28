<?php
// api/community/get-posts.php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'recent';
$category = isset($_GET['category']) ? $_GET['category'] : '';

// Build query
$sql = "SELECT 
            p.*, 
            u.full_name as author_name, 
            u.profile_image as author_avatar,
            (SELECT COUNT(*) FROM community_reactions WHERE post_id = p.id) as support_count,
            (SELECT COUNT(*) FROM community_comments WHERE post_id = p.id) as comment_count,
            (SELECT COUNT(*) FROM community_reactions WHERE post_id = p.id AND user_id = $user_id) as user_reacted
        FROM community_posts p
        JOIN users u ON p.user_id = u.id
        WHERE p.is_approved = 1";

if ($category) {
    $sql .= " AND p.category = '$category'";
}

if ($filter == 'popular') {
    $sql .= " ORDER BY support_count DESC, p.created_at DESC";
} else {
    $sql .= " ORDER BY p.created_at DESC";
}

$sql .= " LIMIT 50";

$result = $conn->query($sql);

$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['user_reacted'] = $row['user_reacted'] > 0;
        $row['is_anonymous'] = (bool)$row['is_anonymous'];
        if ($row['is_anonymous']) {
            $row['author_name'] = 'বেনামী';
            $row['author_avatar'] = 'anonymous.png';
        }
        $posts[] = $row;
    }
}

echo json_encode(['success' => true, 'data' => $posts]);

$conn->close();
?>