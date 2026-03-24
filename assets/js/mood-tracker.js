// assets/js/mood-tracker.js

let moodChart = null;
let currentPeriod = 'week';

$(document).ready(function() {
    console.log("✅ Mood Tracker loaded");
    
    // Verify elements exist
    console.log("🔍 Element check:");
    console.log("  - Mood cards:", $('.mood-card').length);
    console.log("  - Date input:", $('#moodDate').length);
    console.log("  - Save button:", $('#saveMoodBtn').length);
    console.log("  - Checkboxes:", $('#exercise').length + $('#meditation').length + $('#socialContact').length);
    
    // Set today's date
    const today = new Date().toISOString().split('T')[0];
    $('#moodDate').val(today);
    console.log("📅 Today's date set to:", today);
    
    // Load existing data for today
    loadMoodData(today);
    
    // Load chart
    loadMoodChart(currentPeriod);
    
    // Load history
    loadMoodHistory();
    
    // Date change handler
    $('#moodDate').on('change', function() {
        const date = $(this).val();
        loadMoodData(date);
    });
    
    // Today button
    $('.btn-today').click(function() {
        const today = new Date().toISOString().split('T')[0];
        $('#moodDate').val(today);
        loadMoodData(today);
    });
    
    // Mood selection
    $('.mood-card').click(function() {
        console.log("🎯 Mood card clicked");
        $('.mood-card').removeClass('selected');
        $(this).addClass('selected');
        
        const moodScore = $(this).data('score');
        const moodLabel = $(this).data('label');
        const moodEmoji = $(this).data('emoji');
        
        console.log("✅ Selected mood:", {score: moodScore, label: moodLabel, emoji: moodEmoji});
        
        $('#selectedMoodScore').val(moodScore);
        $('#selectedMoodLabel').val(moodLabel);
        $('#selectedMoodEmoji').val(moodEmoji);
    });
    
    // Period selector
    $('.period-btn').click(function() {
        $('.period-btn').removeClass('active');
        $(this).addClass('active');
        currentPeriod = $(this).data('period');
        loadMoodChart(currentPeriod);
    });
    
    // Save mood
    $('#saveMoodBtn').click(function(e) {
        e.preventDefault();
        console.log("💾 Save button clicked!");
        saveMood();
        return false;
    });
    
    // Enter key in notes
    $('#moodNotes').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            saveMood();
        }
    });
});

function loadMoodData(date) {
    $.ajax({
        url: '../api/get-mood-history.php',
        type: 'GET',
        data: { date: date, single: true },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                // Fill form with existing data
                const data = response.data;
                
                // Select mood
                $('.mood-card').removeClass('selected');
                $(`.mood-card[data-score="${data.mood_score}"]`).addClass('selected');
                
                $('#selectedMoodScore').val(data.mood_score);
                $('#selectedMoodLabel').val(data.mood_label);
                $('#selectedMoodEmoji').val(data.mood_emoji);
                
                // Fill notes
                $('#moodNotes').val(data.notes || '');
                
                // Fill sleep hours
                $('#sleepHours').val(data.sleep_hours || '');
                
                // Fill activities
                $('#exercise').prop('checked', data.exercise == 1);
                $('#meditation').prop('checked', data.meditation == 1);
                $('#socialContact').prop('checked', data.social_contact == 1);
            } else {
                // Reset form for new day
                $('.mood-card').removeClass('selected');
                $('#selectedMoodScore').val('');
                $('#selectedMoodLabel').val('');
                $('#selectedMoodEmoji').val('');
                $('#moodNotes').val('');
                $('#sleepHours').val('');
                $('#exercise').prop('checked', false);
                $('#meditation').prop('checked', false);
                $('#socialContact').prop('checked', false);
            }
        },
        error: function() {
            console.log('Error loading mood data');
        }
    });
}

function saveMood() {
    // Get selected mood
    const selectedMood = $('.mood-card.selected');
    if (!selectedMood.length) {
        alert('দয়া করে একটি মুড নির্বাচন করুন');
        return;
    }
    
    const moodData = {
        mood_score: selectedMood.data('score'),
        mood_label: selectedMood.data('label'),
        mood_emoji: selectedMood.data('emoji'),
        notes: $('#moodNotes').val(),
        sleep_hours: $('#sleepHours').val() || null,
        exercise: $('#exercise').is(':checked') ? 1 : 0,
        meditation: $('#meditation').is(':checked') ? 1 : 0,
        social_contact: $('#socialContact').is(':checked') ? 1 : 0,
        entry_date: $('#moodDate').val()
    };
    
    console.log("📤 Saving mood:", moodData);
    
    // Show loading
    const $btn = $('#saveMoodBtn');
    $btn.html('<span class="spinner"></span> সংরক্ষণ করা হচ্ছে...').prop('disabled', true);
    
    $.ajax({
        url: '../api/save-mood.php',
        type: 'POST',
        data: JSON.stringify(moodData),
        contentType: 'application/json',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log("✅ Save response:", response);
            if (response.success) {
                showMessage('success', response.message);
                // Reload chart and history
                loadMoodChart(currentPeriod);
                loadMoodHistory();
            } else {
                showMessage('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("❌ Save error:", status, error);
            console.error("Response text:", xhr.responseText);
            showMessage('error', 'সার্ভারে সমস্যা হয়েছে: ' + error);
        },
        complete: function() {
            $btn.html('<i class="fas fa-save"></i> মুড সংরক্ষণ করুন').prop('disabled', false);
        }
    });
}

function loadMoodChart(period) {
    $.ajax({
        url: '../api/get-mood-history.php',
        type: 'GET',
        data: { period: period },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                renderChart(response.data);
                updateSummaryStats(response.data);
            }
        },
        error: function() {
            console.log('Error loading chart data');
        }
    });
}

