// assets/js/mentor.js

let currentMentorId = null;
let currentEnrollmentId = null;

$(document).ready(function() {
    console.log("✅ Mentor page loaded");
    console.log("✅ jQuery version:", $.fn.jquery);
    console.log("✅ Document ready fired");
    
    // Verify functions are available
    console.log("✅ openEnrollModal available:", typeof openEnrollModal);
    console.log("✅ submitEnrollment available:", typeof submitEnrollment);
    
    // Load mentors on page load
    loadMentors();
    
    // Filter button click
    $('#applyFilter').click(function() {
        console.log("🔍 Filter applied");
        loadMentors();
    });
    
    // Reset filter
    $('#resetFilter').click(function() {
        console.log("🔄 Filter reset");
        $('#specialtyFilter').val('');
        $('#searchFilter').val('');
        $('#ratingFilter').val('');
        $('#availabilityFilter').val('');
        loadMentors();
    });
    
    // Close modal
    $('.close-modal, #closeModalBtn').click(function() {
        console.log("✖️ Modal closed");
        closeModal();
    });
    
    // Close on background click
    $(window).click(function(e) {
        if ($(e.target).hasClass('modal')) {
            console.log("✖️ Modal closed (background click)");
            closeModal();
        }
    });
});

// ==================== LOAD MENTORS ====================
function loadMentors() {
    const params = new URLSearchParams({
        specialty: $('#specialtyFilter').val(),
        search: $('#searchFilter').val(),
        rating: $('#ratingFilter').val(),
        available: $('#availabilityFilter').val()
    });
    
    $('#mentorsGrid').html('<div class="empty-state"><i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...</div>');
    
    $.ajax({
        url: '../api/get-mentors.php?' + params.toString(),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayMentors(response.data);
            } else {
                $('#mentorsGrid').html('<div class="empty-state">মেন্টর লোড করতে সমস্যা হয়েছে</div>');
            }
        },
        error: function() {
            $('#mentorsGrid').html('<div class="empty-state">মেন্টর লোড করতে সমস্যা হয়েছে</div>');
        }
    });
}

