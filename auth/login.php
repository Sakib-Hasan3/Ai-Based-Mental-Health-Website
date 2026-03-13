<?php
// auth/login.php
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ../index.php');
    exit();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>লগইন - মেন্টোরা | বাংলায় মানসিক স্বাস্থ্য প্ল্যাটফর্ম</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Auth CSS -->
    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>
    <!-- Animated Background -->
    <div class="auth-bg">
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
        <div class="bg-shape shape-3"></div>
    </div>

    <div class="auth-container">
        <div class="auth-card">
            <div class="row g-0">
                <!-- Left Panel - Branding -->
                <div class="col-lg-6">
                    <div class="auth-brand">
                        <div class="brand-logo">
                            <h2>
                                <i class="fas fa-brain"></i> মেন্টোরা
                                <span>Mentora</span>
                            </h2>
                        </div>
                        
                        <div class="brand-quote">
                            <i class="fas fa-quote-left"></i>
                            আপনার মানসিক স্বাস্থ্যের যাত্রা শুরু হোক আজ
                        </div>
                        
                        <ul class="brand-features">

                        </ul>
                        
                        <div class="mt-4">
                            <img src="../assets/images/auth-illustration.png" alt="Mental Health" class="img-fluid" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                
                <!-- Right Panel - Login Form -->
                <div class="col-lg-6">
                    <div class="auth-form">
                        <div class="auth-header">
                            <h3>স্বাগতম!</h3>
                            <p>আপনার অ্যাকাউন্টে লগইন করুন</p>
                        </div>
                        
                        <?php echo displayFlashMessage(); ?>
                        
                        <form id="loginForm" method="POST" action="login-process.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i>
                                    ইমেইল / ফোন নম্বর
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="email" 
                                           name="email" 
                                           placeholder="your@email.com বা ০১৭xxxxxxxx"
                                           autocomplete="off"
                                           required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="password">
                                    <i class="fas fa-lock"></i>
                                    পাসওয়ার্ড
                                </label>
                                <div class="input-group">
                                    <span class="input-icon">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="********"
                                           required>
                                    <span class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="form-options">
                                <label class="remember-me">
                                    <input type="checkbox" name="remember" id="remember">
                                    <span>মনে রাখুন</span>
                                </label>
                                <a href="forgot-password.php" class="forgot-link">
                                    <i class="fas fa-question-circle"></i>
                                    পাসওয়ার্ড ভুলে গেছেন?
                                </a>
                            </div>
                            
                            <button type="submit" class="btn-login" id="loginBtn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                লগইন করুন
                            </button>
                        </form>
                        
                        <div class="social-login">
                            <p>অথবা লগইন করুন</p>
                            <div class="social-icons">
                                <a href="#" class="social-icon google">
                                    <i class="fab fa-google"></i>
                                </a>
                                <a href="#" class="social-icon facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-icon github">
                                    <i class="fab fa-github"></i>
                                </a>
                            </div>
                        </div>
                        
                        <div class="register-link">
                            <span>এখনও অ্যাকাউন্ট নেই? </span>
                            <a href="register.php">
                                রেজিস্টার করুন <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <!-- Test Credentials (Demo Only) -->
                        <div class="alert alert-info mt-3" style="background: #e0f2fe; border: none; border-radius: 10px;">
                            <small>
                                <i class="fas fa-info-circle"></i>
                                ডেমো একাউন্ট: user@mentora.com / 123456
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/auth.js"></script>
</body>
</html>