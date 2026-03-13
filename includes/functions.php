<?php
// includes/functions.php

session_start();
require_once __DIR__ . '/../config/database.php';

// Get database instance
function getDB() {
    return Database::getInstance();
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not authenticated
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /mentora/auth/login.php');
        exit();
    }
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $user = $db->getSingle(
        "SELECT id, full_name, email, phone, user_type, profile_image, bio, is_verified FROM users WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    return $user;
}

// Hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get flash message
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Display flash message
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = $flash['type'] === 'success' ? 'alert-success' : 'alert-danger';
        return '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">
                    ' . htmlspecialchars($flash['message']) . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>';
    }
    return '';
}

// Validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Validate phone (Bangladeshi format)
function validatePhone($phone) {
    return preg_match('/^(?:\+88|01)?\d{11}$/', $phone);
}

// Log user activity
function logUserActivity($user_id, $action, $ip = null) {
    $db = getDB();
    $ip = $ip ?? $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $db->insert(
        "INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)",
        [$user_id, $action, $ip, $user_agent]
    );
}

// Remember me functionality
function setRememberMe($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $db = getDB();
    $db->insert(
        "INSERT INTO user_sessions (user_id, session_token, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)",
        [$user_id, $token, $expires, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
    );
    
    setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
}

// Check remember me
function checkRememberMe() {
    if (isset($_COOKIE['remember_token'])) {
        $db = getDB();
        $session = $db->getSingle(
            "SELECT user_id FROM user_sessions WHERE session_token = ? AND expires_at > NOW()",
            [$_COOKIE['remember_token']]
        );
        
        if ($session) {
            $_SESSION['user_id'] = $session['user_id'];
            return true;
        }
    }
    return false;
}
?>