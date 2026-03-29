<?php
// resources/api/get-resource-details.php
// Get single resource details by ID

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
        'message' => 'Database connection failed: ' . $conn->connect_error,
        'data' => null
    ]);
    exit();
}

$conn->set_charset("utf8mb4");

// Get resource ID from query parameter
$resource_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($resource_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid resource ID',
        'data' => null
    ]);
    $conn->close();
    exit();
}

// Get resource details
$sql = "SELECT 
            id, 
            title, 
            description, 
            category, 
            resource_type, 
            resource_url, 
            file_path, 
            audio_url, 
            thumbnail,
            contact_info, 
            contact_numbers, 
            duration, 
            author, 
            source,
            is_featured,
            view_count,
            created_at
        FROM resources 
        WHERE id = ? AND is_active = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $resource_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Resource not found',
        'data' => null
    ]);
    $stmt->close();
    $conn->close();
    exit();
}

$resource = $result->fetch_assoc();

// Increment view count
$update_sql = "UPDATE resources SET view_count = view_count + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $resource_id);
$update_stmt->execute();
$update_stmt->close();

// Prepare response data
$response_data = [
    'id' => (int)$resource['id'],
    'title' => $resource['title'],
    'description' => $resource['description'],
    'category' => $resource['category'],
    'resource_type' => $resource['resource_type'],
    'resource_url' => $resource['resource_url'],
    'file_path' => $resource['file_path'],
    'audio_url' => $resource['audio_url'],
    'thumbnail' => $resource['thumbnail'],
    'contact_info' => $resource['contact_info'],
    'contact_numbers' => $resource['contact_numbers'],
    'duration' => $resource['duration'],
    'author' => $resource['author'],
    'source' => $resource['source'],
    'is_featured' => (bool)$resource['is_featured'],
    'view_count' => (int)$resource['view_count'],
    'created_at' => $resource['created_at']
];

// Add type-specific helper data
$response_data['type_icon'] = getTypeIcon($resource['resource_type']);
$response_data['type_color'] = getTypeColor($resource['resource_type']);
$response_data['type_label'] = getTypeLabel($resource['resource_type']);

echo json_encode([
    'success' => true,
    'message' => 'Resource found',
    'data' => $response_data
]);

$stmt->close();
$conn->close();

// ==================== HELPER FUNCTIONS ====================

function getTypeIcon($type) {
    $icons = [
        'article' => 'fa-newspaper',
        'video' => 'fa-video',
        'pdf' => 'fa-file-pdf',
        'breathing' => 'fa-lungs',
        'meditation' => 'fa-head-side-medical',
        'helpline' => 'fa-phone-alt'
    ];
    return $icons[$type] ?? 'fa-file-alt';
}

function getTypeColor($type) {
    $colors = [
        'article' => '#2563eb',
        'video' => '#dc2626',
        'pdf' => '#d97706',
        'breathing' => '#059669',
        'meditation' => '#7e22ce',
        'helpline' => '#db2777'
    ];
    return $colors[$type] ?? '#6366f1';
}

function getTypeLabel($type) {
    $labels = [
        'article' => 'আর্টিকেল',
        'video' => 'ভিডিও',
        'pdf' => 'পিডিএফ গাইড',
        'breathing' => 'শ্বাস-প্রশ্বাসের ব্যায়াম',
        'meditation' => 'মেডিটেশন',
        'helpline' => 'হেল্পলাইন'
    ];
    return $labels[$type] ?? 'রিসোর্স';
}
?>