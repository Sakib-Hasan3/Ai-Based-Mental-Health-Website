<?php
// auth/login.php - Secure Login System
require_once '../config/database.php';
require_once '../includes/functions.php';

// Get the correct base path (handles folder with spaces)
$base_path = '/mental%20health/';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ' . $base_path . 'dashboard/index.php');
    exit();
}

$error = '';
$email = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'নিরাপত্তা টোকেন যাচাইকরণ ব্যর্থ। পুনরায় চেষ্টা করুন।';
    } else {
        // Get and sanitize inputs
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $error = 'ইমেইল/ফোন এবং পাসওয়ার্ড উভয়ই আবশ্যক।';
        } else {
            try {
                $db = getDB();
                
                // Check if input is email or phone
                $isEmail = filter_var($email, FILTER_VALIDATE_EMAIL);
                
                if ($isEmail) {
                    $user = $db->getSingle(
                        "SELECT id, full_name, email, phone, password, user_type, is_verified, is_active, login_attempts FROM users WHERE email = ?",
                        [$email]
                    );
                } else {
                    // Assume it's phone number
                    $user = $db->getSingle(
                        "SELECT id, full_name, email, phone, password, user_type, is_verified, is_active, login_attempts FROM users WHERE phone = ?",
                        [$email]
                    );
                }
                
                // Check if user exists
                if (!$user) {
                    $error = 'ইমেইল/ফোন বা পাসওয়ার্ড ভুল।';
                } 
                // Check if account is active
                else if (!$user['is_active']) {
                    $error = 'এই অ্যাকাউন্টটি নিষ্ক্রিয় করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।';
                } 
                // Check login attempts (prevent brute force)
                else if ($user['login_attempts'] >= 5) {
                    $error = 'অনেকবার ভুল চেষ্টা। ১৫ মিনিট পর আবার চেষ্টা করুন।';
                } 
                // Verify password
                else if (!verifyPassword($password, $user['password'])) {
                    // Increment login attempts
                    $db->insert(
                        "UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?",
                        [$user['id']]
                    );
                    $error = 'ইমেইল/ফোন বা পাসওয়ার্ড ভুল।';
                } 
                // Check if email is verified
                else if (!$user['is_verified']) {
                    $error = 'ইমেইল ভেরিফাই করা হয়নি। আপনার ইমেইলে যাচাইকরণ লিঙ্ক পাঠানো হয়েছে।';
                } 
                else {
                    // Login successful - reset attempts and update last login
                    $db->insert(
                        "UPDATE users SET login_attempts = 0, last_login_at = NOW(), last_login_ip = ? WHERE id = ?",
                        [$_SERVER['REMOTE_ADDR'], $user['id']]
                    );
                    
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['is_verified'] = $user['is_verified'];
                    $_SESSION['logged_in_at'] = time();
                    
                    // Set remember me cookie if requested
                    if ($remember) {
                        setRememberMe($user['id']);
                    }
                    
                    // Log the activity
                    logUserActivity($user['id'], 'login', $_SERVER['REMOTE_ADDR']);
                    
                    // Redirect to dashboard
                    header('Location: ' . $base_path . 'dashboard/index.php');
                    exit();
                }
            } catch (Exception $e) {
                error_log("Login Error: " . $e->getMessage());
                $error = 'একটি ত্রুটি ঘটেছে। পুনরায় চেষ্টা করুন।';
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentora - লগইন | বাংলায় মানসিক স্বাস্থ্য</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Bengali & English -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Hind Siliguri', 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        /* Animated Background */
        .bg-shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 20s infinite;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 400px;
            height: 400px;
            bottom: -150px;
            right: -150px;
            animation-delay: -5s;
        }
        
        .shape-3 {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -30px) scale(1.1); }
            66% { transform: translate(-30px, 20px) scale(0.9); }
        }
        
        /* Main Container */
        .login-container {
            max-width: 1100px;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            animation: slideUp 0.8s ease;
            position: relative;
            z-index: 10;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Left Side - Branding */
        .brand-side {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
            min-height: 600px;
        }
        
        .brand-side::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .brand-icon {
            font-size: 70px;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .brand-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
            line-height: 1.2;
        }
        
        .brand-subtitle {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
            position: relative;
            z-index: 2;
            font-weight: 300;
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
            margin-top: 30px;
            position: relative;
            z-index: 2;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 20px;
            border-radius: 15px;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        
        .feature-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.25);
        }
        
        .feature-item i {
            font-size: 24px;
            width: 40px;
            text-align: center;
        }
        
        .feature-text h5 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .feature-text p {
            font-size: 13px;
            opacity: 0.8;
            margin: 0;
        }
        
        /* Right Side - Login Form */
        .form-side {
            flex: 1;
            padding: 60px 50px;
            background: white;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .form-header h2 {
            color: #333;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .form-header p {
            color: #666;
            font-size: 15px;
        }
        
        /* Error/Success Messages */
        .alert {
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.4s ease;
            border: none;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group label i {
            color: #667eea;
            margin-right: 8px;
        }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            color: #9ca3af;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 20px 14px 45px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: #f9fafb;
            font-family: 'Hind Siliguri', 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        
        .form-control:focus + .input-icon {
            color: #667eea;
        }
        
        /* Password Toggle */
        .password-wrapper {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            cursor: pointer;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .toggle-password:hover {
            color: #667eea;
        }
        
        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0 30px;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .remember-me input {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            cursor: pointer;
        }
        
        .remember-me span {
            color: #6b7280;
            font-size: 14px;
        }
        
        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
        }
        
        /* Login Button */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-login:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Register Link */
        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        .register-link span {
            color: #6b7280;
            font-size: 14px;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            margin-left: 5px;
            transition: color 0.3s ease;
        }
        
        .register-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        /* Demo Credentials */
        .demo-box {
            background: linear-gradient(135deg, #f9fafb, #f3f4f6);
            border: 2px dashed #667eea;
            border-radius: 16px;
            padding: 20px;
            margin-top: 25px;
            text-align: center;
            animation: glow 2s infinite;
        }
        
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.3); }
            50% { box-shadow: 0 0 20px 5px rgba(102, 126, 234, 0.3); }
        }
        
        .demo-box h5 {
            color: #374151;
            font-size: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .demo-box h5 i {
            color: #667eea;
        }
        
        .demo-credentials {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        
        .demo-item {
            text-align: center;
        }
        
        .demo-item .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }
        
        .demo-item .value {
            font-size: 16px;
            font-weight: 600;
            color: #667eea;
            background: white;
            padding: 5px 15px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .brand-side {
                min-height: auto;
                padding: 40px 30px;
                text-align: center;
            }
            
            .feature-item {
                text-align: left;
            }
            
            .form-side {
                padding: 40px 30px;
                min-height: auto;
            }
            
            .brand-title {
                font-size: 36px;
            }
        }
        
        @media (max-width: 576px) {
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .demo-credentials {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="brand-side">
            <div class="brand-icon">
                <i class="fas fa-brain"></i>
            </div>
            <h1 class="brand-title">মেন্টোরা</h1>
            <p class="brand-subtitle">Mentora · বাংলায় মানসিক স্বাস্থ্য</p>
            
            <ul class="feature-list">
                <li class="feature-item">
                    <i class="fas fa-robot"></i>
                    <div class="feature-text">
                        <h5>এআই চ্যাটবট "মনের বন্ধু"</h5>
                        <p>২৪/৭ কথা বলুন, বুঝতে পারবে আপনার মনের কথা</p>
                    </div>
                </li>
                <li class="feature-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <div class="feature-text">
                        <h5>২০০+ অভিজ্ঞ মেন্টর</h5>
                        <p>ক্যারিয়ার, লাইফ স্কিল ও মানসিক স্বাস্থ্যে</p>
                    </div>
                </li>
                <li class="feature-item">
                    <i class="fas fa-heart"></i>
                    <div class="feature-text">
                        <h5>মানসিক স্বাস্থ্য কোর্স</h5>
                        <p>বাংলায় তৈরি বিশেষায়িত কোর্স</p>
                    </div>
                </li>
                <li class="feature-item">
                    <i class="fas fa-users"></i>
                    <div class="feature-text">
                        <h5>কমিউনিটি সাপোর্ট</h5>
                        <p>শেয়ার করুন আপনার অনুভূতি</p>
                    </div>
                </li>
            </ul>
            
            <div style="margin-top: 30px; font-style: italic; opacity: 0.9;">
                <i class="fas fa-quote-left"></i>
                আপনার মানসিক স্বাস্থ্যের যাত্রায় আমরা পাশে আছি
            </div>
        </div>
        
        <!-- Right Side - Login Form -->
        <div class="form-side">
            <div class="form-header">
                <h2>স্বাগতম!</h2>
                <p>আপনার অ্যাকাউন্টে লগইন করুন</p>
            </div>
            
            <!-- Error Message Display -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-envelope"></i>
                        ইমেইল / ফোন নম্বর
                    </label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="email" 
                               id="email"
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="your@email.com বা 017xxxxxxxx"
                               required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        পাসওয়ার্ড
                    </label>
                    <div class="password-wrapper">
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-key"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   name="password" 
                                   id="password"
                                   placeholder="********"
                                   required>
                            <span class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye-slash"></i>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>মনে রাখুন</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-link">
                        পাসওয়ার্ড ভুলে গেছেন?
                    </a>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    লগইন করুন
                </button>
            </form>
            
            <div class="register-link">
                <span>এখনও অ্যাকাউন্ট নেই?</span>
                <a href="register.php">রেজিস্টার করুন →</a>
            </div>
            
            <!-- Test Setup Link -->
            <div class="demo-box">
                <h5>
                    <i class="fas fa-flask"></i>
                    টেস্ট অ্যাকাউন্ট
                </h5>
                <p style="margin: 10px 0; font-size: 14px; color: #6b7280;">টেস্ট করতে চাইলে এখানে ক্লিক করুন:</p>
                <a href="create-test-user.php" style="display: inline-block; background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 10px;">টেস্ট ইউজার তৈরি করুন</a>
                
                <!-- Force Logout Link -->
                <p style="margin-top: 15px; font-size: 12px; color: #9ca3af;">
                    অথবা 
                    <a href="logout.php" style="color: #667eea; text-decoration: underline;">এখানে ক্লিক করুন</a>
                    সেশন ক্লিয়ার করতে
                </p>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            }
        }
        
        // Show loading state on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> লগইন হচ্ছে...';
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
        
        // Add ripple effect to button
        document.querySelector('.btn-login').addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.5)';
            ripple.style.width = '100px';
            ripple.style.height = '100px';
            ripple.style.left = e.clientX - e.target.offsetLeft - 50 + 'px';
            ripple.style.top = e.clientY - e.target.offsetTop - 50 + 'px';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.pointerEvents = 'none';
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        });
        
        // Add keypress event for Enter key
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('loginForm').submit();
            }
        });
    </script>
    
    <style>
        /* Ripple animation */
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    </style>
</body>
</html>