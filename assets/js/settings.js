// assets/js/settings.js

$(document).ready(function() {
    console.log("✅ Settings page loaded");
    
    // Load saved settings
    loadSettings();
    
    // Profile form submit
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });
    
    // Notification form submit
    $('#notificationForm').on('submit', function(e) {
        e.preventDefault();
        updateNotifications();
    });
    
    // Privacy form submit
    $('#privacyForm').on('submit', function(e) {
        e.preventDefault();
        updatePrivacy();
    });
    
    // Delete account
    $('#confirmDeleteBtn').click(function() {
        deleteAccount();
    });
    
    // Close modal
    $('#closeModalBtn, .close-modal').click(function() {
        $('#deleteModal').removeClass('active');
    });
    
    // Close modal on background click
    $(window).click(function(e) {
        if ($(e.target).hasClass('modal')) {
            $('#deleteModal').removeClass('active');
        }
    });
});

// ==================== LOAD SETTINGS ====================
function loadSettings() {
    $.ajax({
        url: '../api/settings/get-settings.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Load profile settings
                $('#full_name').val(response.data.full_name || '');
                $('#email').val(response.data.email || '');
                $('#phone').val(response.data.phone || '');
                $('#bio').val(response.data.bio || '');
                
                // Load notification settings
                if (response.data.notifications) {
                    $('#email_notifications').prop('checked', response.data.notifications.email == 1);
                    $('#push_notifications').prop('checked', response.data.notifications.push == 1);
                    $('#session_reminders').prop('checked', response.data.notifications.session_reminders == 1);
                    $('#weekly_report').prop('checked', response.data.notifications.weekly_report == 1);
                    $('#marketing_emails').prop('checked', response.data.notifications.marketing_emails == 1);
                }
                
                // Load privacy settings
                if (response.data.privacy) {
                    $(`input[name="profile_visibility"][value="${response.data.privacy.profile_visibility}"]`).prop('checked', true);
                    $(`input[name="data_sharing"][value="${response.data.privacy.data_sharing}"]`).prop('checked', true);
                    $('#show_activity_status').prop('checked', response.data.privacy.show_activity_status == 1);
                    $('#show_last_seen').prop('checked', response.data.privacy.show_last_seen == 1);
                }
            }
        },
        error: function() {
            showMessage('error', 'সেটিংস লোড করতে সমস্যা হয়েছে');
        }
    });
}

// ==================== UPDATE PROFILE ====================
function updateProfile() {
    const data = {
        full_name: $('#full_name').val().trim(),
        email: $('#email').val().trim(),
        phone: $('#phone').val().trim(),
        bio: $('#bio').val().trim()
    };
    
    if (!data.full_name) {
        showMessage('error', 'নাম দিন');
        return;
    }
    
    if (!data.email) {
        showMessage('error', 'ইমেইল দিন');
        return;
    }
    
    const $btn = $('#saveProfileBtn');
    $btn.html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/settings/update-profile.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
                // Update sidebar name
                $('.user-name').text(data.full_name);
            } else {
                showMessage('error', response.message);
            }
        },
        error: function() {
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('<i class="fas fa-save"></i> সংরক্ষণ করুন').prop('disabled', false);
        }
    });
}

// ==================== UPDATE NOTIFICATIONS ====================
function updateNotifications() {
    const data = {
        email_notifications: $('#email_notifications').is(':checked') ? 1 : 0,
        push_notifications: $('#push_notifications').is(':checked') ? 1 : 0,
        session_reminders: $('#session_reminders').is(':checked') ? 1 : 0,
        weekly_report: $('#weekly_report').is(':checked') ? 1 : 0,
        marketing_emails: $('#marketing_emails').is(':checked') ? 1 : 0
    };
    
    const $btn = $('#saveNotificationBtn');
    $btn.html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/settings/update-notifications.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
            } else {
                showMessage('error', response.message);
            }
        },
        error: function() {
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('<i class="fas fa-save"></i> সংরক্ষণ করুন').prop('disabled', false);
        }
    });
}

// ==================== UPDATE PRIVACY ====================
function updatePrivacy() {
    const data = {
        profile_visibility: $('input[name="profile_visibility"]:checked').val(),
        data_sharing: $('input[name="data_sharing"]:checked').val(),
        show_activity_status: $('#show_activity_status').is(':checked') ? 1 : 0,
        show_last_seen: $('#show_last_seen').is(':checked') ? 1 : 0
    };
    
    const $btn = $('#savePrivacyBtn');
    $btn.html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/settings/update-privacy.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
            } else {
                showMessage('error', response.message);
            }
        },
        error: function() {
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('<i class="fas fa-save"></i> সংরক্ষণ করুন').prop('disabled', false);
        }
    });
}

// ==================== DELETE ACCOUNT ====================
function deleteAccount() {
    const password = $('#confirm_password').val();
    
    if (!password) {
        showMessage('error', 'পাসওয়ার্ড দিন');
        return;
    }
    
    const $btn = $('#confirmDeleteBtn');
    $btn.html('<span class="spinner"></span> প্রসেসিং...').prop('disabled', true);
    
    $.ajax({
        url: '../api/settings/delete-account.php',
        type: 'POST',
        data: JSON.stringify({ password: password }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
                setTimeout(function() {
                    window.location.href = '../auth/logout.php';
                }, 2000);
            } else {
                showMessage('error', response.message);
                $('#confirm_password').val('');
            }
        },
        error: function() {
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('নিশ্চিত করুন').prop('disabled', false);
        }
    });
}

// ==================== DATA EXPORT ====================
function exportData(type) {
    window.location.href = '../api/settings/export-data.php?type=' + type;
}

function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? '✅' : '❌';
    const alertHtml = `
        <div class="alert ${alertClass}">
            ${icon} ${message}
            <button onclick="$(this).parent().remove()" style="float: right; background: none; border: none; cursor: pointer;">✖</button>
        </div>
    `;
    $('.settings-container').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 3000);
}