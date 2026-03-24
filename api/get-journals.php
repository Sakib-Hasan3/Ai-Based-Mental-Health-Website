<?php
// api/get-journals.php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/journal_helper.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];
$limit = $_GET['limit'] ?? 50;
$offset = $_GET['offset'] ?? 0;
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$mood = $_GET['mood'] ?? '';
$sort = $_GET['sort'] ?? 'recent';

// Database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Build query
$sql = "SELECT id, title, content, mood_score, mood_label, category, tags, created_at, updated_at 
        FROM journal_entries 
        WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($search) {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if ($category) {
    $sql .= " AND category = ?";
    $params[] = $category;
    $types .= "s";
}

if ($mood) {
    $sql .= " AND mood_score = ?";
    $params[] = $mood;
    $types .= "i";
}

// Sorting
if ($sort == 'oldest') {
    $sql .= " ORDER BY created_at ASC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
while ($row = $result->fetch_assoc()) {
    $row['preview'] = getPreview($row['content'], 120);
    $row['mood_badge'] = $row['mood_score'] ? getMoodBadge($row['mood_score']) : null;
    $row['category_info'] = getCategoryInfo($row['category']);
    $row['tags'] = json_decode($row['tags'], true);
    $row['created_at_formatted'] = date('d M Y, h:i A', strtotime($row['created_at']));
    $entries[] = $row;
}

echo json_encode([
    'success' => true,
    'data' => $entries,
    'count' => count($entries)
]);

$stmt->close();
$conn->close();
?>