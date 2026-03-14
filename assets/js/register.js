// assets/js/register.js

$(document).ready(function() {
    
    // ==================== STATE MANAGEMENT ====================
    let currentStep = 1;
    const totalSteps = 3;
    let formData = {
        full_name: '',
        email: '',
        phone: '',
        password: '',
        confirm_password: '',
        date_of_birth: '',
        gender: '',
        address: '',
        city: '',
        terms: false
    };
    
    // ==================== INITIALIZATION ====================
    updateSteps();
    loadFormData();
    
    // ==================== STEP NAVIGATION ====================
    
    // Next button click
    $('.btn-next').click(function(e) {
        e.preventDefault();
        console.log('Next button clicked, Current step:', currentStep);
        console.log('Form data before validation:', formData);
        
        if (validateStep(currentStep)) {
            console.log('Validation passed!');
            saveStepData();
            
            if (currentStep < totalSteps) {
                currentStep++;
                console.log('Moving to step:', currentStep);
                updateSteps();
                loadStepData();
            }
        } else {
            console.log('Validation failed for step:', currentStep);
        }
    });
    
    // Previous button click
    $('.btn-prev').click(function() {
        if (currentStep > 1) {
            saveStepData();
            currentStep--;
            updateSteps();
            loadStepData();
        }
    });
    
    // Update step display
    function updateSteps() {
        // Update step indicators
        $('.progress-step').removeClass('active completed');
        
        for (let i = 1; i <= totalSteps; i++) {
            if (i === currentStep) {
                $(`.progress-step[data-step="${i}"]`).addClass('active');
            } else if (i < currentStep) {
                $(`.progress-step[data-step="${i}"]`).addClass('completed');
            }
        }
        
        // Show/hide form sections
        $('.form-section').removeClass('active');
        $(`#step${currentStep}`).addClass('active');
        
        // Update prev button state
        if (currentStep === 1) {
            $('.btn-prev').prop('disabled', true);
        } else {
            $('.btn-prev').prop('disabled', false);
        }
        
        // Update next button text
        if (currentStep === totalSteps) {
            $('.btn-next').hide();
            $('.btn-register').show();
        } else {
            $('.btn-next').show();
            $('.btn-register').hide();
        }
    }
    
    // ==================== VALIDATION ====================
    
    function validateStep(step) {
        let isValid = true;
        
        // Remove existing errors
        $('.form-input').removeClass('error');
        $('.error-message').remove();
        
        switch(step) {
            case 1: // Personal Information
                const fullName = $('#full_name').val().trim();
                const email = $('#email').val().trim();
                const phone = $('#phone').val().trim();
                
                if (fullName === '') {
                    showError('full_name', 'নাম দিন');
                    isValid = false;
                } else if (fullName.length < 2) {
                    showError('full_name', 'নাম কমপক্ষে ২ অক্ষর হতে হবে');
                    isValid = false;
                }
                
                if (email === '') {
                    showError('email', 'ইমেইল দিন');
                    isValid = false;
                } else if (!isValidEmail(email)) {
                    showError('email', 'সঠিক ইমেইল দিন');
                    isValid = false;
                }
                
                if (phone === '') {
                    showError('phone', 'ফোন নম্বর দিন');
                    isValid = false;
                } else if (!isValidPhone(phone)) {
                    showError('phone', 'ফোন নম্বর ০১ দিয়ে শুরু হতে হবে এবং ১১ ডিজিট হতে হবে');
                    isValid = false;
                }
                break;
                
            case 2: // Account Security
                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();
                
                if (password === '') {
                    showError('password', 'পাসওয়ার্ড দিন');
                    isValid = false;
                } else if (password.length < 4) {
                    showError('password', 'পাসওয়ার্ড কমপক্ষে ৪ অক্ষরের হতে হবে');
                    isValid = false;
                }
                
                if (confirmPassword === '') {
                    showError('confirm_password', 'পাসওয়ার্ড আবার দিন');
                    isValid = false;
                } else if (password !== confirmPassword) {
                    showError('confirm_password', 'পাসওয়ার্ড মিলছে না');
                    isValid = false;
                }
                break;
                
            case 3: // Additional Info
                const dob = $('#date_of_birth').val();
                const gender = $('#gender').val();
                const terms = $('#terms').is(':checked');
                
                if (dob === '') {
                    showError('date_of_birth', 'জন্ম তারিখ দিন');
                    isValid = false;
                }
                
                if (gender === '') {
                    showError('gender', 'লিঙ্গ নির্বাচন করুন');
                    isValid = false;
                }
                
                if (!terms) {
                    showError('terms', 'নিয়মাবলী গ্রহণ করুন');
                    isValid = false;
                }
                break;
        }
        
        return isValid;
    }
    
    // Show error message
    function showError(field, message) {
        $(`#${field}`).addClass('error');
        $(`#${field}`).closest('.form-group').append(
            `<div class="error-message">
                <i class="fas fa-exclamation-circle"></i> ${message}
            </div>`
        );
    }
    
    // Email validation
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
    
    // Phone validation (Bangladesh)
    function isValidPhone(phone) {
        // Accept: 01XXXXXXXXX (11 digits) or +8801XXXXXXXXX
        const regex = /^((?:\+88)?01[0-9]{9}|01[0-9]{9})$/;
        console.log('Validating phone:', phone, 'Result:', regex.test(phone));
        return regex.test(phone);
    }
    
    // ==================== DATA MANAGEMENT ====================
    
    // Save current step data
    function saveStepData() {
        switch(currentStep) {
            case 1:
                formData.full_name = $('#full_name').val().trim();
                formData.email = $('#email').val().trim();
                formData.phone = $('#phone').val().trim();
                break;
            case 2:
                formData.password = $('#password').val();
                formData.confirm_password = $('#confirm_password').val();
                break;
            case 3:
                formData.date_of_birth = $('#date_of_birth').val();
                formData.gender = $('#gender').val();
                formData.address = $('#address').val().trim();
                formData.city = $('#city').val().trim();
                formData.terms = $('#terms').is(':checked');
                break;
        }
        
        // Save to localStorage for persistence
        localStorage.setItem('registrationForm', JSON.stringify(formData));
    }
    
    // Load data into form
    function loadStepData() {
        switch(currentStep) {
            case 1:
                $('#full_name').val(formData.full_name);
                $('#email').val(formData.email);
                $('#phone').val(formData.phone);
                break;
            case 2:
                $('#password').val(formData.password);
                $('#confirm_password').val(formData.confirm_password);
                break;
            case 3:
                $('#date_of_birth').val(formData.date_of_birth);
                $('#gender').val(formData.gender);
                $('#address').val(formData.address);
                $('#city').val(formData.city);
                $('#terms').prop('checked', formData.terms);
                break;
        }
    }
    
    // Load saved form data
    function loadFormData() {
        const saved = localStorage.getItem('registrationForm');
        if (saved) {
            formData = JSON.parse(saved);
            loadStepData();
        }
    }
    
    // ==================== PASSWORD STRENGTH METER ====================
    
    $('#password').on('keyup', function() {
        const password = $(this).val();
        const strength = checkPasswordStrength(password);
        
        $('.strength-meter-fill').removeClass('weak medium strong');
        
        if (strength === 'weak') {
            $('.strength-meter-fill').addClass('weak');
            $('.strength-text').text('দুর্বল পাসওয়ার্ড');
        } else if (strength === 'medium') {
            $('.strength-meter-fill').addClass('medium');
            $('.strength-text').text('মাঝারি পাসওয়ার্ড');
        } else if (strength === 'strong') {
            $('.strength-meter-fill').addClass('strong');
            $('.strength-text').text('শক্তিশালী পাসওয়ার্ড');
        } else {
            $('.strength-text').text('');
        }
    });
    
    function checkPasswordStrength(password) {
        if (password.length < 6) return 'weak';
        
        let score = 0;
        if (password.match(/[a-z]/)) score++;
        if (password.match(/[A-Z]/)) score++;
        if (password.match(/[0-9]/)) score++;
        if (password.match(/[^a-zA-Z0-9]/)) score++;
        
        if (score < 2) return 'weak';
        if (score < 4) return 'medium';
        return 'strong';
    }
    
    // ==================== FORM SUBMISSION ====================
    
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        if (validateStep(currentStep)) {
            saveStepData();
            
            // Show loading state
            const $btn = $(this).find('.btn-register');
            $btn.html('<span class="spinner"></span> রেজিস্ট্রেশন হচ্ছে...').addClass('loading');
            
            // Collect all data
            const submitData = {
                ...formData,
                action: 'register'
            };
            
            // Simulate API call (replace with actual AJAX)
            setTimeout(function() {
                // Show success message
                showNotification('success', 'রেজিস্ট্রেশন সফল হয়েছে! ইমেইল ভেরিফাই করুন।');
                
                // Redirect to login after 2 seconds
                setTimeout(function() {
                    window.location.href = 'login.php?registered=1';
                }, 2000);
                
                // Clear saved data
                localStorage.removeItem('registrationForm');
                
            }, 2000);
            
            // Uncomment for actual AJAX
            /*
            $.ajax({
                url: 'register-process.php',
                type: 'POST',
                data: submitData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        localStorage.removeItem('registrationForm');
                        setTimeout(() => {
                            window.location.href = 'login.php?registered=1';
                        }, 2000);
                    } else {
                        showNotification('error', response.message);
                    }
                },
                error: function() {
                    showNotification('error', 'সার্ভারে সমস্যা হয়েছে');
                },
                complete: function() {
                    $btn.html('রেজিস্ট্রেশন সম্পন্ন করুন').removeClass('loading');
                }
            });
            */
        }
    });
    
    // ==================== NOTIFICATION SYSTEM ====================
    
    function showNotification(type, message) {
        const alertHtml = `
            <div class="alert alert-${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
                <button class="close-btn" onclick="this.parentElement.remove()" style="margin-left: auto; background: none; border: none; cursor: pointer;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        $('.form-side').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // ==================== REAL-TIME VALIDATION ====================
    
    // Email availability check
    let emailCheckTimeout;
    $('#email').on('keyup', function() {
        clearTimeout(emailCheckTimeout);
        const email = $(this).val();
        
        if (isValidEmail(email)) {
            emailCheckTimeout = setTimeout(function() {
                // Simulate email check (replace with AJAX)
                console.log('Checking email:', email);
            }, 500);
        }
    });
    
    // Phone availability check
    let phoneCheckTimeout;
    $('#phone').on('keyup', function() {
        clearTimeout(phoneCheckTimeout);
        const phone = $(this).val();
        
        if (isValidPhone(phone)) {
            phoneCheckTimeout = setTimeout(function() {
                // Simulate phone check (replace with AJAX)
                console.log('Checking phone:', phone);
            }, 500);
        }
    });
    
    // ==================== UI ENHANCEMENTS ====================
    
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).closest('.password-wrapper').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    // Input focus effects
    $('.form-input').on('focus', function() {
        $(this).closest('.input-wrapper').find('.input-icon').css('color', 'var(--primary)');
    });
    
    $('.form-input').on('blur', function() {
        if (!$(this).val()) {
            $(this).closest('.input-wrapper').find('.input-icon').css('color', 'var(--gray-400)');
        }
    });
    
    // Floating label effect
    $('.form-input').each(function() {
        if ($(this).val()) {
            $(this).closest('.input-wrapper').find('.input-icon').css('color', 'var(--primary)');
        }
    });
    
    // ==================== PARALLAX EFFECT ====================
    
    $(document).mousemove(function(e) {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        $('.particle').each(function(index) {
            const speed = (index + 1) * 20;
            const x = (mouseX * speed) - (speed / 2);
            const y = (mouseY * speed) - (speed / 2);
            
            $(this).css({
                transform: `translate(${x}px, ${y}px)`
            });
        });
    });
    
    // ==================== INITIALIZE ====================
    
    console.log('Registration page initialized');
    
    // Clear form on page load (optional)
    // localStorage.removeItem('registrationForm');
});