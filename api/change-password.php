<?php
// api/change-password.php
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

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$current_password = $input['current_password'] ?? '';
$new_password = $input['new_password'] ?? '';
$confirm_password = $input['confirm_password'] ?? '';

// Validation
if (empty($current_password)) {
    echo json_encode(['success' => false, 'message' => 'বর্তমান পাসওয়ার্ড দিন']);
    $conn->close();
    exit();
}

if (strlen($new_password) < 4) {
    echo json_encode(['success' => false, 'message' => 'নতুন পাসওয়ার্ড কমপক্ষে ৪ অক্ষরের হতে হবে']);
    $conn->close();
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড মিলছে না']);
    $conn->close();
    exit();
}

// Get current user password
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'ইউজার পাওয়া যায়নি']);
    $conn->close();
    exit();
}

// Verify current password
if (!password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'বর্তমান পাসওয়ার্ড ভুল']);
    $conn->close();
    exit();
}

// Hash new password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// Update password
$update_sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("si", $hashed_password, $user_id);

if ($update_stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => '✅ পাসওয়ার্ড পরিবর্তন হয়েছে'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'পাসওয়ার্ড পরিবর্তন ব্যর্থ হয়েছে'
    ]);
}

$update_stmt->close();
$conn->close();
?>
