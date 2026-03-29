<?php
// api/settings/update-notifications.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

$email_notifications = $input['email_notifications'] ?? 0;
$push_notifications = $input['push_notifications'] ?? 0;
$session_reminders = $input['session_reminders'] ?? 0;
$weekly_report = $input['weekly_report'] ?? 0;
$marketing_emails = $input['marketing_emails'] ?? 0;

$conn = new mysqli('localhost', 'root', '', 'mentora_db');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Create user_settings table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS user_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE,
    email_notifications BOOLEAN DEFAULT 1,
    push_notifications BOOLEAN DEFAULT 1,
    session_reminders BOOLEAN DEFAULT 1,
    weekly_report BOOLEAN DEFAULT 0,
    marketing_emails BOOLEAN DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$sql = "INSERT INTO user_settings (user_id, email_notifications, push_notifications, session_reminders, weekly_report, marketing_emails)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        email_notifications = VALUES(email_notifications),
        push_notifications = VALUES(push_notifications),
        session_reminders = VALUES(session_reminders),
        weekly_report = VALUES(weekly_report),
        marketing_emails = VALUES(marketing_emails)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $user_id, $email_notifications, $push_notifications, $session_reminders, $weekly_report, $marketing_emails);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'নোটিফিকেশন সেটিংস আপডেট হয়েছে']);
} else {
    echo json_encode(['success' => false, 'message' => 'আপডেট করতে ব্যর্থ হয়েছে']);
}

$conn->close();
?>