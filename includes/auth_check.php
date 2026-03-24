<?php
// includes/auth_check.php
// এই ফাইল চেক করে ইউজার লগইন করেছে কিনা

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Not logged in - redirect to login page
    header('Location: ../auth/login.php');
    exit();
}

// Optional: Check if session is expired (30 minutes)
$session_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session expired
    session_unset();
    session_destroy();
    header('Location: ../auth/login.php?timeout=1');
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Get user data from session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_type = $_SESSION['user_type'] ?? 'user';

// Optional: Check if user is active in database
// You can add database query here to verify user status
?>