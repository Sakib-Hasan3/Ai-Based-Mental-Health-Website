<?php
// api/update-enrollment-status.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$enrollment_id = (int)$data['id'];
$status = $data['status'];
$user_id = $_SESSION['user_id'];

// Only allow valid status values
$validStatuses = ['pending', 'confirmed', 'completed', 'cancelled', 'rejected'];
if (!in_array($status, $validStatuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
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

// Verify ownership
$checkOwnership = $conn->prepare("SELECT id FROM mentor_enrollments WHERE id = ? AND user_id = ?");
$checkOwnership->bind_param('ii', $enrollment_id, $user_id);
$checkOwnership->execute();
if ($checkOwnership->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found or unauthorized']);
    $checkOwnership->close();
    $conn->close();
    exit();
}
$checkOwnership->close();

// Update status
$query = "UPDATE mentor_enrollments SET status = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('sii', $status, $enrollment_id, $user_id);

if ($stmt->execute()) {
    $statusLabels = [
        'cancelled' => 'বাতিল করা হয়েছে',
        'confirmed' => 'নিশ্চিত করা হয়েছে',
        'completed' => 'সম্পন্ন হয়েছে'
    ];
    $message = 'সেশন ' . ($statusLabels[$status] ?? $status);
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'আপডেট করতে সমস্যা হয়েছে']);
}

$stmt->close();
$conn->close();
?>
