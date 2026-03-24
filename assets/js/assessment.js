// assets/js/assessment.js

let assessmentChart = null;
let factorsChart = null;

$(document).ready(function() {
    console.log("✅ Assessment JS loaded");
    
    // Form submission
    $('#assessmentForm').on('submit', function(e) {
        e.preventDefault();
        submitAssessment();
    });
    
    // Download report button
    $('#downloadReport').click(function() {
        downloadPDF();
    });
    
    // Load history
    loadHistory();
});

// Submit assessment
function submitAssessment() {
    // Validate form
    if (!validateForm()) {
        return;
    }
    
    // Show loading
    $('#submitBtn').prop('disabled', true);
    $('#submitBtn').html('<span class="spinner"></span> বিশ্লেষণ করা হচ্ছে...');
    
    // Get form data
    let formData = {
        gender: $('#gender').val(),
        occupation: $('#occupation').val(),
        self_employed: $('#self_employed').val(),
        family_history: $('#family_history').val(),
        days_indoors: $('#days_indoors').val(),
        growing_stress: $('#growing_stress').val(),
        changes_habits: $('#changes_habits').val(),
        mental_health_history: $('#mental_health_history').val(),
        mood_swings: $('#mood_swings').val(),
        coping_struggles: $('#coping_struggles').val(),
        work_interest: $('#work_interest').val(),
        social_weakness: $('#social_weakness').val(),
        mental_health_interview: $('#mental_health_interview').val(),
        care_options: $('#care_options').val()
    };
    
    // Send to API
    $.ajax({
        url: '../api/predict.php',
        type: 'POST',
        data: JSON.stringify(formData),
        contentType: 'application/json',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayResults(response.data);
                scrollToResults();
            } else {
                alert('ত্রুটি: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('API Error:', error);
            alert('সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        },
        complete: function() {
            $('#submitBtn').prop('disabled', false);
            $('#submitBtn').html('<i class="fas fa-brain"></i> আমার মানসিক স্বাস্থ্য যাচাই করুন');
        }
    });
}

// Validate form
function validateForm() {
    let isValid = true;
    $('.form-select').each(function() {
        if (!$(this).val()) {
            $(this).css('border-color', '#ef4444');
            isValid = false;
        } else {
            $(this).css('border-color', '#e5e7eb');
        }
    });
    
    if (!isValid) {
        alert('দয়া করে সব প্রশ্নের উত্তর দিন');
    }
    
    return isValid;
}

// Display results
function displayResults(data) {
    $('#resultsSection').addClass('visible');
    
    // Update gauge
    updateGauge(data.risk_percentage, data.risk_level);
    
    // Update pie chart
    updatePieChart(data.treatment_probability, data.no_treatment_probability);
    
    // Update factors chart
    updateFactorsChart(data.top_factors);
    
    // Update recommendations
    updateRecommendations(data);
    
    // Save to history (already saved in backend)
}

// Update gauge meter
function updateGauge(percentage, level) {
    const gauge = document.querySelector('.gauge');
    if (gauge) {
        const degrees = (percentage / 100) * 360;
        gauge.style.background = `conic-gradient(
            #10b981 0deg,
            #f59e0b ${Math.min(degrees, 180)}deg,
            #ef4444 ${Math.min(degrees, 360)}deg
        )`;
    }
    
    $('#gaugePercentage').text(percentage + '%');
    
    let levelText = '', levelClass = '';
    if (percentage < 30) {
        levelText = 'কম ঝুঁকি';
        levelClass = 'low';
    } else if (percentage < 60) {
        levelText = 'মাঝারি ঝুঁকি';
        levelClass = 'moderate';
    } else {
        levelText = 'উচ্চ ঝুঁকি';
        levelClass = 'high';
    }
    
    $('#riskLevel').text(levelText).removeClass('low moderate high').addClass(levelClass);
}

// Update pie chart
function updatePieChart(treatment, noTreatment) {
    const ctx = document.getElementById('probabilityChart').getContext('2d');
    
    if (assessmentChart) {
        assessmentChart.destroy();
    }
    
    assessmentChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['চিকিৎসা প্রয়োজন', 'চিকিৎসা প্রয়োজন নেই'],
            datasets: [{
                data: [treatment, noTreatment],
                backgroundColor: ['#ef4444', '#10b981'],
                borderWidth: 0,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: { size: 12 }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

// Update factors chart
function updateFactorsChart(factors) {
    const ctx = document.getElementById('factorsChart').getContext('2d');
    
    if (factorsChart) {
        factorsChart.destroy();
    }
    
    const labels = factors.map(f => getBengaliName(f.name));
    const values = factors.map(f => f.importance * 100);
    
    factorsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'প্রভাবের মাত্রা (%)',
                data: values,
                backgroundColor: '#6366f1',
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });
    
    // Also update list view
    let html = '';
    factors.forEach(f => {
        html += `
            <div class="factor-item">
                <span class="factor-name">${getBengaliName(f.name)}</span>
                <div class="factor-bar">
                    <div class="factor-fill" style="width: ${f.importance * 100}%"></div>
                </div>
                <span class="factor-value">${(f.importance * 100).toFixed(1)}%</span>
            </div>
        `;
    });
    $('#factorsList').html(html);
}

// Update recommendations
function updateRecommendations(data) {
    let html = '';
    let cardClass = '';
    let icon = '';
    
    if (data.risk_level === 'High') {
        cardClass = 'high';
        icon = '🚨';
        html = `
            <div class="recommendation-card ${cardClass}">
                <div class="recommendation-title">
                    <span>${icon}</span>
                    জরুরি! পেশাদার সাহায্য প্রয়োজন
                </div>
                <p class="recommendation-text">
                    আপনার মানসিক স্বাস্থ্যের অবস্থা উদ্বেগজনক। দেরি না করে নিচের যেকোনো একটি পদক্ষেপ নিন:
                </p>
                <ul class="tips-list">
                    <li><i class="fas fa-phone-alt"></i> হেল্পলাইনে কল করুন: ০১৯৭৭-৮৫৫০৫৫</li>
                    <li><i class="fas fa-user-md"></i> নিকটস্থ মনোরোগ বিশেষজ্ঞের সাথে যোগাযোগ করুন</li>
                    <li><i class="fas fa-users"></i> পরিবারের সদস্যদের জানান</li>
                    <li><i class="fas fa-heart"></i> একা থাকবেন না, কাছের মানুষদের সাথে থাকুন</li>
                </ul>
                <div class="helpline">
                    <i class="fas fa-phone-alt"></i> জাতীয় মানসিক স্বাস্থ্য হটলাইন<br>
                    <span class="helpline-number">০১৯৭৭-৮৫৫০৫৫</span>
                </div>
            </div>
        `;
    } else if (data.risk_level === 'Moderate') {
        cardClass = 'moderate';
        icon = '⚠️';
        html = `
            <div class="recommendation-card ${cardClass}">
                <div class="recommendation-title">
                    <span>${icon}</span>
                    কিছু বিষয়ে সতর্কতা প্রয়োজন
                </div>
                <p class="recommendation-text">
                    আপনি মাঝারি ঝুঁকিতে আছেন। এখনই সঠিক পদক্ষেপ নিন:
                </p>
                <ul class="tips-list">
                    <li><i class="fas fa-comments"></i> কাউন্সেলরের সাথে কথা বলুন</li>
                    <li><i class="fas fa-spa"></i> প্রতিদিন ১৫ মিনিট মেডিটেশন করুন</li>
                    <li><i class="fas fa-walking"></i> নিয়মিত হাঁটাহাঁটি করুন</li>
                    <li><i class="fas fa-journal-whills"></i> জার্নাল লিখুন</li>
                    <li><i class="fas fa-users"></i> সাপোর্ট গ্রুপে যোগ দিন</li>
                </ul>
            </div>
        `;
    } else {
        cardClass = 'low';
        icon = '🌟';
        html = `
            <div class="recommendation-card ${cardClass}">
                <div class="recommendation-title">
                    <span>${icon}</span>
                    আপনি ভালো আছেন!
                </div>
                <p class="recommendation-text">
                    আপনার মানসিক স্বাস্থ্য ভালো অবস্থায় আছে। এই ধারা বজায় রাখুন:
                </p>
                <ul class="tips-list">
                    <li><i class="fas fa-heart"></i> প্রতিদিন ১৫ মিনিট মেডিটেশন চালিয়ে যান</li>
                    <li><i class="fas fa-dumbbell"></i> নিয়মিত ব্যায়াম করুন</li>
                    <li><i class="fas fa-users"></i> বন্ধু-বান্ধবের সাথে সময় কাটান</li>
                    <li><i class="fas fa-moon"></i> পর্যাপ্ত ঘুম নিশ্চিত করুন</li>
                    <li><i class="fas fa-carrot"></i> স্বাস্থ্যকর খাবার খান</li>
                </ul>
            </div>
        `;
    }
    
    $('#recommendations').html(html);
}

// Load history
function loadHistory() {
    $.ajax({
        url: '../api/get-history.php',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                displayHistory(response.data);
            }
        }
    });
}

// Display history table
function displayHistory(history) {
    let html = '';
    if (history.length === 0) {
        html = '<tr><td colspan="5" style="text-align: center;">কোনো পূর্বের অ্যাসেসমেন্ট নেই</td></tr>';
    } else {
        history.forEach(item => {
            let riskClass = '';
            if (item.prediction_band === 'Low Risk') riskClass = 'low';
            else if (item.prediction_band === 'Moderate Risk') riskClass = 'moderate';
            else riskClass = 'high';
            
            html += `
                <tr>
                    <td>${item.created_at}</td>
                    <td><span class="risk-badge ${riskClass}">${item.prediction_band}</span></td>
                    <td>${item.risk_percentage}%</td>
                    <td>${item.prediction_label === 'Yes' ? 'চিকিৎসা প্রয়োজন' : 'চিকিৎসা প্রয়োজন নেই'}</td>
                    <td><button class="btn-download" onclick="viewReport(${item.id})" style="padding: 5px 12px;"><i class="fas fa-eye"></i> দেখুন</button></td>
                </tr>
            `;
        });
    }
    $('#historyBody').html(html);
}

// View specific report
function viewReport(id) {
    $.ajax({
        url: '../api/get-report.php',
        type: 'POST',
        data: JSON.stringify({ id: id }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                displayResults(response.data);
                scrollToResults();
                showMessage('success', 'পূর্বের রিপোর্ট লোড করা হয়েছে');
            }
        }
    });
}

