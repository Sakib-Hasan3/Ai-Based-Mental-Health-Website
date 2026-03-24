// assets/js/journal.js

// Global variables
let currentEditingId = null;
let moodChart = null;

$(document).ready(function() {
    console.log("✅ Journal page loaded");
    
    // Load journals on page load
    loadJournals();
    
    // Mood selector - select mood on click
    $('.mood-option').click(function() {
        $('.mood-option').removeClass('selected');
        $(this).addClass('selected');
        $('#selectedMoodScore').val($(this).data('score'));
        $('#selectedMoodLabel').val($(this).data('label'));
    });
    
    // Save journal form submission
    $('#journalForm').submit(function(e) {
        e.preventDefault();
        saveJournal();
    });
    
    // Search/filter on change
    $('#searchInput, #categoryFilter, #moodFilter, #sortFilter').on('input change', function() {
        loadJournals();
    });
    
    // Modal close button
    $('#closeModalBtn').click(function() {
        closeModal();
    });
    
    // Close modal on background click
    $(window).click(function(e) {
        if ($(e.target).hasClass('modal')) {
            closeModal();
        }
    });
    
    // Escape key to close modal
    $(document).keydown(function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
});

// ==================== SAVE JOURNAL ====================
function saveJournal() {
    const content = $('#journalContent').val().trim();
    if (!content) {
        showMessage('error', 'দয়া করে জার্নাল কন্টেন্ট লিখুন');
        return;
    }
    
    const data = {
        title: $('#journalTitle').val().trim(),
        content: content,
        mood_score: $('#selectedMoodScore').val() || null,
        mood_label: $('#selectedMoodLabel').val() || null,
        category: $('#journalCategory').val(),
        tags: []
    };
    
    const $btn = $('#saveJournalBtn');
    $btn.html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/save-journal.php',
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showMessage('success', response.message);
                // Reset form
                $('#journalForm')[0].reset();
                $('.mood-option').removeClass('selected');
                $('#selectedMoodScore').val('');
                $('#selectedMoodLabel').val('');
                // Reload journals
                loadJournals();
            } else {
                showMessage('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Save error:', error);
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        },
        complete: function() {
            $btn.html('<i class="fas fa-save"></i> জার্নাল সংরক্ষণ করুন').prop('disabled', false);
        }
    });
}

// ==================== LOAD JOURNALS ====================
function loadJournals() {
    const params = new URLSearchParams({
        search: $('#searchInput').val(),
        category: $('#categoryFilter').val(),
        mood: $('#moodFilter').val(),
        sort: $('#sortFilter').val(),
        limit: 50
    });
    
    $('#entriesContainer').html('<div class="empty-state"><i class="fas fa-spinner fa-spin"></i> লোড হচ্ছে...</div>');
    
    $.ajax({
        url: '../api/get-journals.php?' + params.toString(),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayJournals(response.data);
            } else {
                $('#entriesContainer').html('<div class="empty-state">জার্নাল লোড করতে সমস্যা হয়েছে</div>');
            }
        },
        error: function() {
            $('#entriesContainer').html('<div class="empty-state">জার্নাল লোড করতে সমস্যা হয়েছে</div>');
        }
    });
}

