<?php
// api/update-journal.php
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

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$entry_id = $input['id'];
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');
$mood_score = $input['mood_score'] ?? null;
$mood_label = $input['mood_label'] ?? null;
$category = $input['category'] ?? 'general';
$tags = $input['tags'] ?? [];

// Validate
$errors = validateJournalEntry($title, $content, $mood_score, $category);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => $errors[0]]);
    exit();
}

// Check ownership
$db = Database::getInstance();
$conn = $db->getConnection();

$check_sql = "SELECT id FROM journal_entries WHERE id = ? AND user_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $entry_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Entry not found or unauthorized']);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Get mood label if mood score provided
if ($mood_score && !$mood_label) {
    $moodInfo = getMoodBadge($mood_score);
    $mood_label = $moodInfo['label'];
}

$tags_json = json_encode($tags);

// Update
$sql = "UPDATE journal_entries SET 
        title = ?, content = ?, mood_score = ?, mood_label = ?, 
        category = ?, tags = ?, updated_at = NOW() 
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisssii", 
    $title, $content, $mood_score, $mood_label, $category, $tags_json, $entry_id, $user_id
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'জার্নাল আপডেট হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'আপডেট করতে ব্যর্থ হয়েছে']);
}

$stmt->close();
$conn->close();
?>