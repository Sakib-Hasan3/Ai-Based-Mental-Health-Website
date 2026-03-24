<?php
// dashboard/profile.php
session_start();
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Get user stats
$stats = [];

// Mood entries count
$mood_sql = "SELECT COUNT(*) as count FROM mood_entries WHERE user_id = ?";
$mood_stmt = $conn->prepare($mood_sql);
if ($mood_stmt) {
    $mood_stmt->bind_param("i", $user_id);
    $mood_stmt->execute();
    $mood_result = $mood_stmt->get_result();
    $stats['mood_count'] = $mood_result->fetch_assoc()['count'] ?? 0;
    $mood_stmt->close();
}

// Journal entries count
$journal_sql = "SELECT COUNT(*) as count FROM journal_entries WHERE user_id = ?";
$journal_stmt = $conn->prepare($journal_sql);
if ($journal_stmt) {
    $journal_stmt->bind_param("i", $user_id);
    $journal_stmt->execute();
    $journal_result = $journal_stmt->get_result();
    $stats['journal_count'] = $journal_result->fetch_assoc()['count'] ?? 0;
    $journal_stmt->close();
}

// Sessions count
$session_sql = "SELECT COUNT(*) as count FROM mentor_sessions WHERE user_id = ? AND status = 'completed'";
$session_stmt = $conn->prepare($session_sql);
if ($session_stmt) {
    $session_stmt->bind_param("i", $user_id);
    $session_stmt->execute();
    $session_result = $session_stmt->get_result();
    $stats['session_count'] = $session_result->fetch_assoc()['count'] ?? 0;
    $session_stmt->close();
}

// Achievements count
$achievement_sql = "SELECT COUNT(*) as count FROM achievements WHERE user_id = ?";
$achievement_stmt = $conn->prepare($achievement_sql);
if ($achievement_stmt) {
    $achievement_stmt->bind_param("i", $user_id);
    $achievement_stmt->execute();
    $achievement_result = $achievement_stmt->get_result();
    $stats['achievement_count'] = $achievement_result->fetch_assoc()['count'] ?? 0;
    $achievement_stmt->close();
}

$stmt->close();
$conn->close();

// Default values
$profile_image = $user_data['profile_image'] ?? 'default-avatar.png';
$full_name = $user_data['full_name'] ?? '';
$email = $user_data['email'] ?? '';
$phone = $user_data['phone'] ?? '';
$bio = $user_data['bio'] ?? '';
$date_of_birth = $user_data['date_of_birth'] ?? '';
$gender = $user_data['gender'] ?? '';
$address = $user_data['address'] ?? '';
$city = $user_data['city'] ?? '';
$created_at = isset($user_data['created_at']) ? date('F Y', strtotime($user_data['created_at'])) : date('F Y');