// ==================== DISPLAY JOURNALS ====================
function displayJournals(entries) {
    if (!entries || entries.length === 0) {
        $('#entriesContainer').html(`
            <div class="empty-state">
                <i class="fas fa-book-open"></i>
                <h3>এখনো কোনো জার্নাল লেখা হয়নি</h3>
                <p>আজকের অনুভূতি লিখে শুরু করুন</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    entries.forEach(entry => {
        const moodHtml = entry.mood_score ? 
            `<span class="badge badge-mood">${entry.mood_badge.emoji} ${entry.mood_score}/10</span>` : '';
        const categoryInfo = entry.category_info;
        
        html += `
            <div class="entry-card" data-id="${entry.id}">
                <div class="entry-title">${escapeHtml(entry.title) || 'নামহীন এন্ট্রি'}</div>
                <div class="entry-date"><i class="far fa-calendar-alt"></i> ${entry.created_at_formatted}</div>
                <div class="entry-preview">${escapeHtml(entry.preview)}</div>
                <div class="entry-footer">
                    <div class="entry-badges">
                        ${moodHtml}
                        <span class="badge badge-category" style="background: ${categoryInfo.color}">
                            <i class="fas ${categoryInfo.icon}"></i> ${categoryInfo.name}
                        </span>
                    </div>
                    <div class="entry-actions">
                        <button class="action-btn" onclick="viewEntry(${entry.id})" title="দেখুন">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn" onclick="editEntry(${entry.id})" title="সম্পাদনা করুন">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="deleteEntry(${entry.id})" title="ডিলিট করুন">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });
    $('#entriesContainer').html(html);
}

// ==================== VIEW ENTRY DETAILS ====================
function viewEntry(id) {
    $.ajax({
        url: '../api/get-journal-details.php?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const entry = response.data;
                $('#modalTitle').text(entry.title || 'নামহীন এন্ট্রি');
                $('#modalDate').html(`<i class="far fa-calendar-alt"></i> ${entry.created_at_formatted}`);
                $('#modalContent').html(`<p style="white-space: pre-wrap;">${escapeHtml(entry.content)}</p>`);
                
                let moodHtml = '';
                if (entry.mood_score) {
                    moodHtml = `
                        <div class="badge badge-mood" style="display: inline-block;">
                            ${entry.mood_badge.emoji} মুড: ${entry.mood_score}/10 (${entry.mood_label})
                        </div>
                    `;
                }
                $('#modalMood').html(moodHtml);
                
                const categoryInfo = entry.category_info;
                $('#modalCategory').html(`
                    <div class="badge" style="background: ${categoryInfo.color}; display: inline-block;">
                        <i class="fas ${categoryInfo.icon}"></i> ${categoryInfo.name}
                    </div>
                `);
                
                currentEditingId = id;
                $('#entryModal').addClass('active');
            } else {
                showMessage('error', 'এন্ট্রি পাওয়া যায়নি');
            }
        },
        error: function() {
            showMessage('error', 'এন্ট্রি লোড করতে সমস্যা হয়েছে');
        }
    });
}

// ==================== EDIT ENTRY ====================
function editEntry(id) {
    // Redirect to edit page (or open edit modal)
    window.location.href = 'edit-journal.php?id=' + id;
}

// ==================== DELETE ENTRY ====================
function deleteEntry(id) {
    if (confirm('এই জার্নাল এন্ট্রি ডিলিট করতে চান? এটি পুনরুদ্ধার করা যাবে না।')) {
        $.ajax({
            url: '../api/delete-journal.php',
            type: 'POST',
            data: JSON.stringify({ id: id }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);
                    loadJournals();
                } else {
                    showMessage('error', response.message);
                }
            },
            error: function() {
                showMessage('error', 'ডিলিট করতে ব্যর্থ হয়েছে');
            }
        });
    }
}

// ==================== CLOSE MODAL ====================
function closeModal() {
    $('#entryModal').removeClass('active');
    currentEditingId = null;
}

// ==================== SHOW MESSAGE ====================
function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? '✅' : '❌';
    const alertHtml = `
        <div class="alert ${alertClass}">
            ${icon} ${message}
            <button onclick="$(this).parent().remove()" style="float: right; background: none; border: none; font-size: 16px; cursor: pointer;">✖</button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.journal-container').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

// ==================== ESCAPE HTML ====================
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

// ==================== EXPORT FUNCTIONS (for global use) ====================
window.viewEntry = viewEntry;
window.editEntry = editEntry;
window.deleteEntry = deleteEntry;
window.closeModal = closeModal;