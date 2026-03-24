<?php
// api/enroll-mentor.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['mentor_id']) || !isset($data['session_date']) || !isset($data['session_time'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$user_id = $_SESSION['user_id'];
$mentor_id = (int)$data['mentor_id'];
$session_date = $data['session_date'];
$session_time = $data['session_time'];
$session_type = $data['session_type'] ?? 'video';
$topic = $data['topic'] ?? '';

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

// Check if mentor exists
$checkMentor = $conn->prepare("SELECT id FROM mentors WHERE id = ?");
$checkMentor->bind_param('i', $mentor_id);
$checkMentor->execute();
if ($checkMentor->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Mentor not found']);
    $checkMentor->close();
    $conn->close();
    exit();
}
$checkMentor->close();

// Get mentor hourly rate
$getMentor = $conn->prepare("SELECT hourly_rate FROM mentors WHERE id = ?");
$getMentor->bind_param('i', $mentor_id);
$getMentor->execute();
$mentorResult = $getMentor->get_result();
$mentor = $mentorResult->fetch_assoc();
$amount = $mentor['hourly_rate'] ?? 0;
$getMentor->close();

// Insert enrollment
$query = "INSERT INTO mentor_enrollments (mentor_id, user_id, session_date, session_time, session_type, topic, amount, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param('iissssd', $mentor_id, $user_id, $session_date, $session_time, $session_type, $topic, $amount);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'সেশন বুকিং সফল! মেন্টরের নিশ্চিতকরণের জন্য অপেক্ষা করুন']);
} else {
    echo json_encode(['success' => false, 'message' => 'বুকিং করতে সমস্যা হয়েছে: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
