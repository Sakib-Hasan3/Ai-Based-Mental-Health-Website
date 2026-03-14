<?php
// auth/login.php
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - মেন্টোরা | বাংলায় মানসিক স্বাস্থ্য প্ল্যাটফর্ম</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Login CSS -->
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Left Side - Illustration (exactly like your reference) -->
            <div class="illustration-side">
                <!-- Floating Shapes -->
                <div class="floating-shape shape-1"></div>
                <div class="floating-shape shape-2"></div>
                <div class="floating-shape shape-3"></div>
                
                <div class="illustration-content">
                    <!-- Logo Area -->
                    <div class="logo-area">
                        <div class="logo-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="brand-name">মেন্টোরা</div>
                        <div class="brand-tagline">Mentora · বাংলায় মানসিক স্বাস্থ্য</div>
                    </div>
                    
                    
                    
                    <!-- Feature List - exactly as in your reference -->
                    <div class="feature-list">
                        <div class="feature-item">
                            <i class="fas fa-robot"></i>
                            <span>এআই চ্যাটবট "মনের বন্ধু"</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>২০০+ অভিজ্ঞ মেন্টর</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-heart"></i>
                            <span>মানসিক স্বাস্থ্য কোর্স</span>
                        </div>
                    </div>
                    
                    <!-- Quote at bottom -->
                    <div style="margin-top: 30px; color: rgba(255,255,255,0.8); font-size: 14px;">
                        <i class="fas fa-quote-left"></i>
                        আপনার জীবনে ইতিবাচক পরিবর্তন আনুন
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Login Form -->
            <div class="form-side">
                <div class="form-header">
                    <h1>লগইন করুন</h1>
                    <p>আপনার অ্যাকাউন্টে প্রবেশ করুন</p>
                </div>
                
                <!-- Flash Messages -->
                <?php echo displayFlashMessage(); ?>
                
                <!-- Login Form -->
                <form id="loginForm" method="POST" action="login-process.php">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label">ইমেইল</label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="far fa-envelope"></i>
                            </span>
                            <input type="text" 
                                   class="form-input" 
                                   id="email" 
                                   name="email" 
                                   placeholder="আপনার ইমেইল লিখুন"
                                   autocomplete="off">
                        </div>
                    </div>
                    
                    <!-- Password Field -->
                    <div class="form-group">
                        <label class="form-label">পাসওয়ার্ড</label>
                        <div class="password-wrapper">
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-input" 
                                       id="password" 
                                       name="password" 
                                       placeholder="********">
                                <span class="toggle-password">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Form Options -->
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            <span>মনে রাখুন</span>
                        </label>
                        <a href="forgot-password.php" class="forgot-link">
                            পাসওয়ার্ড ভুলে গেছেন?
                        </a>
                    </div>
                    
                    <!-- Login Button -->
                    <button type="submit" class="login-btn" id="loginBtn">
                        <i class="fas fa-sign-in-alt"></i>
                        লগইন করুন
                    </button>
                </form>
                
                <!-- Register Link -->
                <div class="register-section">
                    <div class="register-text">এখনও অ্যাকাউন্ট নেই?</div>
                    <a href="register.php" class="register-link">
                        রেজিস্টার করুন 
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
               
                
               
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/login.js"></script>
</body>
</html>