function renderChart(data) {
    const ctx = document.getElementById('moodChart').getContext('2d');
    
    if (moodChart) {
        moodChart.destroy();
    }
    
    const labels = data.map(item => {
        const date = new Date(item.entry_date);
        return date.toLocaleDateString('bn-BD', { day: 'numeric', month: 'short' });
    });
    
    const scores = data.map(item => item.mood_score);
    
    // Calculate average line
    const avgScore = scores.reduce((a, b) => a + b, 0) / scores.length;
    
    moodChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'মুড স্কোর',
                    data: scores,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                },
                {
                    label: 'গড় মুড',
                    data: Array(scores.length).fill(avgScore),
                    borderColor: '#f59e0b',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `মুড: ${context.raw}/10`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    title: {
                        display: true,
                        text: 'মুড স্কোর (1-10)'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function updateSummaryStats(data) {
    if (!data.length) {
        $('#avgMood').text('--');
        $('#bestMood').text('--');
        $('#worstMood').text('--');
        $('#totalEntries').text('0');
        return;
    }
    
    const scores = data.map(item => item.mood_score);
    const avgMood = (scores.reduce((a, b) => a + b, 0) / scores.length).toFixed(1);
    const bestMood = Math.max(...scores);
    const worstMood = Math.min(...scores);
    const totalEntries = data.length;
    
    $('#avgMood').text(avgMood);
    $('#bestMood').text(bestMood);
    $('#worstMood').text(worstMood);
    $('#totalEntries').text(totalEntries);
}

function loadMoodHistory() {
    $.ajax({
        url: '../api/get-mood-history.php',
        type: 'GET',
        data: { limit: 30 },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.data) {
                renderHistoryTable(response.data);
            }
        },
        error: function() {
            console.log('Error loading history');
        }
    });
}

function renderHistoryTable(data) {
    let html = '';
    
    if (data.length === 0) {
        html = '<tr><td colspan="6" style="text-align: center;">কোনো মুড এন্ট্রি নেই</td></tr>';
    } else {
        data.forEach(item => {
            let moodClass = '';
            if (item.mood_score >= 7) moodClass = 'high';
            else if (item.mood_score >= 4) moodClass = 'medium';
            else moodClass = 'low';
            
            // Format date
            const date = new Date(item.entry_date);
            const formattedDate = date.toLocaleDateString('bn-BD', { 
                day: 'numeric', 
                month: 'short', 
                year: 'numeric' 
            });
            
            // Activity icons
            let activityIcons = '';
            if (item.exercise) activityIcons += '<i class="fas fa-dumbbell active" title="ব্যায়াম"></i> ';
            if (item.meditation) activityIcons += '<i class="fas fa-spa active" title="মেডিটেশন"></i> ';
            if (item.social_contact) activityIcons += '<i class="fas fa-users active" title="সামাজিক যোগাযোগ"></i> ';
            if (!activityIcons) activityIcons = '<i class="fas fa-minus-circle" style="color: #9ca3af;"></i>';
            
            html += `
                <tr>
                    <td>${formattedDate}</td>
                    <td><span class="mood-badge ${moodClass}">${item.mood_emoji} ${item.mood_score}/10</span></td>
                    <td>${item.mood_label}</td>
                    <td>${item.sleep_hours ? item.sleep_hours + ' ঘন্টা' : '-'}</td>
                    <td class="activity-icons">${activityIcons}</td>
                    <td><button class="btn-delete" onclick="deleteMoodEntry(${item.id})"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
        });
    }
    
    $('#historyBody').html(html);
}

function deleteMoodEntry(id) {
    if (confirm('এই মুড এন্ট্রি ডিলিট করতে চান?')) {
        $.ajax({
            url: '../api/save-mood.php',
            type: 'DELETE',
            data: JSON.stringify({ id: id }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    showMessage('success', response.message);
                    // Reload data
                    const currentDate = $('#moodDate').val();
                    loadMoodData(currentDate);
                    loadMoodChart(currentPeriod);
                    loadMoodHistory();
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

function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
            <button onclick="$(this).parent().remove()" style="margin-left: auto; background: none; border: none; font-size: 16px; cursor: pointer;">&times;</button>
        </div>
    `;
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('.mood-container').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}