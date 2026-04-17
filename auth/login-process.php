
<?php

session_start();
require_once("../db.php");

// Get the correct base path (handles folder with spaces)
$base_path = '/mental%20health/';

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    header("Location: login.php?error=All fields required");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ? OR phone = ? LIMIT 1");
$stmt->bind_param("ss", $email, $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $user = $result->fetch_assoc();

    if (!password_verify($password, $user['password'])) {
        header("Location: login.php?error=Invalid credentials");
        exit();
    }

    if ($user['is_active'] == 0) {
        header("Location: login.php?error=Account disabled");
        exit();
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_type'] = $user['user_type'];

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    $update = $conn->prepare("UPDATE users SET last_login_at = NOW(), last_login_ip = ?, login_attempts = 0 WHERE id = ?");
    $update->bind_param("si", $ip, $user['id']);
    $update->execute();

    header("Location: " . $base_path . "dashboard/index.php");
    exit();
} else {
    header("Location: login.php?error=Invalid credentials");
    exit();
}
