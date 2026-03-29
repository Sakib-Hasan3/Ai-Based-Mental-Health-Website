<?php
// api/settings/update-privacy.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$profile_visibility = $input['profile_visibility'] ?? 'public';
$data_sharing = $input['data_sharing'] ?? 'anonymized';
$show_activity_status = $input['show_activity_status'] ?? 1;
$show_last_seen = $input['show_last_seen'] ?? 1;

$conn = new mysqli('localhost', 'root', '', 'mentora_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Create user_privacy table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user_privacy (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    profile_visibility ENUM('public', 'friends', 'private') DEFAULT 'public',
    data_sharing ENUM('full', 'anonymized', 'none') DEFAULT 'anonymized',
    show_activity_status BOOLEAN DEFAULT 1,
    show_last_seen BOOLEAN DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$sql = "INSERT INTO user_privacy (user_id, profile_visibility, data_sharing, show_activity_status, show_last_seen)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        profile_visibility = VALUES(profile_visibility),
        data_sharing = VALUES(data_sharing),
        show_activity_status = VALUES(show_activity_status),
        show_last_seen = VALUES(show_last_seen)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issii", $user_id, $profile_visibility, $data_sharing, $show_activity_status, $show_last_seen);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'গোপনীয়তা সেটিংস আপডেট হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'আপডেট করতে ব্যর্থ হয়েছে']);
}

$conn->close();
?>