<?php
// api/get-mentor-details.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Check if mentor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Mentor ID is required']);
    exit();
}

$mentor_id = (int)$_GET['id'];

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

// Get mentor details
$query = "SELECT id, name, specialty, hourly_rate, experience_years, qualification, bio, rating, total_sessions, profile_image, mentor_tier FROM mentors WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $mentor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Mentor not found']);
    $stmt->close();
    $conn->close();
    exit();
}

$mentor = $result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'data' => $mentor]);
?>
