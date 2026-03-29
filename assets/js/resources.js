// assets/js/resources.js

let currentType = 'all';
let currentSearch = '';

$(document).ready(function() {
    console.log("✅ Resources page loaded");
    
    // Load all resources
    loadResources();
    
    // Search button click
    $('#searchBtn').click(function() {
        currentSearch = $('#searchInput').val().trim();
        loadResources();
    });
    
    // Enter key on search
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            currentSearch = $(this).val().trim();
            loadResources();
        }
    });
    
    // Filter buttons
    $('.filter-btn').click(function() {
        $('.filter-btn').removeClass('active');
        $(this).addClass('active');
        currentType = $(this).data('type');
        loadResources();
    });
});

// ==================== LOAD RESOURCES ====================
function loadResources() {
    let url = '../resources/api/get-resources.php?';
    if (currentType !== 'all') {
        url += 'type=' + encodeURIComponent(currentType) + '&';
    }
    if (currentSearch) {
        url += 'search=' + encodeURIComponent(currentSearch);
    }
    
    $('#resourcesGrid').html(`
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: var(--gray-500);">রিসোর্স লোড হচ্ছে...</p>
        </div>
    `);
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayResources(response.data);
                if (currentSearch) {
                    showMessage('success', `${response.count} টি রিসোর্স পাওয়া গেছে`);
                }
            } else {
                $('#resourcesGrid').html(`
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>রিসোর্স লোড করতে সমস্যা হয়েছে</h3>
                        <p>পুনরায় চেষ্টা করুন</p>
                    </div>
                `);
            }
        },
        error: function() {
            $('#resourcesGrid').html(`
                <div class="empty-state">
                    <i class="fas fa-wifi"></i>
                    <h3>সার্ভারে সমস্যা হয়েছে</h3>
                    <p>পুনরায় চেষ্টা করুন</p>
                </div>
            `);
        }
    });
}

