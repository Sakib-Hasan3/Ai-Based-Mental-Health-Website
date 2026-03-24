// assets/js/profile.js
$(document).ready(function() {
    console.log("✅ Profile page loaded");
    
    // ==================== TAB SWITCHING ====================
    $('.tab-btn').click(function() {
        const tab = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-content').removeClass('active');
        $(`#${tab}`).addClass('active');
    });
    
    // ==================== TOGGLE PASSWORD VISIBILITY ====================
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
    
    // ==================== PASSWORD STRENGTH METER ====================
    $('#new_password').on('keyup', function() {
        const password = $(this).val();
        let strength = 'weak';
        
        if (password.length >= 6) {
            if (password.match(/[a-z]/) && password.match(/[A-Z]/) && password.match(/[0-9]/) && password.match(/[^a-zA-Z0-9]/)) {
                strength = 'strong';
            } else if (password.match(/[a-z]/) && (password.match(/[A-Z]/) || password.match(/[0-9]/))) {
                strength = 'medium';
            }
        }
        
        $('.strength-meter-fill').removeClass('weak medium strong');
        if (password.length === 0) {
            $('.strength-meter-fill').css('width', '0');
            $('.strength-text').text('');
        } else if (strength === 'weak') {
            $('.strength-meter-fill').addClass('weak');
            $('.strength-text').text('দুর্বল পাসওয়ার্ড');
        } else if (strength === 'medium') {
            $('.strength-meter-fill').addClass('medium');
            $('.strength-text').text('মাঝারি পাসওয়ার্ড');
        } else {
            $('.strength-meter-fill').addClass('strong');
            $('.strength-text').text('শক্তিশালী পাসওয়ার্ড');
        }
    });
    
    // ==================== AVATAR UPLOAD ====================
    $('#editAvatarBtn, .avatar-edit').click(function() {
        $('#avatarInput').click();
    });
    
    $('#avatarInput').on('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (!file.type.match('image.*')) {
            showMessage('error', 'শুধুমাত্র ছবি ফাইল আপলোড করুন');
            return;
        }
        
        if (file.size > 2 * 1024 * 1024) {
            showMessage('error', 'ছবির সাইজ ২MB এর কম হতে হবে');
            return;
        }
        
        const formData = new FormData();
        formData.append('avatar', file);
        
        $.ajax({
            url: '../api/upload-avatar.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    $('#profileAvatar').attr('src', res.avatar_url + '?t=' + new Date().getTime());
                    showMessage('success', 'প্রোফাইল ছবি আপডেট হয়েছে');
                } else {
                    showMessage('error', res.message);
                }
            },
            error: function() {
                showMessage('error', 'ছবি আপলোড করতে সমস্যা হয়েছে');
            }
        });
    });
    
    // ==================== PROFILE FORM SUBMIT ====================
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            full_name: $('#full_name').val(),
            email: $('#email').val(),
            phone: $('#phone').val(),
            date_of_birth: $('#date_of_birth').val(),
            gender: $('#gender').val(),
            address: $('#address').val(),
            city: $('#city').val(),
            bio: $('#bio').val()
        };
        
        $('#saveProfileBtn').html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
        
        $.ajax({
            url: '../api/update-profile.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    showMessage('success', res.message);
                    $('.user-name').text(formData.full_name);
                    $('.hero-name').text(formData.full_name);
                } else {
                    showMessage('error', res.message);
                }
            },
            error: function() {
                showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
            },
            complete: function() {
                $('#saveProfileBtn').html('<i class="fas fa-save"></i> সংরক্ষণ করুন').prop('disabled', false);
            }
        });
    });
    
    // ==================== PASSWORD FORM SUBMIT ====================
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            current_password: $('#current_password').val(),
            new_password: $('#new_password').val(),
            confirm_password: $('#confirm_password').val()
        };
        
        if (formData.new_password !== formData.confirm_password) {
            showMessage('error', 'পাসওয়ার্ড মিলছে না');
            return;
        }
        
        $('#changePasswordBtn').html('<span class="spinner"></span> পরিবর্তন করা হচ্ছে...').prop('disabled', true);
        
        $.ajax({
            url: '../api/change-password.php',
            type: 'POST',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                const res = JSON.parse(response);
                if (res.success) {
                    showMessage('success', res.message);
                    $('#passwordForm')[0].reset();
                    $('.strength-meter-fill').css('width', '0');
                    $('.strength-text').text('');
                } else {
                    showMessage('error', res.message);
                }
            },
            error: function() {
                showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
            },
            complete: function() {
                $('#changePasswordBtn').html('<i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন').prop('disabled', false);
            }
        });
    });
    
    // ==================== HELPER FUNCTIONS ====================
    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass}" style="margin-bottom: 20px;">
                <i class="fas ${icon}"></i>
                <span>${message}</span>
                <button onclick="$(this).parent().remove()" style="margin-left: auto; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>
            </div>
        `;
        
        $('.tab-content.active').prepend(alertHtml);
        
        setTimeout(() => {
            $('.alert').fadeOut('slow', function() { $(this).remove(); });
        }, 5000);
    }
});

// ==================== RESET FORM FUNCTION ====================
function resetForm() {
    $('#profileForm')[0].reset();
    $('#full_name').val($('#full_name').data('original'));
    $('#email').val($('#email').data('original'));
    $('#phone').val($('#phone').data('original'));
    $('#date_of_birth').val($('#date_of_birth').data('original'));
    $('#gender').val($('#gender').data('original'));
    $('#address').val($('#address').data('original'));
    $('#city').val($('#city').data('original'));
    $('#bio').val($('#bio').data('original'));
    showMessage('success', 'ফর্ম রিসেট করা হয়েছে');
}