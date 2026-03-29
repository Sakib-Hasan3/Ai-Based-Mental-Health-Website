<?php
// dashboard/mentor-details.php
session_start();
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get mentor ID from URL
$mentor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($mentor_id <= 0) {
    header('Location: mentor.php');
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

// Get user profile image
$profile_image = 'default-avatar.svg';
$user_sql = "SELECT profile_image FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_row = $user_result->fetch_assoc()) {
    $profile_image = $user_row['profile_image'] ?? 'default-avatar.svg';
}
$user_stmt->close();

// Get mentor details
$sql = "SELECT 
            m.*,
            u.full_name,
            u.email,
            u.phone,
            u.profile_image as user_profile_image
        FROM mentors m
        JOIN users u ON m.user_id = u.id
        WHERE m.id = ? AND m.is_verified = 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mentor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: mentor.php?error=not_found');
    exit();
}

$mentor = $result->fetch_assoc();

// Get reviews for this mentor
$review_sql = "SELECT 
                    r.*,
                    u.full_name,
                    u.profile_image
                FROM mentor_reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.mentor_id = ?
                ORDER BY r.created_at DESC
                LIMIT 10";

$review_stmt = $conn->prepare($review_sql);
$review_stmt->bind_param("i", $mentor_id);
$review_stmt->execute();
$reviews_result = $review_stmt->get_result();

$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}

// Check if user has already booked this mentor
$check_booking_sql = "SELECT id, status FROM mentor_enrollments 
                      WHERE mentor_id = ? AND user_id = ? 
                      ORDER BY id DESC LIMIT 1";
$check_stmt = $conn->prepare($check_booking_sql);
$check_stmt->bind_param("ii", $mentor_id, $user_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();
$existing_booking = $check_result->fetch_assoc();

$conn->close();

// Helper function for tier badge
function getTierBadge($tier) {
    $badges = [
        'silver' => ['class' => 'silver', 'icon' => 'fa-star', 'text' => 'Silver Mentor'],
        'gold' => ['class' => 'gold', 'icon' => 'fa-star gold', 'text' => 'Gold Mentor'],
        'platinum' => ['class' => 'platinum', 'icon' => 'fa-crown', 'text' => 'Platinum Mentor']
    ];
    return $badges[$tier] ?? $badges['silver'];
}

function getRatingStars($rating) {
    $stars = '';
    $fullStars = floor($rating);
    $hasHalf = ($rating - $fullStars) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $fullStars) {
            $stars .= '<i class="fas fa-star"></i>';
        } elseif ($i == $fullStars + 1 && $hasHalf) {
            $stars .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $stars .= '<i class="far fa-star"></i>';
        }
    }
    return $stars;
}

