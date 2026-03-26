// assets/js/community.js

let currentFilter = 'recent';
let currentCategory = '';

$(document).ready(function() {
    console.log("✅ Community page loaded");
    
    // Load posts on page load
    loadPosts();
    
    // Filter buttons
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        
        if ($(this).data('filter') === 'category') {
            currentCategory = $(this).data('category');
            currentFilter = '';
        } else {
            currentFilter = $(this).data('filter');
            currentCategory = '';
        }
        loadPosts();
    });
    
    // Create post
    $('#createPostBtn').click(function() {
        createPost();
    });
    
    // Enter key in post content
    $('#postContent').on('keypress', function(e) {
        if (e.which === 13 && e.ctrlKey) {
            createPost();
        }
    });
});

// ==================== CREATE POST ====================
function createPost() {
    const content = $('#postContent').val().trim();
    if (!content) {
        showMessage('error', 'দয়া করে কিছু লিখুন');
        return;
    }
    
    if (content.length < 5) {
        showMessage('error', 'কমপক্ষে ৫ অক্ষর লিখুন');
        return;
    }
    
    const data = {
        content: content,
        category: $('#postCategory').val(),
        is_anonymous: $('#anonymousPost').is(':checked') ? 1 : 0
    };
    
    const $btn = $('#createPostBtn');
    $btn.html('<span class="spinner"></span> পোস্ট হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/community/create-post.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
                $('#postContent').val('');
                loadPosts();
            } else {
                showMessage('error', response.message);
            }
        },
        error: function() {
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে');
        },
        complete: function() {
            $btn.html('<i class="fas fa-paper-plane"></i> পোস্ট করুন').prop('disabled', false);
        }
    });
}

// ==================== LOAD POSTS ====================
function loadPosts() {
    const params = new URLSearchParams({
        filter: currentFilter,
        category: currentCategory
    });
    
    $('#postsFeed').html('<div class="empty-state"><i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...</div>');
    
    $.ajax({
        url: '../api/community/get-posts.php?' + params.toString(),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayPosts(response.data);
            } else {
                $('#postsFeed').html('<div class="empty-state">পোস্ট লোড করতে সমস্যা হয়েছে</div>');
            }
        },
        error: function() {
            $('#postsFeed').html('<div class="empty-state">পোস্ট লোড করতে সমস্যা হয়েছে</div>');
        }
    });
}

// ==================== DISPLAY POSTS ====================
function displayPosts(posts) {
    if (!posts || posts.length === 0) {
        $('#postsFeed').html(`
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>কোনো পোস্ট নেই</h3>
                <p>প্রথম পোস্টটি করুন!</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    posts.forEach(post => {
        const authorName = post.is_anonymous ? 'বেনামী' : post.author_name;
        const authorAvatar = post.is_anonymous ? 'default-avatar.png' : (post.author_avatar || 'default-avatar.png');
        const categoryClass = getCategoryClass(post.category);
        const date = new Date(post.created_at).toLocaleDateString('bn-BD', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        const hasReacted = post.user_reacted;
        
        html += `
            <div class="post-card" data-post-id="${post.id}">
                <div class="post-header">
                    <div class="post-author">
                        <div class="post-author-avatar">
                            <img src="../assets/images/avatars/${authorAvatar}" alt="${authorName}">
                        </div>
                        <div class="author-info">
                            <h4>${escapeHtml(authorName)}</h4>
                            <div class="post-date">${date}</div>
                        </div>
                    </div>
                    <div class="category-badge ${categoryClass}">${getCategoryName(post.category)}</div>
                </div>
                <div class="post-content">${escapeHtml(post.content)}</div>
                <div class="post-footer">
                    <div class="post-actions">
                        <button class="action-btn support-btn ${hasReacted ? 'active' : ''}" onclick="reactToPost(${post.id})">
                            <i class="fas fa-heart"></i> <span class="support-count">${post.support_count}</span>
                        </button>
                        <button class="action-btn" onclick="toggleComments(${post.id})">
                            <i class="fas fa-comment"></i> <span class="comment-count">${post.comment_count}</span>
                        </button>
                        <button class="action-btn report-btn" onclick="reportPost(${post.id})">
                            <i class="fas fa-flag"></i> রিপোর্ট
                        </button>
                    </div>
                </div>
                <div class="comments-section" id="comments-${post.id}">
                    <div class="comment-input">
                        <input type="text" id="comment-input-${post.id}" placeholder="আপনার মন্তব্য লিখুন...">
                        <button onclick="addComment(${post.id})">মন্তব্য</button>
                    </div>
                    <div class="comments-list" id="comments-list-${post.id}">
                        ${post.comments ? renderComments(post.comments) : '<div class="comment-item">কোনো মন্তব্য নেই</div>'}
                    </div>
                </div>
            </div>
        `;
    });
    $('#postsFeed').html(html);
}

// ==================== REACT TO POST ====================
function reactToPost(postId) {
    $.ajax({
        url: '../api/community/react-post.php',
        type: 'POST',
        data: JSON.stringify({ post_id: postId }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const $card = $(`.post-card[data-post-id="${postId}"]`);
                $card.find('.support-count').text(response.support_count);
                const $btn = $card.find('.support-btn');
                if (response.reacted) {
                    $btn.addClass('active');
                } else {
                    $btn.removeClass('active');
                }
            }
        }
    });
}

// ==================== TOGGLE COMMENTS ====================
function toggleComments(postId) {
    $(`#comments-${postId}`).toggleClass('active');
    if ($(`#comments-${postId}`).hasClass('active')) {
        loadComments(postId);
    }
}

// ==================== LOAD COMMENTS ====================
function loadComments(postId) {
    $.ajax({
        url: '../api/community/get-comments.php',
        type: 'GET',
        data: { post_id: postId },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderCommentsList(postId, response.data);
            }
        }
    });
}

