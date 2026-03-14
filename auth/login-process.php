
<?php
// auth/login-process.php
require_once '../includes/functions.php';

// Set header for JSON response
header('Content-Type: application/json');

// Check if it's POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Security token validation failed']);
    exit();
}

// Get and sanitize inputs
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate inputs
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'ইমেইল ও পাসওয়ার্ড দিন']);
    exit();
}

$db = getDB();

// Check if input is email or phone
$isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);

if ($isEmail) {
    $user = $db->getSingle(
        "SELECT id, full_name, email, password, user_type, is_verified, is_active, login_attempts FROM users WHERE email = ?",
        [$email]
    );
} else {
    // Assume it's phone number
    $user = $db->getSingle(
        "SELECT id, full_name, phone, password, user_type, is_verified, is_active, login_attempts FROM users WHERE phone = ?",
        [$email]
    );
}

// Check if user exists
if (!$user) {
    // Log failed attempt
    error_log("Failed login attempt for email/phone: $email");
    
    echo json_encode([
        'success' => false, 
        'message' => 'ইমেইল/ফোন বা পাসওয়ার্ড ভুল',
        'field' => 'email'
    ]);
    exit();
}

// Check if account is active
if (!$user['is_active']) {
    echo json_encode([
        'success' => false, 
        'message' => 'এই অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।'
    ]);
    exit();
}

// Check login attempts (prevent brute force)
if ($user['login_attempts'] >= 5) {
    echo json_encode([
        'success' => false, 
        'message' => 'অনেকবার ভুল চেষ্টা। ১৫ মিনিট পর আবার চেষ্টা করুন।'
    ]);
    exit();
}

// Verify password
if (!verifyPassword($password, $user['password'])) {
    // Increment login attempts
    $db->insert(
        "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
        [$user['id']]
    );
    
    echo json_encode([
        'success' => false, 
        'message' => 'ইমেইল/ফোন বা পাসওয়ার্ড ভুল',
        'field' => 'password'
    ]);
    exit();
}

// Check if email is verified (optional)
if (!$user['is_verified']) {
    echo json_encode([
        'success' => false, 
        'message' => 'ইমেইল ভেরিফাই করা হয়নি। আপনার ইমেইল চেক করুন।'
    ]);
    exit();
}

// Login successful - reset attempts and update last login
$db->insert(
    "UPDATE users SET login_attempts = 0, last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
    [$_SERVER['REMOTE_ADDR'], $user['id']]
);

// Set session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_type'] = $user['user_type'];
$_SESSION['logged_in_at'] = time();

// Set remember me cookie if requested
if ($remember) {
    setRememberMe($user['id']);
}

// Log the activity
logUserActivity($user['id'], 'login', $_SERVER['REMOTE_ADDR']);

// Determine redirect URL based on user type
$redirect = '../index.php';
if ($user['user_type'] === 'admin') {
    $redirect = '../admin/dashboard.php';
} elseif ($user['user_type'] === 'mentor') {
    $redirect = '../mentor/dashboard.php';
} elseif ($user['user_type'] === 'doctor') {
    $redirect = '../doctor/dashboard.php';
}

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'লগইন সফল হয়েছে! রিডাইরেক্ট হচ্ছে...',
    'redirect' => $redirect,
    'user' => [
        'name' => $user['full_name'],
        'type' => $user['user_type']
    ]
]);
exit();
?>