// ==================== DISPLAY MENTORS ====================
function displayMentors(mentors) {
    if (!mentors || mentors.length === 0) {
        $('#mentorsGrid').html(`
            <div class="empty-state">
                <i class="fas fa-user-graduate"></i>
                <h3>কোনো মেন্টর পাওয়া যায়নি</h3>
                <p>অন্য ক্যাটাগরি নির্বাচন করুন</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    mentors.forEach(mentor => {
        const tierInfo = getTierInfo(mentor.mentor_tier);
        const rating = parseFloat(mentor.rating) || 0;
        const ratingStars = getRatingStars(rating);
        
        html += `
            <div class="mentor-card" data-id="${mentor.id}">
                <div class="mentor-badge ${tierInfo.class}">${tierInfo.label}</div>
                <div class="mentor-card-header">
                    <div class="mentor-avatar">
                        <img src="../assets/images/avatars/${mentor.profile_image || 'default-avatar.svg'}" alt="${mentor.name}">
                    </div>
                    <h3 class="mentor-name">${escapeHtml(mentor.name)}</h3>
                    <div class="mentor-specialty">${escapeHtml(mentor.specialty)}</div>
                    <div class="mentor-rating">
                        <div class="rating-stars">${ratingStars}</div>
                        <span class="rating-value">${rating.toFixed(1)}</span>
                        <span>(${mentor.total_sessions} সেশন)</span>
                    </div>
                </div>
                <div class="mentor-card-body">
                    <div class="mentor-experience">
                        <i class="fas fa-briefcase"></i>
                        <span>${mentor.experience_years} বছর অভিজ্ঞতা</span>
                    </div>
                    <div class="mentor-experience">
                        <i class="fas fa-graduation-cap"></i>
                        <span>${escapeHtml(mentor.qualification || 'যোগ্যতা দেখানো হয়নি')}</span>
                    </div>
                    <div class="mentor-bio">${escapeHtml(mentor.bio || 'কোনো বায়ো যোগ করা হয়নি')}</div>
                </div>
                <div class="mentor-footer">
                    <div class="mentor-price">
                        ৳${mentor.hourly_rate} <span>/সেশন</span>
                    </div>
                    <div>
                        <a href="mentor-details.php?id=${mentor.id}" class="btn-view">
                            <i class="fas fa-eye"></i> বিস্তারিত
                        </a>
                        <button class="btn-enroll" onclick="openEnrollModal(${mentor.id})">
                            <i class="fas fa-calendar-plus"></i> বুক করুন
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    $('#mentorsGrid').html(html);
}

// ==================== OPEN ENROLL MODAL ====================
function openEnrollModal(mentorId) {
    console.log("📅 Opening enrollment modal for mentor:", mentorId);
    currentMentorId = mentorId;
    
    // Get mentor details
    $.ajax({
        url: '../api/get-mentor-details.php?id=' + mentorId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log("✅ Mentor details received:", response);
            if (response.success) {
                const mentor = response.data;
                $('#enrollMentorName').text(mentor.name);
                $('#enrollPrice').text('৳' + mentor.hourly_rate);
                $('#enrollModal').addClass('active');
                console.log("✅ Modal opened for:", mentor.name);
            } else {
                console.error("❌ Error response:", response.message);
                showMessage('error', response.message || 'মেন্টর তথ্য লোড করতে ব্যর্থ');
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ AJAX Error:", error, xhr);
            showMessage('error', 'মেন্টর তথ্য লোড করতে সমস্যা হয়েছে');
        }
    });
}

// ==================== SUBMIT ENROLLMENT ====================
function submitEnrollment() {
    console.log("📝 Submit enrollment called");
    const sessionDate = $('#sessionDate').val();
    const sessionTime = $('#sessionTime').val();
    const sessionType = $('#sessionType').val();
    const topic = $('#sessionTopic').val();
    
    console.log("📋 Form values:", { sessionDate, sessionTime, sessionType, topic, mentorId: currentMentorId });
    
    if (!sessionDate || !sessionTime) {
        showMessage('error', 'দয়া করে সেশনের তারিখ ও সময় নির্বাচন করুন');
        return;
    }
    
    const data = {
        mentor_id: currentMentorId,
        session_date: sessionDate,
        session_time: sessionTime,
        session_type: sessionType,
        topic: topic
    };
    
    const $btn = $('#confirmEnrollBtn');
    $btn.html('<span class="spinner"></span> বুকিং হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/enroll-mentor.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            console.log("✅ Enrollment successful:", response);
            if (response.success) {
                showMessage('success', response.message);
                closeModal();
                $('#enrollForm')[0].reset();
            } else {
                showMessage('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Enrollment error:", error, xhr);
            showMessage('error', 'বুকিং করতে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('নিশ্চিত করুন').prop('disabled', false);
        }
    });
}

// ==================== LOAD MY ENROLLMENTS ====================
function loadMyEnrollments() {
    $.ajax({
        url: '../api/get-my-enrollments.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayEnrollments(response.data);
            } else {
                $('#enrollmentsContainer').html('<div class="empty-state">এনরোলমেন্ট লোড করতে সমস্যা হয়েছে</div>');
            }
        }
    });
}

// ==================== DISPLAY ENROLLMENTS ====================
function displayEnrollments(enrollments) {
    if (!enrollments || enrollments.length === 0) {
        $('#enrollmentsContainer').html(`
            <div class="empty-state">
                <i class="fas fa-calendar-alt"></i>
                <h3>কোনো বুকিং নেই</h3>
                <p>মেন্টর বুক করে শুরু করুন</p>
                <a href="mentor.php" class="btn-view" style="margin-top: 15px;">মেন্টর খুঁজুন</a>
            </div>
        `);
        return;
    }
    
    let html = '';
    enrollments.forEach(enrollment => {
        const statusInfo = getStatusInfo(enrollment.status);
        
        html += `
            <div class="enrollment-card">
                <div class="enrollment-header">
                    <div class="enrollment-mentor">${escapeHtml(enrollment.mentor_name)}</div>
                    <div class="status-badge ${statusInfo.class}">${statusInfo.label}</div>
                </div>
                <div class="enrollment-details">
                    <div class="detail-item">
                        <i class="fas fa-calendar"></i>
                        <span>${enrollment.session_date}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span>${enrollment.session_time}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-video"></i>
                        <span>${getSessionTypeLabel(enrollment.session_type)}</span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-tag"></i>
                        <span>${escapeHtml(enrollment.topic || 'নির্ধারিত হয়নি')}</span>
                    </div>
                </div>
                ${enrollment.status === 'pending' ? `
                <div class="enrollment-actions">
                    <button class="btn-cancel" onclick="cancelEnrollment(${enrollment.id})">
                        <i class="fas fa-times"></i> বাতিল করুন
                    </button>
                </div>
                ` : ''}
                ${enrollment.status === 'completed' && !enrollment.rated ? `
                <div class="enrollment-actions">
                    <button class="btn-rate" onclick="openRatingModal(${enrollment.id}, ${enrollment.mentor_id})">
                        <i class="fas fa-star"></i> রেটিং দিন
                    </button>
                </div>
                ` : ''}
            </div>
        `;
    });
    $('#enrollmentsContainer').html(html);
}

// ==================== CANCEL ENROLLMENT ====================
function cancelEnrollment(enrollmentId) {
    if (confirm('এই বুকিং বাতিল করতে চান?')) {
        $.ajax({
            url: '../api/update-enrollment-status.php',
            type: 'POST',
            data: JSON.stringify({ id: enrollmentId, status: 'cancelled' }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);
                    loadMyEnrollments();
                } else {
                    showMessage('error', response.message);
                }
            }
        });
    }
}