$tierInfo = getTierBadge($mentor['mentor_tier']);
$ratingStars = getRatingStars($mentor['rating']);
$expertise_areas = explode(',', $mentor['expertise_areas'] ?? '');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($mentor['full_name']); ?> - মেন্টর প্রোফাইল | মেন্টোরা</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gold: #fbbf24;
            --silver: #94a3b8;
            --platinum: #a5f3fc;
        }
        
        body {
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
            min-height: 100vh;
        }
        
        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 30px 25px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .user-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid white;
            overflow: hidden;
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
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            border-radius: 12px;
            gap: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .nav-link.active {
            background: white;
            color: #4f46e5;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .nav-link i {
            width: 24px;
            font-size: 18px;
        }
        
        /* Main Content */
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
        
        /* Back Button */
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-600);
            text-decoration: none;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary);
        }
        
        /* Mentor Detail Container */
        .mentor-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Mentor Profile Card */
        .mentor-profile-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0) 70%);
            animation: rotate 30s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            gap: 35px;
            position: relative;
            z-index: 1;
            flex-wrap: wrap;
        }
        
        .mentor-avatar-large {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .mentor-avatar-large img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info {
            color: white;
        }
        
        .profile-info h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        
        .profile-info .specialty {
            font-size: 16px;
            opacity: 0.9;
            margin-bottom: 12px;
        }
        
        .profile-badges {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .tier-badge {
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .tier-badge.silver {
            background: linear-gradient(135deg, #94a3b8, #64748b);
            color: white;
        }
        
        .tier-badge.gold {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        .tier-badge.platinum {
            background: linear-gradient(135deg, #a5f3fc, #06b6d4);
            color: #155e75;
        }
        
        .rating-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        /* Profile Body */
        .profile-body {
            padding: 30px;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--gray-800);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-title i {
            color: var(--primary);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--gray-50);
            border-radius: 16px;
        }
        
        .info-item i {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 18px;
        }
        
        .info-item-content {
            flex: 1;
        }
        
        .info-item-label {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .info-item-value {
            font-size: 15px;
            font-weight: 600;
            color: var(--gray-800);
        }
        
        .expertise-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .expertise-tag {
            background: var(--gray-100);
            padding: 6px 15px;
            border-radius: 30px;
            font-size: 13px;
            color: var(--gray-700);
        }
        
        /* Booking Card */
        .booking-card {
            background: white;
            border-radius: 30px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: sticky;
            top: 100px;
        }
        
        .price {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .price span {
            font-size: 16px;
            font-weight: normal;
            color: var(--gray-500);
        }
        
        .btn-book {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-book.disabled {
            background: var(--gray-400);
            cursor: not-allowed;
        }
        
        .btn-book.disabled:hover {
            transform: none;
            box-shadow: none;
        }
        
        /* Reviews Section */
        .reviews-section {
            background: white;
            border-radius: 30px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .review-card {
            padding: 20px;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .review-card:last-child {
            border-bottom: none;
        }
        
        .review-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 12px;
        }
        
        .reviewer-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gray-200);
            overflow: hidden;
        }
        
        .reviewer-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .reviewer-info h4 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 3px;
        }
        
        .review-date {
            font-size: 12px;
            color: var(--gray-500);
        }
        
        .review-rating {
            color: #fbbf24;
            margin-bottom: 10px;
        }
        
        .review-text {
            color: var(--gray-600);
            line-height: 1.5;
        }
        
        .empty-reviews {
            text-align: center;
            padding: 40px;
            color: var(--gray-500);
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 24px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .modal-content h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        
        .modal-content .form-group {
            margin-bottom: 20px;
        }
        
        .modal-content label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .modal-content input,
        .modal-content select,
        .modal-content textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 14px;
        }
        
        /* Alert */
        .alert {
            padding: 12px 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        /* Spinner */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
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
            .profile-header-content {
                flex-direction: column;
                text-align: center;
            }
            .profile-badges {
                justify-content: center;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="/mental%20health/dashboard/index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mood-tracker.php" class="nav-link"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/assessment.php" class="nav-link"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/journal.php" class="nav-link"><i class="fas fa-book"></i><span>জার্নাল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mentor.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/community.php" class="nav-link"><i class="fas fa-users"></i><span>কমিউনিটি</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/doctor.php" class="nav-link"><i class="fas fa-user-md"></i><span>ডাক্তার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/resources.php" class="nav-link"><i class="fas fa-book-open"></i><span>রিসোর্স</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/achievements.php" class="nav-link active"><i class="fas fa-trophy"></i><span>অ্যাচিভমেন্ট</span></a></li>
                <li class="nav-item"><a href="/mental%20health/auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-chalkboard-teacher"></i> মেন্টর প্রোফাইল</h1>
            </div>
            
            <div class="mentor-detail-container">
                <!-- Back Link -->
                <a href="mentor.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> সব মেন্টর দেখুন
                </a>
                
                <!-- Mentor Profile Card -->
                <div class="mentor-profile-card">
                    <div class="profile-header">
                        <div class="profile-header-content">
                            <div class="mentor-avatar-large">
                                <img src="../assets/images/avatars/<?php echo htmlspecialchars($mentor['user_profile_image'] ?? 'default-avatar.svg'); ?>" alt="<?php echo htmlspecialchars($mentor['full_name']); ?>">
                            </div>
                            <div class="profile-info">
                                <h1><?php echo htmlspecialchars($mentor['full_name']); ?></h1>
                                <div class="specialty"><?php echo htmlspecialchars($mentor['specialty']); ?></div>
                                <div class="profile-badges">
                                    <span class="tier-badge <?php echo $tierInfo['class']; ?>">
                                        <i class="fas <?php echo $tierInfo['icon']; ?>"></i> <?php echo $tierInfo['text']; ?>
                                    </span>
                                    <span class="rating-badge">
                                        <i class="fas fa-star" style="color: #fbbf24;"></i> <?php echo number_format($mentor['rating'], 1); ?> (<?php echo $mentor['total_sessions']; ?> সেশন)
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-body">
                        <div class="info-section">
                            <div class="info-title">
                                <i class="fas fa-user-circle"></i>
                                পরিচিতি
                            </div>
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-briefcase"></i>
                                    <div class="info-item-content">
                                        <div class="info-item-label">অভিজ্ঞতা</div>
                                        <div class="info-item-value"><?php echo $mentor['experience_years']; ?> বছর</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-graduation-cap"></i>
                                    <div class="info-item-content">
                                        <div class="info-item-label">যোগ্যতা</div>
                                        <div class="info-item-value"><?php echo nl2br(htmlspecialchars($mentor['qualification'] ?? 'যোগ্যতা দেখানো হয়নি')); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-language"></i>
                                    <div class="info-item-content">
                                        <div class="info-item-label">ভাষা</div>
                                        <div class="info-item-value"><?php echo htmlspecialchars($mentor['languages'] ?? 'বাংলা, ইংরেজি'); ?></div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-clock"></i>
                                    <div class="info-item-content">
                                        <div class="info-item-label">সাপ্তাহিক সময়সূচি</div>
                                        <div class="info-item-value"><?php echo htmlspecialchars($mentor['availability'] ?? 'যোগাযোগ করুন'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-section">
                            <div class="info-title">
                                <i class="fas fa-quote-left"></i>
                                নিজের সম্পর্কে
                            </div>
                            <p style="line-height: 1.6; color: var(--gray-600);"><?php echo nl2br(htmlspecialchars($mentor['bio'] ?? 'কোনো বায়ো যোগ করা হয়নি')); ?></p>
                        </div>
                        
                        <div class="info-section">
                            <div class="info-title">
                                <i class="fas fa-star"></i>
                                বিশেষ দক্ষতা
                            </div>
                            <div class="expertise-tags">
                                <?php foreach ($expertise_areas as $area): ?>
                                    <?php $area = trim($area); if(!empty($area)): ?>
                                    <span class="expertise-tag"><?php echo htmlspecialchars($area); ?></span>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Card -->
                <div class="booking-card">
                    <div class="price">
                        ৳<?php echo number_format($mentor['hourly_rate'], 0); ?> <span>/সেশন</span>
                    </div>
                    <div class="rating-stars" style="margin-bottom: 15px;">
                        <?php echo $ratingStars; ?>
                        <span style="margin-left: 8px;"><?php echo number_format($mentor['rating'], 1); ?> / 5</span>
                    </div>
                    <?php if ($existing_booking && $existing_booking['status'] == 'pending'): ?>
                        <button class="btn-book disabled" disabled>
                            <i class="fas fa-clock"></i> বুকিং পেন্ডিং
                        </button>
                        <p style="font-size: 12px; color: var(--gray-500); margin-top: 10px;">
                            আপনার বুকিংটি অনুমোদনের অপেক্ষায় আছে
                        </p>
                    <?php elseif ($existing_booking && $existing_booking['status'] == 'confirmed'): ?>
                        <button class="btn-book disabled" disabled>
                            <i class="fas fa-check-circle"></i> সেশন নিশ্চিত হয়েছে
                        </button>
                    <?php else: ?>
                        <button class="btn-book" onclick="openBookingModal()">
                            <i class="fas fa-calendar-plus"></i> সেশন বুক করুন
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Reviews Section -->
                <div class="reviews-section">
                    <div class="reviews-header">
                        <div class="info-title">
                            <i class="fas fa-comments"></i>
                            ক্লায়েন্ট রিভিউ (<?php echo count($reviews); ?>)
                        </div>
                    </div>
                    
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer-avatar">
                                        <img src="../assets/images/avatars/<?php echo htmlspecialchars($review['profile_image'] ?? 'default-avatar.svg'); ?>" alt="<?php echo htmlspecialchars($review['full_name']); ?>">
                                    </div>
                                    <div class="reviewer-info">
                                        <h4><?php echo htmlspecialchars($review['full_name']); ?></h4>
                                        <div class="review-date"><?php echo date('d M Y', strtotime($review['created_at'])); ?></div>
                                    </div>
                                </div>
                                <div class="review-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <div class="review-text">
                                    <?php echo nl2br(htmlspecialchars($review['review'] ?? 'কোনো মন্তব্য নেই')); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-reviews">
                            <i class="fas fa-comment-slash" style="font-size: 48px; opacity: 0.5;"></i>
                            <p style="margin-top: 15px;">এখনো কোনো রিভিউ নেই</p>
                            <p style="font-size: 14px;">প্রথম রিভিউ দিতে আপনিই পারেন!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Modal -->
    <div id="bookingModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-calendar-plus"></i> সেশন বুক করুন</h3>
            <form id="bookingForm">
                <div class="form-group">
                    <label><i class="fas fa-calendar"></i> তারিখ *</label>
                    <input type="date" id="sessionDate" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> সময় *</label>
                    <input type="time" id="sessionTime" required>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-video"></i> সেশন টাইপ</label>
                    <select id="sessionType">
                        <option value="video">ভিডিও কল</option>
                        <option value="audio">অডিও কল</option>
                        <option value="chat">চ্যাট</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> আলোচনার বিষয় (ঐচ্ছিক)</label>
                    <input type="text" id="sessionTopic" placeholder="আপনি কী বিষয়ে আলোচনা করতে চান?">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-book" id="confirmBookBtn">নিশ্চিত করুন</button>
                    <button type="button" class="btn-book" onclick="closeModal()" style="background: var(--gray-500);">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        let mentorId = <?php echo $mentor_id; ?>;
        
        $(document).ready(function() {
            console.log("✅ Mentor details page loaded");
            
            $('#bookingForm').on('submit', function(e) {
                e.preventDefault();
                submitBooking();
            });
            
            // Close modal on background click
            $(window).click(function(e) {
                if ($(e.target).hasClass('modal')) {
                    closeModal();
                }
            });
        });
        
        function openBookingModal() {
            $('#bookingModal').addClass('active');
        }
        
        function closeModal() {
            $('#bookingModal').removeClass('active');
            $('#bookingForm')[0].reset();
        }
        
        function submitBooking() {
            const sessionDate = $('#sessionDate').val();
            const sessionTime = $('#sessionTime').val();
            
            if (!sessionDate || !sessionTime) {
                alert('দয়া করে সেশনের তারিখ ও সময় নির্বাচন করুন');
                return;
            }
            
            const data = {
                mentor_id: mentorId,
                session_date: sessionDate,
                session_time: sessionTime,
                session_type: $('#sessionType').val(),
                topic: $('#sessionTopic').val()
            };
            
            const $btn = $('#confirmBookBtn');
            $btn.html('<span class="spinner"></span> বুকিং হচ্ছে...').prop('disabled', true);
            
            $.ajax({
                url: '../api/enroll-mentor.php',
                type: 'POST',
                data: JSON.stringify(data),
                contentType: 'application/json',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message);
                        closeModal();
                        location.reload();
                    } else {
                        alert('❌ ' + response.message);
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    alert('সার্ভারে সমস্যা হয়েছে। আবার চেষ্টা করুন।');
                },
                complete: function() {
                    $btn.html('নিশ্চিত করুন').prop('disabled', false);
                }
            });
        }
    </script>
</body>
</html>