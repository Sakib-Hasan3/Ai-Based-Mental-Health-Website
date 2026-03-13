<?php
/**
 * MENTORA - Main Configuration File
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection from outside includes folder
require_once __DIR__ . '/../db.php';

// Application Settings
define('SITE_NAME', 'Mentora');
define('SITE_URL', 'http://localhost/mental-health');
define('SITE_EMAIL', 'info@mentora.com');
define('SITE_PHONE', '+880 1234-567890');

// Paths
define('BASE_PATH', dirname(__DIR__) . '/');
define('INCLUDES_PATH', __DIR__ . '/');
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('ASSETS_PATH', SITE_URL . '/assets/');

// Security Keys (Change these in production)
define('SECRET_KEY', 'your-secret-key-here-change-in-production');
define('ENCRYPTION_KEY', 'your-encryption-key-here-change-in-production');

// Pagination
define('ITEMS_PER_PAGE', 10);
define('ADMIN_ITEMS_PER_PAGE', 20);

// File Upload Settings
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Session Timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting (Turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Include functions file
require_once __DIR__ . '/functions.php';

// Now $conn is available from db.php
global $conn;
?>