<?php
// dashboard/settings.php
session_start();
require_once '../includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'];

// Get profile image
$conn = new mysqli('localhost', 'root', '', 'mentora_db');
$profile_image = 'default-avatar.png';

if (!$conn->connect_error) {
    $sql = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $profile_image = $row['profile_image'] ?? 'default-avatar.png';
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সেটিংস - মেন্টোরা</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">🧠 মেন্টোরা<span>Mentora</span></div>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="User">
                </div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="mood-tracker.php" class="nav-link"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="assessment.php" class="nav-link"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="journal.php" class="nav-link"><i class="fas fa-book"></i><span>জার্নাল</span></a></li>
                <li class="nav-item"><a href="mentor.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="community.php" class="nav-link"><i class="fas fa-users"></i><span>কমিউনিটি</span></a></li>
                <li class="nav-item"><a href="doctor.php" class="nav-link"><i class="fas fa-user-md"></i><span>ডাক্তার</span></a></li>
                <li class="nav-item"><a href="resources.php" class="nav-link"><i class="fas fa-book-open"></i><span>রিসোর্স</span></a></li>
                <li class="nav-item"><a href="achievements.php" class="nav-link"><i class="fas fa-trophy"></i><span>অ্যাচিভমেন্ট</span></a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i><span>সেটিংস</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-cog"></i> সেটিংস</h1>
            </div>
            
            <div class="settings-container">
                <div class="settings-hero">
                    <h1>⚙️ অ্যাকাউন্ট সেটিংস</h1>
                    <p>আপনার প্রোফাইল, নোটিফিকেশন ও গোপনীয়তা সেটিংস পরিচালনা করুন</p>
                </div>
                
                <div class="settings-grid">
                    <!-- Profile Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-user-circle"></i>
                            <h2>প্রোফাইল সেটিংস</h2>
                        </div>
                        <div class="card-body">
                            <form id="profileForm">
                                <div class="form-group">
                                    <label><i class="fas fa-user"></i> সম্পূর্ণ নাম</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> ইমেইল ঠিকানা</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> ফোন নম্বর</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="form-group">
                                    <label><i class="fas fa-info-circle"></i> নিজের সম্পর্কে</label>
                                    <textarea class="form-control" id="bio" rows="3" placeholder="আপনার সম্পর্কে কিছু লিখুন..."></textarea>
                                </div>
                                <button type="submit" class="btn-save" id="saveProfileBtn">
                                    <i class="fas fa-save"></i> সংরক্ষণ করুন
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-bell"></i>
                            <h2>নোটিফিকেশন সেটিংস</h2>
                        </div>
                        <div class="card-body">
                            <form id="notificationForm">
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>ইমেইল নোটিফিকেশন</h4>
                                        <p>নতুন মেসেজ, আপডেট ইমেইলে পেতে</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="email_notifications">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>পুশ নোটিফিকেশন</h4>
                                        <p>ব্রাউজারে নোটিফিকেশন পেতে</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="push_notifications">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>সেশন রিমাইন্ডার</h4>
                                        <p>মেন্টর সেশনের আগে রিমাইন্ডার পেতে</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="session_reminders">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>সাপ্তাহিক রিপোর্ট</h4>
                                        <p>সাপ্তাহিক মানসিক স্বাস্থ্য রিপোর্ট পেতে</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="weekly_report">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>মার্কেটিং ইমেইল</h4>
                                        <p>অফার, টিপস ও নিউজলেটার পেতে</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="marketing_emails">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <button type="submit" class="btn-save" id="saveNotificationBtn">
                                    <i class="fas fa-save"></i> সংরক্ষণ করুন
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Privacy Settings -->
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-shield-alt"></i>
                            <h2>গোপনীয়তা সেটিংস</h2>
                        </div>
                        <div class="card-body">
                            <form id="privacyForm">
                                <div class="form-group">
                                    <label>প্রোফাইল দৃশ্যমানতা</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="profile_visibility" value="public"> সবার জন্য দৃশ্যমান</label>
                                        <label><input type="radio" name="profile_visibility" value="friends"> শুধু বন্ধুদের জন্য</label>
                                        <label><input type="radio" name="profile_visibility" value="private"> শুধু আমি</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>ডাটা শেয়ারিং</label>
                                    <div class="radio-group">
                                        <label><input type="radio" name="data_sharing" value="full"> সম্পূর্ণ ডাটা শেয়ার করুন</label>
                                        <label><input type="radio" name="data_sharing" value="anonymized"> বেনামী ডাটা শেয়ার করুন</label>
                                        <label><input type="radio" name="data_sharing" value="none"> ডাটা শেয়ার করবেন না</label>
                                    </div>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>অ্যাক্টিভিটি স্ট্যাটাস</h4>
                                        <p>অনলাইন স্ট্যাটাস অন্যদের দেখান</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="show_activity_status">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-switch">
                                    <div class="toggle-info">
                                        <h4>লাস্ট সিন</h4>
                                        <p>শেষবার অনলাইন দেখার সময় দেখান</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" id="show_last_seen">
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <button type="submit" class="btn-save" id="savePrivacyBtn">
                                    <i class="fas fa-save"></i> সংরক্ষণ করুন
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Data Export -->
                    <div class="settings-card">
                        <div class="card-header">
                            <i class="fas fa-database"></i>
                            <h2>ডাটা এক্সপোর্ট</h2>
                        </div>
                        <div class="card-body">
                            <div class="data-export">
                                <h4>জার্নাল এক্সপোর্ট</h4>
                                <p>আপনার সব জার্নাল এন্ট্রি PDF বা JSON ফরম্যাটে ডাউনলোড করুন</p>
                                <button type="button" class="btn-outline" onclick="exportData('journal')">
                                    <i class="fas fa-download"></i> জার্নাল এক্সপোর্ট
                                </button>
                            </div>
                            <div class="data-export">
                                <h4>মুড ডাটা এক্সপোর্ট</h4>
                                <p>আপনার মুড ট্র্যাকিং ডাটা এক্সপোর্ট করুন</p>
                                <button type="button" class="btn-outline" onclick="exportData('mood')">
                                    <i class="fas fa-download"></i> মুড ডাটা এক্সপোর্ট
                                </button>
                            </div>
                            <div class="data-export">
                                <h4>সম্পূর্ণ ডাটা এক্সপোর্ট</h4>
                                <p>আপনার অ্যাকাউন্টের সব ডাটা এক্সপোর্ট করুন</p>
                                <button type="button" class="btn-outline" onclick="exportData('all')">
                                    <i class="fas fa-download"></i> সম্পূর্ণ ডাটা এক্সপোর্ট
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Danger Zone -->
                    <div class="settings-card danger-zone">
                        <div class="card-header">
                            <i class="fas fa-exclamation-triangle"></i>
                            <h2>ডেঞ্জার জোন</h2>
                        </div>
                        <div class="card-body">
                            <div class="warning-text">
                                <i class="fas fa-trash-alt"></i> অ্যাকাউন্ট মুছে ফেলুন - এই কাজটি অপরিবর্তনীয়
                            </div>
                            <button type="button" class="btn-danger" id="openDeleteModalBtn">
                                <i class="fas fa-trash-alt"></i> অ্যাকাউন্ট ডিলিট করুন
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Account Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-exclamation-triangle"></i> অ্যাকাউন্ট ডিলিট</h3>
            <p>আপনি কি নিশ্চিত? আপনার সব ডাটা মুছে যাবে এবং এটি পুনরুদ্ধার করা যাবে না।</p>
            <div class="form-group">
                <label>পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" id="confirm_password" class="form-control" placeholder="আপনার পাসওয়ার্ড দিন">
            </div>
            <div class="modal-buttons">
                <button class="btn-danger" id="confirmDeleteBtn">হ্যাঁ, অ্যাকাউন্ট ডিলিট করুন</button>
                <button class="btn-outline" id="closeModalBtn">বাতিল করুন</button>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/settings.js"></script>
    <script>
        $('#openDeleteModalBtn').click(function() {
            $('#deleteModal').addClass('active');
        });
    </script>
</body>
</html>