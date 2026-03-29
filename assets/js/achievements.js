// assets/js/achievements.js

let currentCategory = 'all';

$(document).ready(function() {
    console.log("✅ Achievements page loaded");
    
    // Load achievements
    loadAchievements();
    loadRecentAchievements();
    
    // Category filter
    $('.category-btn').click(function() {
        $('.category-btn').removeClass('active');
        $(this).addClass('active');
        currentCategory = $(this).data('category');
        loadAchievements();
    });
});

// ==================== LOAD ACHIEVEMENTS ====================
function loadAchievements() {
    let url = '../api/achievements/get-achievements.php';
    if (currentCategory !== 'all') {
        url += '?category=' + encodeURIComponent(currentCategory);
    }
    
    $('#achievementsGrid').html(`
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px;">অ্যাচিভমেন্ট লোড হচ্ছে...</p>
        </div>
    `);
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayAchievements(response.data);
                updateStats(response.stats);
            } else {
                $('#achievementsGrid').html(`
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>অ্যাচিভমেন্ট লোড করতে সমস্যা হয়েছে</h3>
                    </div>
                `);
            }
        },
        error: function() {
            $('#achievementsGrid').html(`
                <div class="empty-state">
                    <i class="fas fa-wifi"></i>
                    <h3>সার্ভারে সমস্যা হয়েছে</h3>
                </div>
            `);
        }
    });
}

// ==================== DISPLAY ACHIEVEMENTS ====================
function displayAchievements(achievements) {
    if (!achievements || achievements.length === 0) {
        $('#achievementsGrid').html(`
            <div class="empty-state">
                <i class="fas fa-trophy"></i>
                <h3>কোনো অ্যাচিভমেন্ট পাওয়া যায়নি</h3>
            </div>
        `);
        return;
    }
    
    let html = '';
    achievements.forEach(achievement => {
        const progressPercent = (achievement.progress_current / achievement.progress_target) * 100;
        let statusHtml = '';
        
        if (achievement.is_claimed) {
            statusHtml = `<div class="claimed-badge"><i class="fas fa-check-circle"></i> ক্লেইম করা হয়েছে</div>`;
        } else if (achievement.is_completed) {
            statusHtml = `
                <button class="btn-claim" onclick="claimAchievement(${achievement.id})">
                    <i class="fas fa-gift"></i> ক্লেইম করুন (${achievement.points} পয়েন্ট)
                </button>
            `;
        } else {
            statusHtml = `<div class="locked-badge"><i class="fas fa-lock"></i> লক করা আছে</div>`;
        }
        
        let cardClass = '';
        if (achievement.is_claimed) cardClass = 'claimed';
        else if (achievement.is_completed) cardClass = 'completed';
        else cardClass = 'locked';
        
        html += `
            <div class="achievement-card ${cardClass}">
                <div class="achievement-badge" style="background: linear-gradient(135deg, ${achievement.badge_color}, ${adjustColor(achievement.badge_color, -20)})">
                    <i class="fas ${achievement.badge_icon}"></i>
                </div>
                <h3 class="achievement-name">${escapeHtml(achievement.name)}</h3>
                <p class="achievement-description">${escapeHtml(achievement.description)}</p>
                <div class="achievement-points">
                    <i class="fas fa-star"></i> ${achievement.points} পয়েন্ট
                </div>
                <div class="progress-section">
                    <div class="progress-label">
                        <span>প্রগতি</span>
                        <span>${achievement.progress_current}/${achievement.progress_target}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${progressPercent}%"></div>
                    </div>
                </div>
                ${statusHtml}
            </div>
        `;
    });
    $('#achievementsGrid').html(html);
}

// ==================== UPDATE STATS ====================
function updateStats(stats) {
    $('#totalPoints').text(stats.total_points || 0);
    $('#completedCount').text(stats.completed_count || 0);
    $('#claimedCount').text(stats.claimed_count || 0);
    $('#lockedCount').text(stats.locked_count || 0);
}

// ==================== LOAD RECENT ACHIEVEMENTS ====================
function loadRecentAchievements() {
    $.ajax({
        url: '../api/achievements/get-achievements.php?recent=1',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.recent && response.recent.length > 0) {
                displayRecentAchievements(response.recent);
            } else {
                $('#recentList').html('<div class="empty-state" style="padding: 20px;">কোনো রিসেন্ট অ্যাচিভমেন্ট নেই</div>');
            }
        }
    });
}

// ==================== DISPLAY RECENT ACHIEVEMENTS ====================
function displayRecentAchievements(recent) {
    let html = '';
    recent.forEach(item => {
        html += `
            <div class="recent-item">
                <div class="recent-icon" style="background: ${item.badge_color}">
                    <i class="fas ${item.badge_icon}"></i>
                </div>
                <div class="recent-info">
                    <div class="recent-name">${escapeHtml(item.name)}</div>
                    <div class="recent-date">${item.claimed_at_formatted || item.completed_at_formatted || ''}</div>
                </div>
                <div class="recent-points">+${item.points} pts</div>
            </div>
        `;
    });
    $('#recentList').html(html);
}

// ==================== CLAIM ACHIEVEMENT ====================
function claimAchievement(achievementId) {
    $.ajax({
        url: '../api/achievements/claim-achievement.php',
        type: 'POST',
        data: JSON.stringify({ achievement_id: achievementId }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showToast('success', response.message);
                loadAchievements();
                loadRecentAchievements();
            } else {
                showToast('error', response.message);
            }
        },
        error: function() {
            showToast('error', 'ক্লেইম করতে সমস্যা হয়েছে');
        }
    });
}

// ==================== HELPER FUNCTIONS ====================
function adjustColor(color, percent) {
    // Simple color adjustment for gradient
    return color;
}

function showToast(type, message) {
    const toast = $(`
        <div class="toast-message" style="background: ${type === 'success' ? '#10b981' : '#ef4444'}">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            ${message}
        </div>
    `);
    $('body').append(toast);
    setTimeout(() => {
        toast.fadeOut(300, function() { $(this).remove(); });
    }, 3000);
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