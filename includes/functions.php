<?php
/**
 * MENTORA - Core Functions File
 */

// Note: Database functions (query, fetchOne, etc.) are already in db.php
// Do NOT redefine them here to avoid conflicts

// Authentication functions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}

if (!function_exists('isMentor')) {
    function isMentor() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'mentor';
    }
}

if (!function_exists('isDoctor')) {
    function isDoctor() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'doctor';
    }
}

if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit();
    }
}

// Sanitization functions
if (!function_exists('sanitize')) {
    function sanitize($input) {
        global $conn;
        return htmlspecialchars(strip_tags(trim(mysqli_real_escape_string($conn, $input))));
    }
}

if (!function_exists('sanitizeEmail')) {
    function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
}

// Validation functions
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

if (!function_exists('validatePhone')) {
    function validatePhone($phone) {
        return preg_match('/^01[3-9]\d{8}$/', $phone);
    }
}

if (!function_exists('validatePassword')) {
    function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}

// Notification functions
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($type, $message) {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }
}

if (!function_exists('getFlashMessage')) {
    function getFlashMessage() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('displayFlashMessage')) {
    function displayFlashMessage() {
        $flash = getFlashMessage();
        if ($flash) {
            $alertClass = $flash['type'] === 'success' ? 'alert-success' : 
                         ($flash['type'] === 'error' ? 'alert-danger' : 'alert-info');
            echo "<div class='alert $alertClass alert-dismissible fade show' role='alert'>
                    {$flash['message']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                  </div>";
        }
    }
}

// Date/Time functions
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd M, Y') {
        return date($format, strtotime($date));
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return formatDate($datetime);
        }
    }
}

// File upload function
if (!function_exists('uploadFile')) {
    function uploadFile($file, $targetDir, $allowedTypes = null) {
        if ($allowedTypes === null) {
            $allowedTypes = ALLOWED_EXTENSIONS;
        }
        
        $targetDir = UPLOAD_PATH . $targetDir . '/';
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($file['name']);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['success' => false, 'message' => 'File too large'];
        }
        
        // Allow certain file formats
        if (!in_array($fileType, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ['success' => true, 'filename' => $fileName];
        }
        
        return ['success' => false, 'message' => 'Upload failed'];
    }
}

// Generate random string
if (!function_exists('generateRandomString')) {
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}

// Get user IP
if (!function_exists('getUserIP')) {
    function getUserIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }
}

// Log activity (uses insert() from db.php)
if (!function_exists('logActivity')) {
    function logActivity($userId, $action, $details = null) {
        $ip = getUserIP();
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s')
        ];
        return insert('activity_logs', $data); // This uses insert() from db.php
    }
}

// Get user by ID
if (!function_exists('getUserById')) {
    function getUserById($id) {
        return fetchOne("SELECT * FROM users WHERE id = " . intval($id)); // Uses fetchOne() from db.php
    }
}

// Debug function
if (!function_exists('debug')) {
    function debug($data) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
        exit();
    }
}

// Check if table exists
if (!function_exists('tableExists')) {
    function tableExists($tableName) {
        global $conn;
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$tableName'");
        return mysqli_num_rows($result) > 0;
    }
}

// Get settings value
if (!function_exists('getSetting')) {
    function getSetting($key, $default = '') {
        $result = fetchOne("SELECT setting_value FROM settings WHERE setting_key = '$key'");
        return $result ? $result['setting_value'] : $default;
    }
}
?>