// ==================== DISPLAY RESOURCES ====================
function displayResources(resources) {
    if (!resources || resources.length === 0) {
        $('#resourcesGrid').html(`
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>কোনো রিসোর্স পাওয়া যায়নি</h3>
                <p>অন্য ক্যাটাগরি নির্বাচন করুন</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    resources.forEach(resource => {
        html += getResourceCard(resource);
    });
    $('#resourcesGrid').html(html);
}

// ==================== GET RESOURCE CARD BY TYPE ====================
function getResourceCard(resource) {
    const type = resource.resource_type;
    const iconClass = getTypeIconClass(type);
    const iconName = getTypeIconName(type);
    
    let cardHtml = `
        <div class="resource-card" data-id="${resource.id}">
            <div class="resource-card-header">
                <div class="resource-type-icon ${iconClass}">
                    <i class="fas ${iconName}"></i>
                </div>
                <h3 class="resource-title">${escapeHtml(resource.title)}</h3>
                <p class="resource-description">${escapeHtml(resource.description || 'কোনো বিবরণ নেই')}</p>
                <div class="resource-meta">
                    ${resource.author ? `<span><i class="fas fa-user"></i> ${escapeHtml(resource.author)}</span>` : ''}
                    ${resource.duration ? `<span><i class="fas fa-clock"></i> ${resource.duration}</span>` : ''}
                </div>
            </div>
    `;
    
    // Type-specific footer
    switch(type) {
        case 'article':
            cardHtml += getArticleFooter(resource);
            break;
        case 'video':
            cardHtml += getVideoFooter(resource);
            break;
        case 'pdf':
            cardHtml += getPdfFooter(resource);
            break;
        case 'breathing':
            cardHtml += getBreathingFooter(resource);
            break;
        case 'meditation':
            cardHtml += getMeditationFooter(resource);
            break;
        case 'helpline':
            cardHtml += getHelplineFooter(resource);
            break;
        default:
            cardHtml += getDefaultFooter(resource);
    }
    
    cardHtml += `</div>`;
    return cardHtml;
}

function getArticleFooter(resource) {
    return `
        <div class="resource-card-footer">
            <button class="btn-access" onclick="openResource('${escapeHtml(resource.resource_url)}')">
                <i class="fas fa-book-open"></i> পড়ুন
            </button>
        </div>
    `;
}

function getVideoFooter(resource) {
    return `
        <div class="resource-card-footer">
            <button class="btn-access" onclick="openResource('${escapeHtml(resource.resource_url)}')">
                <i class="fas fa-play"></i> দেখুন
            </button>
        </div>
    `;
}

function getPdfFooter(resource) {
    return `
        <div class="resource-card-footer">
            <button class="btn-access" onclick="openResource('${escapeHtml(resource.file_path || resource.resource_url)}')">
                <i class="fas fa-download"></i> ডাউনলোড করুন
            </button>
        </div>
    `;
}

function getBreathingFooter(resource) {
    return `
        <div class="resource-card-footer">
            <button class="btn-access" onclick="openResource('${escapeHtml(resource.resource_url)}')">
                <i class="fas fa-lungs"></i> শুরু করুন
            </button>
        </div>
    `;
}

function getMeditationFooter(resource) {
    return `
        <div class="resource-card-footer">
            <div class="audio-player">
                <i class="fas fa-play-circle" style="font-size: 24px; color: var(--primary); cursor: pointer;" onclick="playAudio('${escapeHtml(resource.audio_url)}', this)"></i>
                <span style="font-size: 13px;">${resource.duration || 'শুনুন'}</span>
            </div>
        </div>
    `;
}

function getHelplineFooter(resource) {
    let numbersHtml = '';
    if (resource.contact_numbers) {
        const numbers = resource.contact_numbers.split(',');
        numbers.forEach(num => {
            numbersHtml += `<a href="tel:${num.trim()}" class="helpline-number"><i class="fas fa-phone-alt"></i> ${num.trim()}</a>`;
        });
    }
    
    return `
        <div class="resource-card-footer">
            <div class="helpline-numbers">
                ${numbersHtml}
                ${resource.contact_info ? `<p style="font-size: 12px; color: var(--gray-500); margin-top: 8px;"><i class="fas fa-info-circle"></i> ${escapeHtml(resource.contact_info)}</p>` : ''}
            </div>
        </div>
    `;
}

function getDefaultFooter(resource) {
    return `
        <div class="resource-card-footer">
            <button class="btn-access" onclick="openResource('${escapeHtml(resource.resource_url)}')">
                <i class="fas fa-external-link-alt"></i> দেখুন
            </button>
        </div>
    `;
}

// ==================== HELPER FUNCTIONS ====================
function getTypeIconClass(type) {
    const classes = {
        'article': 'type-article',
        'video': 'type-video',
        'pdf': 'type-pdf',
        'breathing': 'type-breathing',
        'meditation': 'type-meditation',
        'helpline': 'type-helpline'
    };
    return classes[type] || 'type-article';
}

function getTypeIconName(type) {
    const icons = {
        'article': 'fa-newspaper',
        'video': 'fa-video',
        'pdf': 'fa-file-pdf',
        'breathing': 'fa-lungs',
        'meditation': 'fa-head-side-medical',
        'helpline': 'fa-phone-alt'
    };
    return icons[type] || 'fa-file-alt';
}

function openResource(url) {
    if (url) {
        window.open(url, '_blank');
    } else {
        showMessage('error', 'লিংক উপলব্ধ নেই');
    }
}

function playAudio(url, element) {
    if (!url) {
        showMessage('error', 'অডিও ফাইল উপলব্ধ নেই');
        return;
    }
    
    // Create audio element if not exists
    if (!window.currentAudio) {
        window.currentAudio = new Audio();
    }
    
    // Toggle play/pause
    if (window.currentAudio.src === url && !window.currentAudio.paused) {
        window.currentAudio.pause();
        $(element).removeClass('fa-pause-circle').addClass('fa-play-circle');
    } else {
        window.currentAudio.src = url;
        window.currentAudio.play();
        $(element).removeClass('fa-play-circle').addClass('fa-pause-circle');
        
        window.currentAudio.onended = function() {
            $(element).removeClass('fa-pause-circle').addClass('fa-play-circle');
        };
    }
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
    $('.resources-container').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
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