<?php
// api/submit-rating.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['enrollment_id']) || !isset($data['rating'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$user_id = $_SESSION['user_id'];
$enrollment_id = (int)$data['enrollment_id'];
$mentor_id = (int)$data['mentor_id'];
$rating = (int)$data['rating'];
$review = $data['review'] ?? '';

// Validate rating
if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'রেটিং ১-৫ এর মধ্যে হতে হবে']);
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
$checkOwnership = $conn->prepare("SELECT id FROM mentor_enrollments WHERE id = ? AND user_id = ? AND status = 'completed'");
$checkOwnership->bind_param('ii', $enrollment_id, $user_id);
$checkOwnership->execute();
if ($checkOwnership->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found or not completed']);
    $checkOwnership->close();
    $conn->close();
    exit();
}
$checkOwnership->close();

// Update enrollment with rating
$query = "UPDATE mentor_enrollments SET rating = ?, feedback = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('isii', $rating, $review, $enrollment_id, $user_id);
$stmt->execute();
$stmt->close();

// Update mentor's average rating
$getMentorRatings = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_sessions FROM mentor_enrollments WHERE mentor_id = ? AND rating IS NOT NULL");
$getMentorRatings->bind_param('i', $mentor_id);
$getMentorRatings->execute();
$result = $getMentorRatings->get_result();
$ratingData = $result->fetch_assoc();
$getMentorRatings->close();

$avgRating = $ratingData['avg_rating'] ?? 0;
$totalSessions = $ratingData['total_sessions'] ?? 0;

// Update mentor table
$updateMentor = $conn->prepare("UPDATE mentors SET rating = ?, total_sessions = ? WHERE id = ?");
$updateMentor->bind_param('dii', $avgRating, $totalSessions, $mentor_id);
$updateMentor->execute();
$updateMentor->close();

$conn->close();

echo json_encode(['success' => true, 'message' => 'আপনার রেটিং সংরক্ষিত হয়েছে']);
?>
