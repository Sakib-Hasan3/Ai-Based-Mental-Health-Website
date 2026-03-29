<?php
// api/settings/delete-account.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$password = $input['password'] ?? '';
$user_id = $_SESSION['user_id'];

if (empty($password)) {
    echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড দিন']);
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'mentora_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Verify password
$sql = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'পাসওয়ার্ড ভুল']);
    $conn->close();
    exit();
}

// Delete user (cascade will delete all related data)
$delete_sql = "DELETE FROM users WHERE id = ?";
$delete_stmt = $conn->prepare($delete_sql);
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'আপনার অ্যাকাউন্ট মুছে ফেলা হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'অ্যাকাউন্ট মুছতে ব্যর্থ হয়েছে']);
}

$conn->close();
?>