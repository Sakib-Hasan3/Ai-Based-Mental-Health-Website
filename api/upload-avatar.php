<?php
// api/upload-avatar.php
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

// Check if file was uploaded
if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'ছবি আপলোড করতে সমস্যা হয়েছে']);
    $conn->close();
    exit();
}

$file = $_FILES['avatar'];
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
$max_size = 2 * 1024 * 1024; // 2MB

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'শুধুমাত্র ছবি ফাইল আপলোড করুন (JPG, PNG, GIF)']);
    $conn->close();
    exit();
}

// Validate file size
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'ছবির সাইজ ২MB এর কম হতে হবে']);
    $conn->close();
    exit();
}

// Create upload directory if not exists
$upload_dir = __DIR__ . '/../assets/images/avatars/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;
$relative_path = $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    // Update database
    $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $filename, $user_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'প্রোফাইল ছবি আপডেট হয়েছে',
            'avatar_url' => $relative_path
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ডাটাবেজ আপডেট করতে ব্যর্থ হয়েছে']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ছবি আপলোড করতে ব্যর্থ হয়েছে']);
}

$conn->close();
?>