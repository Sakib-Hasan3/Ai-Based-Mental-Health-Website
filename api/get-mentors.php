<?php
// api/get-mentors.php
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

// Get filter parameters
$specialty = $_GET['specialty'] ?? '';
$search = $_GET['search'] ?? '';
$rating = $_GET['rating'] ?? '';
$available = $_GET['available'] ?? '';

// Build query
$query = "SELECT * FROM mentors WHERE 1=1";
$params = [];
$types = '';

// Add filters
if (!empty($specialty)) {
    $query .= " AND specialty = ?";
    $params[] = $specialty;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR bio LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss';
}

if (!empty($rating)) {
    $query .= " AND rating >= ?";
    $params[] = (float)$rating;
    $types .= 'd';
}

if ($available === '1') {
    $query .= " AND is_available = 1";
}

// Order by rating
$query .= " ORDER BY rating DESC LIMIT 50";

// Execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$mentors = [];
while ($row = $result->fetch_assoc()) {
    $mentors[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'data' => $mentors]);
?>
