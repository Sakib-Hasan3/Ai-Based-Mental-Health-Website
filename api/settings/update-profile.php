<?php
// api/settings/update-profile.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$full_name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$bio = trim($input['bio'] ?? '');

// Validate
if (empty($full_name)) {
    echo json_encode(['success' => false, 'message' => 'নাম দিন']);
    exit();
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'সঠিক ইমেইল দিন']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'mentora_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if email exists for other users
$check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("si", $email, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'এই ইমেইল ইতিমধ্যে নিবন্ধিত আছে']);
    $conn->close();
    exit();
}

// Update profile
$sql = "UPDATE users SET full_name = ?, email = ?, phone = ?, bio = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $full_name, $email, $phone, $bio, $user_id);

if ($stmt->execute()) {
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    echo json_encode(['success' => true, 'message' => 'প্রোফাইল আপডেট হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'আপডেট করতে ব্যর্থ হয়েছে']);
}

$conn->close();
?>