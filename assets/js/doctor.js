// assets/js/doctor.js

$(document).ready(function() {
    console.log("✅ Doctor page loaded");
    
    // Load all doctors initially
    loadDoctors('');
    
    // Search button click
    $('#searchDoctors').click(function() {
        const division = $('#divisionSelect').val();
        loadDoctors(division);
    });
    
    // Enter key on dropdown
    $('#divisionSelect').on('keypress', function(e) {
        if (e.which === 13) {
            const division = $(this).val();
            loadDoctors(division);
        }
    });
});

// ==================== LOAD DOCTORS ====================
function loadDoctors(division) {
    let url = '../api/get-doctors-by-division.php';
    if (division) {
        url += '?division=' + encodeURIComponent(division);
    }
    
    $('#doctorsGrid').html(`
        <div class="loading">
            <div class="spinner"></div>
            <p style="margin-top: 15px; color: var(--gray-500);">ডাক্তার লোড হচ্ছে...</p>
        </div>
    `);
    
    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayDoctors(response.data);
                if (division) {
                    showMessage('success', `${response.count} জন ডাক্তার পাওয়া গেছে`);
                }
            } else {
                $('#doctorsGrid').html(`
                    <div class="empty-state">
                        <i class="fas fa-user-md"></i>
                        <h3>কোনো ডাক্তার পাওয়া যায়নি</h3>
                        <p>অন্য বিভাগ নির্বাচন করুন</p>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error Status:", status);
            console.error("Error:", error);
            console.error("Response Status Code:", xhr.status);
            console.error("Response Text:", xhr.responseText);
            
            let errorMsg = 'সার্ভারে সমস্যা হয়েছে';
            if (xhr.status === 401) {
                errorMsg = 'আপনার সেশন শেষ হয়েছে। পুনরায় লগইন করুন।';
            } else if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    // Not JSON, use default message
                }
            }
            
            $('#doctorsGrid').html(`
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>${errorMsg}</h3>
                    <p>পুনরায় চেষ্টা করুন</p>
                </div>
            `);
        }
    });
}

// ==================== DISPLAY DOCTORS ====================
function displayDoctors(doctors) {
    if (!doctors || doctors.length === 0) {
        $('#doctorsGrid').html(`
            <div class="empty-state">
                <i class="fas fa-user-md"></i>
                <h3>কোনো ডাক্তার পাওয়া যায়নি</h3>
                <p>অন্য বিভাগ নির্বাচন করুন</p>
            </div>
        `);
        return;
    }
    
    let html = '';
    doctors.forEach(doctor => {
        const avatarHtml = doctor.profile_image && doctor.profile_image !== 'default-doctor.png' 
            ? `<img src="../assets/images/avatars/${doctor.profile_image}" alt="${escapeHtml(doctor.name)}">`
            : `<i class="fas fa-user-md"></i>`;
        
        html += `
            <div class="doctor-card" onclick="visitDoctorWebsite('${escapeHtml(doctor.website_url)}')">
                <div class="doctor-card-header">
                    <div class="doctor-avatar">
                        ${avatarHtml}
                    </div>
                    <h3 class="doctor-name">${escapeHtml(doctor.name)}</h3>
                    <div class="doctor-specialty">${escapeHtml(doctor.specialization)}</div>
                    <div class="doctor-hospital">${escapeHtml(doctor.hospital_name || 'হাসপাতাল তথ্য নেই')}</div>
                </div>
                <div class="doctor-card-body">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>${escapeHtml(doctor.division)}${doctor.district ? ' > ' + escapeHtml(doctor.district) : ''}</span>
                    </div>
                    ${doctor.experience_years ? `
                    <div class="info-item">
                        <i class="fas fa-briefcase"></i>
                        <span>${doctor.experience_years} বছর অভিজ্ঞতা</span>
                    </div>
                    ` : ''}
                    ${doctor.phone ? `
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span>${escapeHtml(doctor.phone)}</span>
                    </div>
                    ` : ''}
                </div>
                <div class="doctor-footer">
                    <button class="btn-visit" onclick="event.stopPropagation(); visitDoctorWebsite('${escapeHtml(doctor.website_url)}')">
                        <i class="fas fa-external-link-alt"></i> ওয়েবসাইট দেখুন
                    </button>
                </div>
            </div>
        `;
    });
    $('#doctorsGrid').html(html);
}

// ==================== VISIT DOCTOR WEBSITE ====================
function visitDoctorWebsite(url) {
    if (url) {
        window.open(url, '_blank');
    } else {
        showMessage('error', 'ওয়েবসাইট লিংক উপলব্ধ নেই');
    }
}

// ==================== SHOW MESSAGE ====================
function showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-error';
    const icon = type === 'success' ? '✅' : '❌';
    const alertHtml = `
        <div class="alert ${alertClass}">
            ${icon} ${message}
            <button onclick="$(this).parent().remove()" style="float: right; background: none; border: none; cursor: pointer;">✖</button>
        </div>
    `;
    $('.doctor-container').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').fadeOut('slow', function() { $(this).remove(); });
    }, 3000);
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