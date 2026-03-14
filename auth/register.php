<?php
// auth/register.php
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
    <title>রেজিস্টার - মেন্টোরা | বাংলায় মানসিক স্বাস্থ্য প্ল্যাটফর্ম</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/register.css">
    
    <style>
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-card {
            background: white;
            padding: 30px 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
        }
        
        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #6366f1;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        
        .loading-text {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }
        
        .success-message {
            color: #10b981;
            font-size: 18px;
            font-weight: 600;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-card">
            <div class="loading-spinner"></div>
            <div class="loading-text" id="loadingText">রেজিস্ট্রেশন হচ্ছে...</div>
        </div>
    </div>

    <div class="register-container">
        <div class="register-card">
            <!-- Left Side - Welcome Section -->
            <div class="welcome-side">
                <!-- Floating Particles -->
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                
                <div class="welcome-content">
                    <!-- Brand Section -->
                    <div class="brand-section">
                        <div class="brand-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <div class="brand-name">মেন্টোরা</div>
                        <div class="brand-tagline">Mentora · বাংলায় মানসিক স্বাস্থ্য</div>
                    </div>
                    
                    <!-- Welcome Message -->
                    <div class="welcome-message">
                        <h2 class="welcome-title">
                            আপনার যাত্রা শুরু হোক আজ
                        </h2>
                        <p class="welcome-text">
                            মেন্টোরাতে যোগ দিন এবং পান ২৪/৭ মানসিক স্বাস্থ্য সেবা, 
                            অভিজ্ঞ মেন্টর ও এআই চ্যাটবট "মনের বন্ধু"-এর সাথership।
                        </p>
                        
                        <!-- Benefits -->
                        <div class="benefits-list">
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="benefit-text">
                                    এআই চ্যাটবট "মনের বন্ধু"
                                    <small>২৪/৭ কথা বলুন</small>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                </div>
                                <div class="benefit-text">
                                    ২০০+ অভিজ্ঞ মেন্টর
                                    <small>ক্যারিয়ার ও লাইফ স্কিল</small>
                                </div>
                            </div>
                            
                            <div class="benefit-item">
                                <div class="benefit-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="benefit-text">
                                    মানসিক স্বাস্থ্য কোর্স
                                    <small>বাংলায় তৈরি কোর্স</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Registration Form -->
            <div class="form-side">
                <div class="form-header">
                    <h2>নতুন অ্যাকাউন্ট খুলুন</h2>
                    <p>নিচের তথ্যগুলো পূরণ করুন</p>
                </div>
                
                <!-- Flash Messages -->
                <div id="flashMessage"></div>
                
                <!-- Registration Form -->
                <form id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <!-- Personal Information -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user label-icon"></i>
                            সম্পূর্ণ নাম
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="far fa-user"></i>
                            </span>
                            <input type="text" 
                                   class="form-input" 
                                   id="full_name" 
                                   name="full_name" 
                                   placeholder="আপনার পুরো নাম লিখুন"
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope label-icon"></i>
                            ইমেইল ঠিকানা
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="far fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-input" 
                                   id="email" 
                                   name="email" 
                                   placeholder="your@email.com"
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-phone label-icon"></i>
                            ফোন নম্বর
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-phone-alt"></i>
                            </span>
                            <input type="tel" 
                                   class="form-input" 
                                   id="phone" 
                                   name="phone" 
                                   placeholder="০১XXXXXXXXX"
                                   autocomplete="off"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Account Security -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock label-icon"></i>
                            পাসওয়ার্ড
                        </label>
                        <div class="password-wrapper">
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-input" 
                                       id="password" 
                                       name="password" 
                                       placeholder="********"
                                       required>
                                <span class="toggle-password" onclick="togglePassword('password', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-check-circle label-icon"></i>
                            পাসওয়ার্ড নিশ্চিত করুন
                        </label>
                        <div class="password-wrapper">
                            <div class="input-wrapper">
                                <span class="input-icon">
                                    <i class="fas fa-check"></i>
                                </span>
                                <input type="password" 
                                       class="form-input" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="********"
                                       required>
                                <span class="toggle-password" onclick="togglePassword('confirm_password', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar label-icon"></i>
                            জন্ম তারিখ
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </span>
                            <input type="date" 
                                   class="form-input" 
                                   id="date_of_birth" 
                                   name="date_of_birth"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-venus-mars label-icon"></i>
                            লিঙ্গ
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-user"></i>
                            </span>
                            <select class="form-input" id="gender" name="gender" required>
                                <option value="">নির্বাচন করুন</option>
                                <option value="male">পুরুষ</option>
                                <option value="female">মহিলা</option>
                                <option value="other">অন্যান্য</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt label-icon"></i>
                            ঠিকানা
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            <input type="text" 
                                   class="form-input" 
                                   id="address" 
                                   name="address" 
                                   placeholder="আপনার ঠিকানা"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-city label-icon"></i>
                            শহর
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fas fa-city"></i>
                            </span>
                            <input type="text" 
                                   class="form-input" 
                                   id="city" 
                                   name="city" 
                                   placeholder="শহরের নাম"
                                   required>
                        </div>
                    </div>
                    
                    <!-- Terms and Conditions -->
                    <div class="terms-group">
                        <label class="terms-checkbox">
                            <input type="checkbox" id="terms" name="terms" required>
                            <span>
                                আমি মেন্টোরার 
                                <a href="#" target="_blank">নিয়মাবলী</a> ও 
                                <a href="#" target="_blank">গোপনীয়তা নীতি</a> 
                                গ্রহণ করছি
                            </span>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="btn-group">
                        <button type="submit" class="btn-register" id="submitBtn">
                            <i class="fas fa-user-plus"></i>
                            রেজিস্ট্রেশন সম্পন্ন করুন
                        </button>
                    </div>
                </form>
                
                <!-- Login Link -->
                <div class="login-link">
                    <span>ইতিমধ্যে অ্যাকাউন্ট আছে?</span>
                    <a href="login.php">
                        লগইন করুন
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Password visibility toggle
        function togglePassword(inputId, element) {
            const input = document.getElementById(inputId);
            const icon = element.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
        
        // Loading overlay functions
        function showLoading(message = 'রেজিস্ট্রেশন হচ্ছে...') {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.getElementById('loadingText').textContent = message;
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Show toast message
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = 'toast-message';
            toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
        
        // Form validation
        function validateForm() {
            // Clear previous errors
            document.querySelectorAll('.error-message').forEach(el => el.remove());
            document.querySelectorAll('.form-input').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            let isValid = true;
            
            // Get values
            const fullName = document.getElementById('full_name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const dob = document.getElementById('date_of_birth').value;
            const gender = document.getElementById('gender').value;
            const address = document.getElementById('address').value.trim();
            const city = document.getElementById('city').value.trim();
            const terms = document.getElementById('terms').checked;
            
            // Validate Full Name
            if (fullName.length < 2) {
                showError('full_name', 'নাম অন্তত ২ অক্ষরের হতে হবে');
                isValid = false;
            }
            
            // Validate Email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showError('email', 'সঠিক ইমেইল ঠিকানা দিন');
                isValid = false;
            }
            
            // Validate Phone
            const phoneRegex = /^(?:\+88)?01[0-9]{9}$/;
            if (!phoneRegex.test(phone)) {
                showError('phone', 'সঠিক ফোন নম্বর দিন (01XXXXXXXXX)');
                isValid = false;
            }
            
            // Validate Password
            if (password.length < 4) {
                showError('password', 'পাসওয়ার্ড অন্তত ৪ অক্ষরের হতে হবে');
                isValid = false;
            }
            
            // Validate Confirm Password
            if (password !== confirmPassword) {
                showError('confirm_password', 'পাসওয়ার্ড মিলছে না');
                isValid = false;
            }
            
            // Validate DOB
            if (!dob) {
                showError('date_of_birth', 'জন্ম তারিখ নির্বাচন করুন');
                isValid = false;
            }
            
            // Validate Gender
            if (!gender) {
                showError('gender', 'লিঙ্গ নির্বাচন করুন');
                isValid = false;
            }
            
            // Validate Address
            if (address.length < 3) {
                showError('address', 'ঠিকানা দিন');
                isValid = false;
            }
            
            // Validate City
            if (city.length < 2) {
                showError('city', 'শহরের নাম দিন');
                isValid = false;
            }
            
            // Validate Terms
            if (!terms) {
                alert('নিয়মাবলী গ্রহণ করুন');
                isValid = false;
            }
            
            return isValid;
        }
        
        function showError(fieldId, message) {
            const input = document.getElementById(fieldId);
            input.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            errorDiv.style.color = '#dc3545';
            errorDiv.style.fontSize = '12px';
            errorDiv.style.marginTop = '4px';
            
            input.parentNode.appendChild(errorDiv);
        }
        
        // =============== AJAX Form Submission ===============
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate form first
                if (!validateForm()) {
                    return;
                }
                
                // Show loading
                showLoading();
                
                // Disable submit button
                $('#submitBtn').prop('disabled', true);
                
                // Get form data
                var formData = {
                    full_name: $('#full_name').val(),
                    email: $('#email').val(),
                    phone: $('#phone').val(),
                    password: $('#password').val(),
                    confirm_password: $('#confirm_password').val(),
                    date_of_birth: $('#date_of_birth').val(),
                    gender: $('#gender').val(),
                    address: $('#address').val(),
                    city: $('#city').val(),
                    terms: $('#terms').is(':checked') ? 'on' : '',
                    csrf_token: $('input[name="csrf_token"]').val()
                };
                
                // AJAX request
                $.ajax({
                    url: 'register-process.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Show success message on overlay
                            $('#loadingText').html('✅ ' + response.message);
                            
                            // Show success toast
                            showToast('রেজিস্ট্রেশন সম্পন্ন হয়েছে!', 'success');
                            
                            // Wait 2 seconds then redirect to login page
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            // Hide loading
                            hideLoading();
                            
                            // Show error message
                            alert('ত্রুটি: ' + response.message);
                            
                            // Re-enable submit button
                            $('#submitBtn').prop('disabled', false);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Hide loading
                        hideLoading();
                        
                        // Show error message
                        alert('সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                        console.log('Error:', error);
                        console.log('Response:', xhr.responseText);
                        
                        // Re-enable submit button
                        $('#submitBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</body>
</html>