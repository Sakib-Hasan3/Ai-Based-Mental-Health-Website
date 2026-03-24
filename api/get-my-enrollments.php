<?php
// api/get-my-enrollments.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

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

// Get user's enrollments with mentor details
$query = "SELECT 
            me.id,
            me.mentor_id,
            me.session_date,
            me.session_time,
            me.session_type,
            me.topic,
            me.status,
            me.rating,
            me.feedback,
            m.name as mentor_name,
            m.specialty,
            m.profile_image,
            m.hourly_rate
          FROM mentor_enrollments me
          LEFT JOIN mentors m ON me.mentor_id = m.id
          WHERE me.user_id = ?
          ORDER BY me.session_date DESC, me.session_time DESC
          LIMIT 50";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$enrollments = [];
while ($row = $result->fetch_assoc()) {
    $enrollments[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'data' => $enrollments]);
?>
