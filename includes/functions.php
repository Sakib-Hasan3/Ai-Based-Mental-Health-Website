<?php
// includes/functions.php
session_start();

// Get database instance
function getDB() {
    return Database::getInstance();
}

// Password hash function
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Password verify function
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// CSRF Token generate
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token verify
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Is logged in check
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        
        $class = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        return '<div class="alert ' . $class . '">' . $flash['message'] . '</div>';
    }
    return '';
}

// FIXED: logUserActivity function with error handling
function logUserActivity($user_id, $action, $ip = null) {
    try {
        $db = getDB();
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // First check if user_logs table exists
        $conn = $db->getConnection();
        $result = $conn->query("SHOW TABLES LIKE 'user_logs'");
        
        if ($result->num_rows == 0) {
            // Create the table if it doesn't exist
            $create_table_sql = "CREATE TABLE IF NOT EXISTS user_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                action VARCHAR(50) NOT NULL,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_action (user_id, action)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->query($create_table_sql);
        }
        
        // Now insert the log
        $sql = "INSERT INTO user_logs (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)";
        $db->insert($sql, [$user_id, $action, $ip, $user_agent]);
        
    } catch (Exception $e) {
        // Log error but don't stop execution
        error_log("Failed to log user activity: " . $e->getMessage());
    }
}

// =============== EMAIL VERIFICATION FUNCTIONS ===============

// Generate email verification token
function generateEmailToken($user_id) {
    $token = bin2hex(random_bytes(32));
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    try {
        $db = getDB();
        $conn = $db->getConnection();
        
        // Check if email_verifications table exists
        $result = $conn->query("SHOW TABLES LIKE 'email_verifications'");
        
        if ($result->num_rows == 0) {
            // Create the table if it doesn't exist
            $create_table_sql = "CREATE TABLE IF NOT EXISTS email_verifications (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                verified_at DATETIME NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_token (user_id, token),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $conn->query($create_table_sql);
        }
        
        // Delete old tokens for this user
        $delete_sql = "DELETE FROM email_verifications WHERE user_id = ? AND verified_at IS NULL";
        $result = $conn->prepare($delete_sql);
        $result->bind_param("i", $user_id);
        $result->execute();
        $result->close();
        
        // Insert new token
        $insert_sql = "INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, ?)";
        $result = $conn->prepare($insert_sql);
        $result->bind_param("iss", $user_id, $token, $expires_at);
        $result->execute();
        $result->close();
        
        return $token;
    } catch (Exception $e) {
        error_log("Failed to generate email token: " . $e->getMessage());
        return null;
    }
}

// Send verification email
function sendVerificationEmail($email, $token, $full_name) {
    $verification_url = "http://" . $_SERVER['HTTP_HOST'] . "/auth/verify-email.php?token=" . $token;
    
    $subject = "মেন্টোরা - ইমেইল যাচাই করুন | Mentora - Verify Your Email";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #0d9488 0%, #6366f1 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
            .button { background: #0d9488; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 20px 0; }
            .footer { text-align: center; color: #64748b; font-size: 12px; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>মেন্টোরা - ইমেইল যাচাই</h1>
            </div>
            <div class='content'>
                <p>আপনাকে স্বাগতম, <strong>" . htmlspecialchars($full_name) . "</strong>!</p>
                
                <p>আপনার মেন্টোরা অ্যাকাউন্ট সম্পূর্ণ করতে আপনার ইমেইল যাচাই করুন।</p>
                
                <p>নিচের বাটনে ক্লিক করুন:</p>
                
                <a href='" . htmlspecialchars($verification_url) . "' class='button'>ইমেইল যাচাই করুন</a>
                
                <p>বা এই লিঙ্ক কপি করুন:</p>
                <p style='word-break: break-all; background: white; padding: 10px; border-radius: 5px; color: #0d9488;'>
                    " . htmlspecialchars($verification_url) . "
                </p>
                
                <p style='color: #ef4444; font-size: 12px;'>
                    <strong>নোট:</strong> এই লিঙ্কটি ২৪ ঘন্টার জন্য বৈধ থাকবে।
                </p>
                
                <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 20px 0;'>
                
                <p style='font-size: 12px; color: #64748b;'>
                    আপনি এই অনুরোধ করেননি? তাহলে এই ইমেইল উপেক্ষা করুন।
                </p>
            </div>
            <div class='footer'>
                <p>© 2026 Mentora - বাংলায় মানসিক স্বাস্থ্য প্ল্যাটফর্ম</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@mentora.com\r\n";
    
    // Try to send email
    $email_sent = mail($email, $subject, $message, $headers);
    
    return $email_sent;
}

// Verify email token
function verifyEmailToken($token) {
    try {
        $db = getDB();
        $conn = $db->getConnection();
        
        // Find token and check if not expired
        $sql = "SELECT user_id, expires_at FROM email_verifications 
                WHERE token = ? AND verified_at IS NULL AND expires_at > NOW()";
        
        $result = $conn->prepare($sql);
        $result->bind_param("s", $token);
        $result->execute();
        $query_result = $result->get_result();
        
        if ($query_result->num_rows === 0) {
            $result->close();
            return ['success' => false, 'message' => 'অবৈধ বা পুরানো টোকেন'];
        }
        
        $row = $query_result->fetch_assoc();
        $user_id = $row['user_id'];
        $result->close();
        
        // Mark as verified
        $update_verification = "UPDATE email_verifications SET verified_at = NOW() WHERE token = ?";
        $stmt = $conn->prepare($update_verification);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->close();
        
        // Mark user as verified
        $update_user = "UPDATE users SET is_verified = 1 WHERE id = ?";
        $stmt = $conn->prepare($update_user);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        return ['success' => true, 'message' => 'ইমেইল সফলভাবে যাচাই হয়েছে', 'user_id' => $user_id];
        
    } catch (Exception $e) {
        error_log("Email verification error: " . $e->getMessage());
        return ['success' => false, 'message' => 'যাচাইকরণ ব্যর্থ হয়েছে'];
    }
}
?>