<?php
// api/delete-journal.php
session_start();
require_once '../config/database.php';
require_once '../includes/auth_check.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$entry_id = $input['id'];

// Database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Check ownership and delete
$sql = "DELETE FROM journal_entries WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $entry_id, $user_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'জার্নাল ডিলিট হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'ডিলিট করতে ব্যর্থ হয়েছে']);
}

$stmt->close();
$conn->close();
?>