<?php
// api/get-mood-history.php
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

// Get parameters
$date = $_GET['date'] ?? null;
$single = isset($_GET['single']) && $_GET['single'] == 'true';
$period = $_GET['period'] ?? 'week';
$limit = $_GET['limit'] ?? 30;

// If single date requested
if ($single && $date) {
    $sql = "SELECT * FROM mood_entries WHERE user_id = ? AND entry_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    echo json_encode(['success' => true, 'data' => $data]);
    $stmt->close();
    $conn->close();
    exit();
}

// Date range based on period
$end_date = date('Y-m-d');
$start_date = $end_date;

switch ($period) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-365 days'));
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-7 days'));
}

$sql = "SELECT * FROM mood_entries 
        WHERE user_id = ? AND entry_date BETWEEN ? AND ? 
        ORDER BY entry_date ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(['success' => true, 'data' => $data]);

$stmt->close();
$conn->close();
?>