// ==================== OPEN RATING MODAL ====================
function openRatingModal(enrollmentId, mentorId) {
    currentEnrollmentId = enrollmentId;
    currentMentorId = mentorId;
    $('#ratingModal').addClass('active');
    
    // Setup star rating
    $('.rating-stars-input i').off('click').on('click', function() {
        const rating = $(this).data('rating');
        $('.rating-stars-input i').removeClass('active');
        $(this).addClass('active');
        for(let i = 1; i < rating; i++) {
            $(`.rating-stars-input i[data-rating="${i}"]`).addClass('active');
        }
        $('#selectedRating').val(rating);
    });
}

// ==================== SUBMIT RATING ====================
function submitRating() {
    const rating = $('#selectedRating').val();
    const review = $('#ratingReview').val();
    
    if (!rating) {
        showMessage('error', 'দয়া করে রেটিং দিন');
        return;
    }
    
    const data = {
        enrollment_id: currentEnrollmentId,
        mentor_id: currentMentorId,
        rating: rating,
        review: review
    };
    
    $.ajax({
        url: '../api/submit-rating.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
                closeModal();
                loadMyEnrollments();
            } else {
                showMessage('error', response.message);
            }
        }
    });
}

// ==================== HELPER FUNCTIONS ====================
function getTierInfo(tier) {
    const tiers = {
        'silver': { label: 'Silver Mentor', class: 'silver' },
        'gold': { label: 'Gold Mentor', class: 'gold' },
        'platinum': { label: 'Platinum Mentor', class: 'platinum' }
    };
    return tiers[tier] || tiers['silver'];
}

function getRatingStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const hasHalf = rating % 1 >= 0.5;
    
    for (let i = 1; i <= 5; i++) {
        if (i <= fullStars) {
            stars += '<i class="fas fa-star"></i>';
        } else if (i === fullStars + 1 && hasHalf) {
            stars += '<i class="fas fa-star-half-alt"></i>';
        } else {
            stars += '<i class="far fa-star"></i>';
        }
    }
    return stars;
}

function getStatusInfo(status) {
    const statuses = {
        'pending': { label: 'অপেক্ষমান', class: 'pending' },
        'confirmed': { label: 'নিশ্চিত', class: 'confirmed' },
        'completed': { label: 'সম্পন্ন', class: 'completed' },
        'cancelled': { label: 'বাতিল', class: 'cancelled' },
        'rejected': { label: 'প্রত্যাখ্যাত', class: 'rejected' }
    };
    return statuses[status] || statuses['pending'];
}

function getSessionTypeLabel(type) {
    const types = {
        'video': 'ভিডিও কল',
        'audio': 'অডিও কল',
        'chat': 'চ্যাট'
    };
    return types[type] || type;
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
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
    $('.mentor-container').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 5000);
}

function closeModal() {
    $('.modal').removeClass('active');
    currentMentorId = null;
    currentEnrollmentId = null;
    $('#enrollForm')[0]?.reset();
    $('#ratingForm')[0]?.reset();
    $('#selectedRating').val('');
}

// Make functions global
window.loadMentors = loadMentors;
window.openEnrollModal = openEnrollModal;
window.submitEnrollment = submitEnrollment;
window.cancelEnrollment = cancelEnrollment;
window.openRatingModal = openRatingModal;
window.submitRating = submitRating;
window.closeModal = closeModal;