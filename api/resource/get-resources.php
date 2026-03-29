<?php
// resources/api/get-resources.php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
        'data' => []
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

// Get parameters
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$sql = "SELECT * FROM resources WHERE is_active = 1";
$params = [];
$types = "";

if ($type && $type !== 'all') {
    $sql .= " AND resource_type = ?";
    $params[] = $type;
    $types .= "s";
}

if ($search) {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "sss";
}

// Order by featured first, then newest
$sql .= " ORDER BY is_featured DESC, created_at DESC";

// Execute query
if (count($params) > 0) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

$resources = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = [
            'id' => (int)$row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category'],
            'resource_type' => $row['resource_type'],
            'resource_url' => $row['resource_url'],
            'file_path' => $row['file_path'],
            'audio_url' => $row['audio_url'],
            'contact_info' => $row['contact_info'],
            'contact_numbers' => $row['contact_numbers'],
            'duration' => $row['duration'],
            'author' => $row['author'],
            'is_featured' => (bool)$row['is_featured']
        ];
    }
}

echo json_encode([
    'success' => true,
    'data' => $resources,
    'count' => count($resources),
    'type' => $type ?: 'all'
]);

$conn->close();
?>