// Calculate wellness score (mood average)
$wellness_score = 85; // Default, you can calculate from mood entries
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>প্রোফাইল - মেন্টোরা</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    
    <style>
        /* Dashboard Styles - Keep existing dashboard styles */
        body {
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
            min-height: 100vh;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 30px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
        }
        
        .logo span {
            font-size: 12px;
            opacity: 0.7;
            display: block;
            margin-top: 5px;
        }
        
        .user-info {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid white;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-email {
            font-size: 12px;
            opacity: 0.8;
            word-break: break-all;
        }
        
        .user-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            margin-top: 10px;
        }
        
        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .nav-item {
            margin: 5px 15px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 18px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            gap: 12px;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: white;
            color: #4f46e5;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        
        .nav-link i {
            width: 24px;
            font-size: 18px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
        }
        
        .top-header {
            background: white;
            border-radius: 24px;
            padding: 20px 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--gray-800);
        }
        
        .page-title i {
            color: var(--primary);
            margin-right: 10px;
        }
        
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            .sidebar .logo span,
            .sidebar .user-info,
            .nav-link span {
                display: none;
            }
            .nav-link {
                justify-content: center;
                padding: 12px;
            }
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    🧠 মেন্টোরা
                    <span>Mentora</span>
                </div>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="User">
                </div>
                <div class="user-name"><?php echo htmlspecialchars($full_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($email); ?></div>
                <div class="user-badge">✨ ফ্রি মেম্বর</div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>ড্যাশবোর্ড</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-user-circle"></i>
                        <span>প্রোফাইল</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="assessment.php" class="nav-link">
                        <i class="fas fa-brain"></i>
                        <span>মানসিক স্বাস্থ্য যাচাই</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../auth/logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>লগআউট</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title">
                    <i class="fas fa-user-circle"></i>
                    আমার প্রোফাইল
                </h1>
                <div class="header-actions">
                    <i class="fas fa-bell" style="font-size: 20px; color: var(--gray-500); cursor: pointer;"></i>
                </div>
            </div>
            
            <div class="profile-container">
                <!-- Hero Banner -->
                <div class="profile-hero">
                    <div class="hero-content">
                        <div class="hero-avatar">
                            <div class="avatar-wrapper">
                                <img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" id="profileAvatar">
                            </div>
                            <div class="avatar-edit" id="editAvatarBtn">
                                <i class="fas fa-camera"></i>
                            </div>
                            <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                        </div>
                        <div class="hero-info">
                            <h1 class="hero-name"><?php echo htmlspecialchars($full_name); ?></h1>
                            <div class="hero-email"><?php echo htmlspecialchars($email); ?></div>
                            <div class="hero-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>সদস্য: <?php echo $created_at; ?></span>
                                </div>
                                <?php if($phone): ?>
                                <div class="meta-item">
                                    <i class="fas fa-phone"></i>
                                    <span><?php echo htmlspecialchars($phone); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($city): ?>
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($city); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-smile"></i></div>
                        <div class="stat-value"><?php echo $stats['mood_count']; ?></div>
                        <div class="stat-label">মুড এন্ট্রি</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
                        <div class="stat-value"><?php echo $stats['journal_count']; ?></div>
                        <div class="stat-label">জার্নাল এন্ট্রি</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-chalkboard-user"></i></div>
                        <div class="stat-value"><?php echo $stats['session_count']; ?></div>
                        <div class="stat-label">মেন্টর সেশন</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                        <div class="stat-value"><?php echo $stats['achievement_count']; ?></div>
                        <div class="stat-label">অ্যাচিভমেন্ট</div>
                    </div>
                </div>
                
                <!-- Wellness Card -->
                <div class="wellness-card">
                    <div class="wellness-info">
                        <h3><i class="fas fa-heartbeat"></i> আপনার ওয়েলনেস স্কোর</h3>
                        <div class="wellness-score"><?php echo $wellness_score; ?>%</div>
                        <div class="wellness-message">🌟 আপনি ভালো করছেন! ধারাবাহিক থাকুন</div>
                    </div>
                    <a href="assessment.php" class="wellness-btn">
                        <i class="fas fa-chart-line"></i> বিস্তারিত দেখুন
                    </a>
                </div>
                
                <!-- Profile Tabs -->
                <div class="profile-tabs">
                    <button class="tab-btn active" data-tab="edit-profile">
                        <i class="fas fa-user-edit"></i> প্রোফাইল সম্পাদনা
                    </button>
                    <button class="tab-btn" data-tab="change-password">
                        <i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন
                    </button>
                    <button class="tab-btn" data-tab="privacy">
                        <i class="fas fa-shield-alt"></i> গোপনীয়তা
                    </button>
                </div>
                
                <!-- Tab 1: Edit Profile -->
                <div class="tab-content active" id="edit-profile">
                    <form class="profile-form" id="profileForm">
                        <div class="form-title">
                            <i class="fas fa-user-edit"></i> ব্যক্তিগত তথ্য
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> সম্পূর্ণ নাম</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" data-original="<?php echo htmlspecialchars($full_name); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-envelope"></i> ইমেইল ঠিকানা</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" data-original="<?php echo htmlspecialchars($email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-phone"></i> ফোন নম্বর</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" data-original="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> জন্ম তারিখ</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo $date_of_birth; ?>" data-original="<?php echo $date_of_birth; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-venus-mars"></i> লিঙ্গ</label>
                                <select class="form-control" id="gender" name="gender" data-original="<?php echo $gender; ?>">
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="male" <?php echo $gender == 'male' ? 'selected' : ''; ?>>পুরুষ</option>
                                    <option value="female" <?php echo $gender == 'female' ? 'selected' : ''; ?>>মহিলা</option>
                                    <option value="other" <?php echo $gender == 'other' ? 'selected' : ''; ?>>অন্যান্য</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-city"></i> শহর</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" data-original="<?php echo htmlspecialchars($city); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-map-marker-alt"></i> ঠিকানা</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($address); ?>" data-original="<?php echo htmlspecialchars($address); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-info-circle"></i> নিজের সম্পর্কে</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" placeholder="আপনার সম্পর্কে কিছু লিখুন..." data-original="<?php echo htmlspecialchars($bio); ?>"><?php echo htmlspecialchars($bio); ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" id="saveProfileBtn">
                                <i class="fas fa-save"></i> সংরক্ষণ করুন
                            </button>
                            <button type="button" class="btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo-alt"></i> রিসেট
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab 2: Change Password -->
                <div class="tab-content" id="change-password">
                    <form class="profile-form" id="passwordForm">
                        <div class="form-title">
                            <i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-lock"></i> বর্তমান পাসওয়ার্ড</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="current_password" name="current_password" placeholder="********" required>
                                <span class="toggle-password"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-key"></i> নতুন পাসওয়ার্ড</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="********" required>
                                <span class="toggle-password"><i class="fas fa-eye-slash"></i></span>
                            </div>
                            <div class="password-strength">
                                <div class="strength-meter">
                                    <div class="strength-meter-fill"></div>
                                </div>
                                <div class="strength-text"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-check-circle"></i> পাসওয়ার্ড নিশ্চিত করুন</label>
                            <div class="password-wrapper">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="********" required>
                                <span class="toggle-password"><i class="fas fa-eye-slash"></i></span>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-primary" id="changePasswordBtn">
                                <i class="fas fa-key"></i> পাসওয়ার্ড পরিবর্তন করুন
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Tab 3: Privacy -->
                <div class="tab-content" id="privacy">
                    <div class="profile-form">
                        <div class="form-title">
                            <i class="fas fa-shield-alt"></i> গোপনীয়তা সেটিংস
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-eye"></i> প্রোফাইল দৃশ্যমানতা</label>
                            <select class="form-control" id="profile_visibility">
                                <option value="public">সকলের জন্য দৃশ্যমান</option>
                                <option value="friends">শুধু বন্ধুদের জন্য</option>
                                <option value="private">শুধু আমি</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> ইমেইল নোটিফিকেশন</label>
                            <div style="display: flex; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" checked> নতুন মেসেজ
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" checked> মেন্টর সেশন রিমাইন্ডার
                                </label>
                                <label style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox"> সাপ্তাহিক রিপোর্ট
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-database"></i> ডাটা এক্সপোর্ট</label>
                            <button type="button" class="btn-secondary" style="margin-top: 10px;">
                                <i class="fas fa-download"></i> আমার ডাটা ডাউনলোড করুন
                            </button>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-trash-alt"></i> অ্যাকাউন্ট মুছে ফেলুন</label>
                            <button type="button" class="btn-secondary" style="background: #fee2e2; color: #dc2626; border-color: #fecaca;">
                                <i class="fas fa-exclamation-triangle"></i> অ্যাকাউন্ট ডিলিট করুন
                            </button>
                            <p class="text-muted" style="font-size: 12px; margin-top: 8px;">⚠️ এই কাজটি অপরিবর্তনীয়। আপনার সব ডাটা মুছে যাবে।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Custom JS -->
    <script src="../assets/js/profile.js"></script>
</body>
</html>