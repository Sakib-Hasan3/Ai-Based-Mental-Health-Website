<?php
// api/save-mood.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;
    
    if ($id) {
        $delete_sql = "DELETE FROM mood_entries WHERE id = ? AND user_id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $id, $user_id);
        
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'মুড এন্ট্রি ডিলিট হয়েছে']);
        } else {
            echo json_encode(['success' => false, 'message' => 'ডিলিট করতে ব্যর্থ হয়েছে']);
        }
        $delete_stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
    $conn->close();
    exit();
}

// Handle POST request (Save)
$input = json_decode(file_get_contents('php://input'), true);

error_log("[SAVE-MOOD] POST Request - Input: " . print_r($input, true));

if (!$input) {
    error_log("[SAVE-MOOD] ERROR: Invalid input");
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    $conn->close();
    exit();
}

$mood_score = $input['mood_score'] ?? 0;
$mood_label = $input['mood_label'] ?? '';
$mood_emoji = $input['mood_emoji'] ?? '';
$notes = $input['notes'] ?? '';
$sleep_hours = $input['sleep_hours'] ?? null;
$exercise = $input['exercise'] ?? 0;
$meditation = $input['meditation'] ?? 0;
$social_contact = $input['social_contact'] ?? 0;
$entry_date = $input['entry_date'] ?? date('Y-m-d');

// Validation
if ($mood_score < 1 || $mood_score > 10) {
    echo json_encode(['success' => false, 'message' => 'সঠিক মুড নির্বাচন করুন']);
    $conn->close();
    exit();
}

// Check if entry exists for this date
$check_sql = "SELECT id FROM mood_entries WHERE user_id = ? AND entry_date = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("is", $user_id, $entry_date);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$existing = $check_result->fetch_assoc();

if ($existing) {
    // Update existing entry
    $update_sql = "UPDATE mood_entries SET 
                    mood_score = ?, mood_label = ?, mood_emoji = ?, 
                    notes = ?, sleep_hours = ?, exercise = ?, 
                    meditation = ?, social_contact = ?, updated_at = NOW()
                    WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isssdiiii", 
        $mood_score, $mood_label, $mood_emoji,
        $notes, $sleep_hours, $exercise,
        $meditation, $social_contact, $existing['id']
    );
    
    if ($update_stmt->execute()) {
        error_log("[SAVE-MOOD] SUCCESS: Mood updated");
        echo json_encode(['success' => true, 'message' => 'মুড আপডেট হয়েছে']);
    } else {
        error_log("[SAVE-MOOD] ERROR: Execute failed - " . $update_stmt->error);
        echo json_encode(['success' => false, 'message' => 'আপডেট করতে ব্যর্থ হয়েছে: ' . $update_stmt->error]);
    }
    $update_stmt->close();
} else {
    // Insert new entry
    $insert_sql = "INSERT INTO mood_entries 
                    (user_id, mood_score, mood_label, mood_emoji, notes, 
                     sleep_hours, exercise, meditation, social_contact, entry_date)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisssdiiis", 
        $user_id, $mood_score, $mood_label, $mood_emoji, $notes,
        $sleep_hours, $exercise, $meditation, $social_contact, $entry_date
    );
    
    if ($insert_stmt->execute()) {
        error_log("[SAVE-MOOD] SUCCESS: Mood inserted");
        echo json_encode(['success' => true, 'message' => 'মুড সংরক্ষণ হয়েছে']);
    } else {
        error_log("[SAVE-MOOD] ERROR: Insert failed - " . $insert_stmt->error);
        echo json_encode(['success' => false, 'message' => 'সংরক্ষণ করতে ব্যর্থ হয়েছে: ' . $insert_stmt->error]);
    }
    $insert_stmt->close();
}

$check_stmt->close();
$conn->close();
?>