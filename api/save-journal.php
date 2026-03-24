<?php
// api/save-journal.php
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

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$user_id = $_SESSION['user_id'];
$title = trim($input['title'] ?? '');
$content = trim($input['content'] ?? '');
$mood_score = $input['mood_score'] ?? null;
$mood_label = $input['mood_label'] ?? null;
$category = $input['category'] ?? 'general';
$tags = $input['tags'] ?? [];
$is_private = $input['is_private'] ?? false;

// Validate
$errors = validateJournalEntry($title, $content, $mood_score, $category);

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => $errors[0], 'errors' => $errors]);
    exit();
}

// Get mood label if mood score provided
if ($mood_score && !$mood_label) {
    $moodInfo = getMoodBadge($mood_score);
    $mood_label = $moodInfo['label'];
}

// Prepare tags JSON
$tags_json = json_encode($tags);

// Database connection
$db = Database::getInstance();
$conn = $db->getConnection();

// Insert
$sql = "INSERT INTO journal_entries 
        (user_id, title, content, mood_score, mood_label, category, tags, is_private, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssi", 
    $user_id, $title, $content, $mood_score, $mood_label, $category, $tags_json, $is_private
);

if ($stmt->execute()) {
    $entry_id = $conn->insert_id;
    echo json_encode([
        'success' => true,
        'message' => 'জার্নাল সংরক্ষণ হয়েছে',
        'entry_id' => $entry_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'সংরক্ষণ করতে ব্যর্থ হয়েছে: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>