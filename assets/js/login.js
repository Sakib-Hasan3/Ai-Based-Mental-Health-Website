// assets/js/login.js

$(document).ready(function() {
    
    // Toggle Password Visibility
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
    
    // Floating Label Effect
    $('.form-input').on('focus blur', function() {
        const wrapper = $(this).closest('.input-wrapper');
        if ($(this).val() !== '') {
            wrapper.addClass('float');
        } else {
            wrapper.removeClass('float');
        }
    });
    
    // Form Submission with Animation
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        // Remove existing errors
        $('.form-input').removeClass('error');
        $('.error-message').remove();
        
        // Get values
        const email = $('#email').val().trim();
        const password = $('#password').val().trim();
        let isValid = true;
        
        // Validation
        if (email === '') {
            showError('email', 'ইমেইল ঠিকানা দিন');
            isValid = false;
        } else if (!isValidEmail(email) && !isValidPhone(email)) {
            showError('email', 'সঠিক ইমেইল বা ফোন নম্বর দিন');
            isValid = false;
        }
        
        if (password === '') {
            showError('password', 'পাসওয়ার্ড দিন');
            isValid = false;
        }
        
        if (isValid) {
            // Show loading state
            const $btn = $(this).find('.login-btn');
            const originalText = $btn.html();
            $btn.html('<span class="spinner"></span> লগইন হচ্ছে...').addClass('loading');
            
            // Simulate API call (replace with actual AJAX)
            setTimeout(function() {
                // For demo - remove in production
                window.location.href = '../index.php';
                
                // Reset button
                $btn.html(originalText).removeClass('loading');
            }, 2000);
            
            // Uncomment for actual AJAX
            /*
            $.ajax({
                url: 'login-process.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('success', response.message);
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1500);
                    } else {
                        showNotification('error', response.message);
                        if (response.field) {
                            showError(response.field, response.message);
                        }
                    }
                },
                error: function() {
                    showNotification('error', 'সার্ভারে সমস্যা হয়েছে');
                },
                complete: function() {
                    $btn.html(originalText).removeClass('loading');
                }
            });
            */
        }
    });
    
    // Show error under field
    function showError(field, message) {
        $(`#${field}`).addClass('error');
        $(`#${field}`).closest('.form-group').append(
            `<div class="error-message" style="color: var(--danger); font-size: 13px; margin-top: 5px;">
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
        return /^(?:\+88|01)?\d{11}$/.test(phone);
    }
    
    // Show notification
    function showNotification(type, message) {
        const alertHtml = `
            <div class="alert alert-${type}">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
                <button class="close-btn" onclick="this.parentElement.remove()">
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
    
    // Remember Me functionality
    $('#remember').on('change', function() {
        const email = $('#email').val();
        if ($(this).is(':checked') && email) {
            localStorage.setItem('remembered_email', email);
        } else {
            localStorage.removeItem('remembered_email');
        }
    });
    
    // Load remembered email
    const rememberedEmail = localStorage.getItem('remembered_email');
    if (rememberedEmail) {
        $('#email').val(rememberedEmail);
        $('#remember').prop('checked', true);
    }
    
    // Ripple effect on button
    $('.login-btn').on('click', function(e) {
        if (!$(this).hasClass('loading')) {
            const ripple = $('<span class="ripple"></span>');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            
            ripple.css({
                width: size,
                height: size,
                left: e.clientX - rect.left - size/2,
                top: e.clientY - rect.top - size/2
            });
            
            $(this).append(ripple);
            
            setTimeout(() => ripple.remove(), 600);
        }
    });
    
    // Auto-fill demo credentials (for demo only)
    $('.demo-credentials').click(function() {
        $('#email').val('user@mentora.com');
        $('#password').val('123456');
        showNotification('info', 'ডেমো ক্রেডেনশিয়াল অটো-ফিল করা হয়েছে');
    });
    
    // Enter key submission
    $('.form-input').on('keypress', function(e) {
        if (e.which === 13) {
            $('#loginForm').submit();
        }
    });
    
    // Input focus animations
    $('.form-input').on('focus', function() {
        $(this).closest('.input-wrapper').find('.input-icon').css('color', 'var(--primary)');
    });
    
    $('.form-input').on('blur', function() {
        if (!$(this).val()) {
            $(this).closest('.input-wrapper').find('.input-icon').css('color', 'var(--gray-400)');
        }
    });
    
    // Parallax effect on shapes
    $(document).mousemove(function(e) {
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        $('.floating-shape').each(function(index) {
            const speed = (index + 1) * 20;
            const x = (mouseX * speed) - (speed / 2);
            const y = (mouseY * speed) - (speed / 2);
            
            $(this).css({
                transform: `translate(${x}px, ${y}px)`
            });
        });
    });
});