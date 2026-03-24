<?php
// api/get-journal-details.php
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
$entry_id = $_GET['id'] ?? 0;

if (!$entry_id) {
    echo json_encode(['success' => false, 'message' => 'Entry ID required']);
    exit();
}

// Database connection
$db = Database::getInstance();
$conn = $db->getConnection();

$sql = "SELECT * FROM journal_entries WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $entry_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $row['mood_badge'] = $row['mood_score'] ? getMoodBadge($row['mood_score']) : null;
    $row['category_info'] = getCategoryInfo($row['category']);
    $row['tags'] = json_decode($row['tags'], true);
    $row['created_at_formatted'] = date('d M Y, h:i A', strtotime($row['created_at']));
    $row['updated_at_formatted'] = date('d M Y, h:i A', strtotime($row['updated_at']));
    
    echo json_encode(['success' => true, 'data' => $row]);
} else {
    echo json_encode(['success' => false, 'message' => 'Entry not found']);
}

$stmt->close();
$conn->close();
?>