// Download PDF report
function downloadPDF() {
    // Get current results data from DOM
    const riskLevel = $('#riskLevel').text();
    const riskPercentage = $('#gaugePercentage').text();
    
    // You can implement PDF generation here
    alert('PDF রিপোর্ট ডাউনলোড করা হবে। এই ফিচারটি শীঘ্রই আসছে!');
}

// Helper functions
function getBengaliName(englishName) {
    const names = {
        'family_history': 'পারিবারিক ইতিহাস',
        'mood_swings': 'মেজাজের ওঠানামা',
        'coping_struggles': 'মোকাবিলায় সমস্যা',
        'growing_stress': 'বর্ধমান চাপ',
        'work_interest': 'কাজে আগ্রহ',
        'social_weakness': 'সামাজিক দুর্বলতা',
        'days_indoors': 'গৃহে অবস্থান',
        'mental_health_history': 'মানসিক স্বাস্থ্যের ইতিহাস',
        'changes_habits': 'অভ্যাস পরিবর্তন',
        'care_options': 'চিকিৎসা সুবিধা'
    };
    return names[englishName] || englishName;
}

function scrollToResults() {
    $('html, body').animate({
        scrollTop: $('#resultsSection').offset().top - 100
    }, 500);
}

function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}" style="margin-bottom: 20px; padding: 12px; border-radius: 12px;">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
            <button onclick="$(this).parent().remove()" style="margin-left: auto; background: none; border: none; font-size: 16px; cursor: pointer;">&times;</button>
        </div>
    `;
    
    $('#resultsSection').prepend(alertHtml);
    
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 5000);
}