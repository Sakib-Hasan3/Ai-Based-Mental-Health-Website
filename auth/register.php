<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>নিবন্ধন | মেন্টোরা</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
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
            overflow-x: hidden;
        }

        /* Register Container */
        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .register-card {
            max-width: 1200px;
            width: 100%;
            display: flex;
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        /* Left Side - Welcome Section */
        .welcome-side {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 40px;
            position: relative;
            overflow: hidden;
            color: white;
        }

        /* Animated Particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }

        .particle-1 {
            width: 100px;
            height: 100px;
            top: 10%;
            right: -30px;
            animation-delay: 0s;
        }

        .particle-2 {
            width: 150px;
            height: 150px;
            bottom: 10%;
            left: -50px;
            animation-delay: 2s;
        }

        .particle-3 {
            width: 80px;
            height: 80px;
            top: 50%;
            left: 30%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        /* Brand Section */
        .brand-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .brand-icon {
            font-size: 60px;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .brand-name {
            font-size: 36px;
            font-weight: 700;
            margin-top: 10px;
            background: linear-gradient(135deg, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-family: 'Poppins', sans-serif;
        }

        .brand-tagline {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Welcome Message */
        .welcome-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .welcome-text {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.95;
            margin-bottom: 30px;
        }

        /* Benefits List */
        .benefits-list {
            margin: 30px 0;
        }

        .benefit-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .benefit-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.15);
        }

        .benefit-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        }

        .benefit-text {
            flex: 1;
        }

        .benefit-text strong {
            display: block;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .benefit-text small {
            font-size: 11px;
            opacity: 0.8;
        }

        /* Testimonial Card */
        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .testimonial-text {
            font-size: 13px;
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .author-avatar {
            width: 45px;
            height: 45px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .author-info h5 {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }

        .author-info p {
            font-size: 11px;
            opacity: 0.8;
            margin: 0;
        }

        /* Right Side - Form Section */
        .form-side {
            flex: 1;
            padding: 50px 40px;
            background: white;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 14px;
            color: #666;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .progress-step {
            text-align: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 12px;
            color: #999;
            font-weight: 500;
        }

        .progress-step.active .step-number {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: scale(1.1);
        }

        .progress-step.completed .step-number {
            background: #4CAF50;
            color: white;
        }

        .progress-step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .label-icon {
            margin-right: 5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            opacity: 0.6;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Hind Siliguri', sans-serif;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Password Strength Indicator */
        .password-strength {
            margin-top: 10px;
        }

        .strength-bar {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-indicator {
            width: 0%;
            height: 100%;
            transition: width 0.3s ease;
        }

        .strength-text {
            font-size: 11px;
            color: #666;
        }

        /* Checkbox Group */
        .checkbox-group {
            margin: 20px 0;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            font-size: 13px;
            color: #666;
        }

        .checkbox-label input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .link:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-family: 'Hind Siliguri', sans-serif;
        }

        .btn-primary::before {
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

        .btn-primary:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        /* Alert Messages */
        .alert {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            animation: slideDown 0.3s ease;
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

        .alert-danger {
            background: #fee;
            color: #c00;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            color: #0a0;
            border: 1px solid #cfc;
        }

        /* Form Footer */
        .form-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }

        .form-footer p {
            font-size: 13px;
            color: #666;
        }

        /* Loading State */
        .btn-primary.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-primary.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            border: 2px solid white;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: translateY(-50%) rotate(360deg); }
        }

        /* Responsive Design */
        @media (max-width: 968px) {
            .register-card {
                flex-direction: column;
                max-width: 500px;
            }
            
            .welcome-side {
                padding: 40px 30px;
            }
            
            .form-side {
                padding: 40px 30px;
            }
            
            .benefits-list {
                display: none;
            }
            
            .testimonial-card {
                margin-top: 20px;
            }
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 20px 15px;
            }
            
            .form-side {
                padding: 30px 20px;
            }
            
            .progress-steps {
                margin-bottom: 30px;
            }
            
            .step-label {
                font-size: 10px;
            }
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        
        <!-- Left Side - Welcome Section (বাংলা) -->
        <div class="welcome-side">
            <div class="particle particle-1"></div>
            <div class="particle particle-2"></div>
            <div class="particle particle-3"></div>
            
            <div class="welcome-content">
                <div class="brand-section">
                    <div class="brand-icon">🌿</div>
                    <h1 class="brand-name">মেন্টোরা</h1>
                    <p class="brand-tagline">আপনার মানসিক স্বাস্থ্যের সঙ্গী</p>
                </div>
                
                <div class="welcome-message">
                    <h2 class="welcome-title">আমাদের সম্প্রদায়ে যোগ দিন</h2>
                    <p class="welcome-text">উন্নত মানসিক স্বাস্থ্যের দিকে প্রথম পদক্ষেপ নিন। মেন্টরদের সাথে সংযোগ স্থাপন করুন, আপনার মেজাজ ট্র্যাক করুন এবং মূল্যবান সম্পদ অ্যাক্সেস করুন।</p>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">✓</div>
                            <div class="benefit-text">
                                <strong>বিশেষজ্ঞ মেন্টর</strong>
                                <small>পেশাদারদের কাছ থেকে দিকনির্দেশনা নিন</small>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">📊</div>
                            <div class="benefit-text">
                                <strong>মেজাজ ট্র্যাকিং</strong>
                                <small>আপনার মানসিক স্বাস্থ্যের অগ্রগতি পর্যবেক্ষণ করুন</small>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">🎯</div>
                            <div class="benefit-text">
                                <strong>ব্যক্তিগতকৃত সম্পদ</strong>
                                <small>আপনার প্রয়োজনে তৈরি কন্টেন্ট</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-card">
                    <p class="testimonial-text">"মেন্টোরা আমাকে নিজেকে আরও ভালভাবে বুঝতে এবং সহায়ক মানুষদের সাথে সংযোগ স্থাপনে সাহায্য করেছে। অত্যন্ত সুপারিশ করছি!"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">এসআর</div>
                        <div class="author-info">
                            <h5>সারা রহমান</h5>
                            <p>মানসিক স্বাস্থ্য উদ্যোক্তা</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Registration Form (বাংলা) -->
        <div class="form-side">
            <div class="form-header">
                <h2>অ্যাকাউন্ট তৈরি করুন</h2>
                <p>হাজারো মানুষের সাথে যোগ দিন যারা তাদের মানসিক স্বাস্থ্যের উন্নতি করছেন</p>
            </div>
            
            <!-- Progress Steps (বাংলা) -->
            <div class="progress-steps">
                <div class="progress-step active completed">
                    <div class="step-number">✓</div>
                    <div class="step-label">অ্যাকাউন্ট</div>
                </div>
                <div class="progress-step active">
                    <div class="step-number">২</div>
                    <div class="step-label">যাচাইকরণ</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">৩</div>
                    <div class="step-label">প্রোফাইল</div>
                </div>
            </div>
            
            <!-- Form Sections -->
            <div class="form-section active">
                
                <?php
                if(isset($_GET['error'])){
                    echo "<div class='alert alert-danger'><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($_GET['error']) . "</div>";
                }
                if(isset($_GET['success'])){
                    echo "<div class='alert alert-success'><i class='fas fa-check-circle'></i> " . htmlspecialchars($_GET['success']) . "</div>";
                }
                ?>
                
                <form method="POST" action="register-process.php" id="registerForm">
                    
                    <!-- পূর্ণ নাম -->
                    <div class="form-group">
                        <label class="form-label"><span class="label-icon">👤</span>পূর্ণ নাম</label>
                        <div class="input-wrapper">
                            <i class="input-icon">👤</i>
                            <input type="text" name="full_name" id="full_name" class="form-input" placeholder="আপনার পূর্ণ নাম লিখুন" required>
                        </div>
                    </div>
                    
                    <!-- ইমেইল -->
                    <div class="form-group">
                        <label class="form-label"><span class="label-icon">✉️</span>ইমেইল ঠিকানা</label>
                        <div class="input-wrapper">
                            <i class="input-icon">✉️</i>
                            <input type="email" name="email" id="email" class="form-input" placeholder="আপনার ইমেইল লিখুন" required>
                        </div>
                    </div>
                    
                    <!-- ফোন নম্বর -->
                    <div class="form-group">
                        <label class="form-label"><span class="label-icon">📱</span>ফোন নম্বর</label>
                        <div class="input-wrapper">
                            <i class="input-icon">📱</i>
                            <input type="text" name="phone" id="phone" class="form-input" placeholder="আপনার ফোন নম্বর লিখুন">
                        </div>
                    </div>
                    
                    <!-- পাসওয়ার্ড -->
                    <div class="form-group">
                        <label class="form-label"><span class="label-icon">🔐</span>পাসওয়ার্ড</label>
                        <div class="input-wrapper">
                            <i class="input-icon">🔐</i>
                            <input type="password" name="password" id="password" class="form-input" placeholder="একটি শক্তিশালী পাসওয়ার্ড তৈরি করুন" required>
                        </div>
                    </div>
                    
                    <!-- পাসওয়ার্ড নিশ্চিত করুন -->
                    <div class="form-group">
                        <label class="form-label"><span class="label-icon">🔐</span>পাসওয়ার্ড নিশ্চিত করুন</label>
                        <div class="input-wrapper">
                            <i class="input-icon">🔐</i>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-input" placeholder="আপনার পাসওয়ার্ড নিশ্চিত করুন" required>
                        </div>
                    </div>
                    
                    <!-- Password Strength Indicator (বাংলা) -->
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-indicator" id="strengthIndicator"></div>
                        </div>
                        <small class="strength-text" id="strengthText">পাসওয়ার্ড শক্তি: দুর্বল</small>
                    </div>
                    
                    <!-- Terms & Conditions (বাংলা) -->
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="terms" required>
                            <span>আমি <a href="#" class="link">শর্তাবলী ও নীতিমালা</a> মেনে চলতে সম্মতি জানাচ্ছি</span>
                        </label>
                    </div>
                    
                    <!-- Submit Button (বাংলা) -->
                    <button type="submit" class="btn-primary btn-submit" id="submitBtn">অ্যাকাউন্ট তৈরি করুন</button>
                    
                </form>
                
            </div>
            
            <!-- Login Link (বাংলা) -->
            <div class="form-footer">
                <p>ইতিমধ্যে একটি অ্যাকাউন্ট আছে? <a href="login.php" class="link">সাইন ইন করুন</a></p>
            </div>
            
        </div>
        
    </div>
</div>

<!-- JavaScript for Password Strength & Validation (বাংলা টেক্সট সহ) -->
<script>
    // Password Strength Checker
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthIndicator = document.getElementById('strengthIndicator');
    const strengthText = document.getElementById('strengthText');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('registerForm');
    
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        let color, text, width;
        
        switch(strength) {
            case 0:
            case 1:
                color = '#ff4444';
                text = 'অত্যন্ত দুর্বল';
                width = '20%';
                break;
            case 2:
                color = '#ff8800';
                text = 'দুর্বল';
                width = '40%';
                break;
            case 3:
                color = '#ffcc00';
                text = 'মাঝারি';
                width = '60%';
                break;
            case 4:
                color = '#88cc00';
                text = 'শক্তিশালী';
                width = '80%';
                break;
            case 5:
                color = '#44cc00';
                text = 'অত্যন্ত শক্তিশালী';
                width = '100%';
                break;
            default:
                color = '#ff4444';
                text = 'দুর্বল';
                width = '40%';
        }
        
        strengthIndicator.style.width = width;
        strengthIndicator.style.backgroundColor = color;
        strengthText.textContent = `পাসওয়ার্ড শক্তি: ${text}`;
        strengthText.style.color = color;
    }
    
    passwordInput.addEventListener('input', function() {
        checkPasswordStrength(this.value);
    });
    
    // Form Validation (বাংলা এরর মেসেজ)
    form.addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const terms = document.getElementById('terms').checked;
        
        // Password match validation
        if (password !== confirmPassword) {
            e.preventDefault();
            showError('পাসওয়ার্ড দুটি মিলছে না!');
            return false;
        }
        
        // Password length validation
        if (password.length < 6) {
            e.preventDefault();
            showError('পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে!');
            return false;
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            showError('দয়া করে একটি বৈধ ইমেইল ঠিকানা লিখুন!');
            return false;
        }
        
        // Phone validation (Bangladesh)
        if (phone && phone.length > 0) {
            const phoneRegex = /^(01|8801)\d{9}$/;
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                showError('দয়া করে একটি বৈধ বাংলাদেশি ফোন নম্বর লিখুন (যেমন: 017xxxxxxxx)');
                return false;
            }
        }
        
        // Terms validation
        if (!terms) {
            e.preventDefault();
            showError('দয়া করে শর্তাবলী ও নীতিমালায় সম্মতি জানান');
            return false;
        }
        
        // Show loading state
        submitBtn.classList.add('loading');
        submitBtn.textContent = 'অ্যাকাউন্ট তৈরি হচ্ছে...';
    });
    
    function showError(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
        
        const formSection = document.querySelector('.form-section');
        const firstElement = formSection.firstChild;
        formSection.insertBefore(alertDiv, firstElement);
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Real-time confirm password validation
    confirmPasswordInput.addEventListener('input', function() {
        if (this.value !== passwordInput.value) {
            this.style.borderColor = '#ff4444';
        } else {
            this.style.borderColor = '#4CAF50';
        }
    });
    
    passwordInput.addEventListener('input', function() {
        if (confirmPasswordInput.value && this.value !== confirmPasswordInput.value) {
            confirmPasswordInput.style.borderColor = '#ff4444';
        } else if (confirmPasswordInput.value) {
            confirmPasswordInput.style.borderColor = '#4CAF50';
        }
    });
    
    // Add animation on page load
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.register-card').classList.add('fade-in');
    });
</script>

</body>
</html>