// ==================== ADD COMMENT ====================
function addComment(postId) {
    const comment = $(`#comment-input-${postId}`).val().trim();
    if (!comment) {
        alert('দয়া করে মন্তব্য লিখুন');
        return;
    }
    
    $.ajax({
        url: '../api/community/comment-post.php',
        type: 'POST',
        data: JSON.stringify({ post_id: postId, comment: comment }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $(`#comment-input-${postId}`).val('');
                loadComments(postId);
                // Update comment count
                $(`.post-card[data-post-id="${postId}"] .comment-count`).text(response.comment_count);
            } else {
                alert(response.message);
            }
        }
    });
}

// ==================== REPORT POST ====================
function reportPost(postId) {
    const reason = prompt('রিপোর্ট করার কারণ লিখুন (ঐচ্ছিক):');
    if (reason === null) return;
    
    $.ajax({
        url: '../api/community/report-post.php',
        type: 'POST',
        data: JSON.stringify({ post_id: postId, reason: reason || 'স্প্যাম বা অনুপযুক্ত কন্টেন্ট' }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', 'রিপোর্ট পাঠানো হয়েছে। প্রশাসক পর্যালোচনা করবেন।');
            } else {
                showMessage('error', response.message);
            }
        }
    });
}

// ==================== HELPER FUNCTIONS ====================
function getCategoryClass(category) {
    const classes = {
        'stress': 'stress',
        'anxiety': 'anxiety',
        'study': 'study',
        'family': 'family',
        'relationship': 'relationship',
        'motivation': 'motivation',
        'sleep': 'sleep',
        'general': 'general'
    };
    return classes[category] || 'general';
}

function getCategoryName(category) {
    const names = {
        'stress': 'স্ট্রেস',
        'anxiety': 'উদ্বেগ',
        'study': 'পড়াশোনা',
        'family': 'পরিবার',
        'relationship': 'সম্পর্ক',
        'motivation': 'প্রেরণা',
        'sleep': 'ঘুম',
        'general': 'সাধারণ'
    };
    return names[category] || category;
}

function renderComments(comments) {
    if (!comments.length) return '<div class="comment-item">কোনো মন্তব্য নেই</div>';
    return comments.map(c => `
        <div class="comment-item">
            <div class="comment-avatar">
                <img src="../assets/images/avatars/${c.author_avatar || 'default-avatar.png'}">
            </div>
            <div class="comment-content">
                <div class="comment-author">${escapeHtml(c.is_anonymous ? 'বেনামী' : c.author_name)}</div>
                <div class="comment-text">${escapeHtml(c.comment)}</div>
                <div class="comment-date">${new Date(c.created_at).toLocaleDateString('bn-BD')}</div>
            </div>
        </div>
    `).join('');
}

function renderCommentsList(postId, comments) {
    if (!comments.length) {
        $(`#comments-list-${postId}`).html('<div class="comment-item">কোনো মন্তব্য নেই</div>');
        return;
    }
    
    let html = '';
    comments.forEach(c => {
        html += `
            <div class="comment-item">
                <div class="comment-avatar">
                    <img src="../assets/images/avatars/${c.author_avatar || 'default-avatar.png'}">
                </div>
                <div class="comment-content">
                    <div class="comment-author">${escapeHtml(c.is_anonymous ? 'বেনামী' : c.author_name)}</div>
                    <div class="comment-text">${escapeHtml(c.comment)}</div>
                    <div class="comment-date">${new Date(c.created_at).toLocaleDateString('bn-BD')}</div>
                </div>
            </div>
        `;
    });
    $(`#comments-list-${postId}`).html(html);
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
    $('.community-container').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 5000);
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