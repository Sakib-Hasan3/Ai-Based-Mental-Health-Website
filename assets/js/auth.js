// assets/js/auth.js

$(document).ready(function() {
    
    // Toggle Password Visibility
    $('.toggle-password').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Form Validation
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Reset previous errors
        $('.form-control').removeClass('is-invalid');
        $('.invalid-feedback').remove();
        
        let isValid = true;
        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        
        // Email validation
        if (email === '') {
            showError('email', 'ইমেইল ঠিকানা দিন');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showError('email', 'সঠিক ইমেইল ঠিকানা দিন');
            isValid = false;
        }
        
        // Password validation
        if (password === '') {
            showError('password', 'পাসওয়ার্ড দিন');
            isValid = false;
        }
        
        if (isValid) {
            // Show loading state
            const $btn = $(this).find('button[type="submit"]');
            const originalText = $btn.html();
            $btn.html('<span class="spinner"></span> লগইন হচ্ছে...').prop('disabled', true);
            
            // Submit form via AJAX
            $.ajax({
                url: 'login-process.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showNotification('success', response.message);
                        
                        // Redirect after delay
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 1500);
                    } else {
                        // Show error message
                        showNotification('error', response.message);
                        
                        // Reset button
                        $btn.html(originalText).prop('disabled', false);
                        
                        // Highlight field if specific error
                        if (response.field) {
                            showError(response.field, response.message);
                        }
                    }
                },
                error: function(xhr, status, error) {
                    showNotification('error', 'সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                    $btn.html(originalText).prop('disabled', false);
                    console.error('Login error:', error);
                }
            });
        }
    });
    
    // Show error message under field
    function showError(field, message) {
        $(`#${field}`).addClass('is-invalid');
        $(`#${field}`).after(`<div class="invalid-feedback">${message}</div>`);
    }
    
    // Email validation
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
    
    // Show notification
    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $('.auth-form').prepend(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Password strength meter (for registration page)
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
    
    // Check password strength
    function checkPasswordStrength(password) {
        if (password.length < 6) {
            return 'weak';
        }
        
        let strength = 0;
        
        // Contains lowercase
        if (password.match(/[a-z]+/)) strength++;
        
        // Contains uppercase
        if (password.match(/[A-Z]+/)) strength++;
        
        // Contains number
        if (password.match(/[0-9]+/)) strength++;
        
        // Contains special character
        if (password.match(/[$@#&!]+/)) strength++;
        
        if (strength < 2) return 'weak';
        if (strength < 4) return 'medium';
        return 'strong';
    }
    
    // Remember me checkbox styling
    $('.remember-me').on('click', function() {
        const checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
    });
    
    // Floating label effect
    $('.form-control').on('focus blur', function(e) {
        const $label = $(this).closest('.form-group').find('label');
        
        if (e.type === 'focus' || $(this).val() !== '') {
            $label.addClass('float-label');
        } else {
            $label.removeClass('float-label');
        }
    });
    
    // Check for saved email (local storage)
    const savedEmail = localStorage.getItem('remembered_email');
    if (savedEmail) {
        $('#email').val(savedEmail);
        $('#remember').prop('checked', true);
    }
    
    // Save email when remember me is checked
    $('#remember').on('change', function() {
        if ($(this).is(':checked')) {
            localStorage.setItem('remembered_email', $('#email').val());
        } else {
            localStorage.removeItem('remembered_email');
        }
    });
    
    // Enter key press handler
    $('.form-control').on('keypress', function(e) {
        if (e.which === 13) {
            $('#loginForm').submit();
        }
    });
    
    // Clear form on page load (optional)
    $('#email, #password').val('');
    
    // Add ripple effect to buttons
    $('.btn-login').on('click', function(e) {
        const ripple = $('<span class="ripple"></span>');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;
        
        ripple.css({
            width: size,
            height: size,
            left: x,
            top: y
        });
        
        $(this).append(ripple);
        
        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});