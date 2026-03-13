<?php
// auth/logout.php
require_once '../includes/functions.php';

// Log activity if user was logged in
if (isLoggedIn()) {
    logUserActivity($_SESSION['user_id'], 'logout', $_SERVER['REMOTE_ADDR']);
}

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    $db = getDB();
    $db->insert(
        "DELETE FROM user_sessions WHERE session_token = ?",
        [$_COOKIE['remember_token']]
    );
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
$_SESSION = array();
session_destroy();

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Redirect to login page
header('Location: login.php?logged_out=1');
exit();
?>