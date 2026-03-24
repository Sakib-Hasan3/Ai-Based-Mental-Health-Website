<?php
// dashboard/index.php - Mental Health Assessment Integrated
session_start();
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get user info from session
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? 'user@example.com';
$user_type = $_SESSION['user_type'] ?? 'user';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ড্যাশবোর্ড - মেন্টোরা</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ==================== Mental Health Assessment Styles ==================== */
        .mental-health-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .form-group {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 5px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: white;
            font-weight: 500;
            font-size: 14px;
            padding: 0 10px;
        }

        .form-group label i {
            margin-right: 5px;
        }

        .form-group .required {
            color: #ff6b6b;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 12px;
            background: rgba(255,255,255,0.95);
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.5);
            transform: translateY(-2px);
        }

        .btn-assess {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-assess:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .btn-assess:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .result-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .result-card h5 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
            border-left: 4px solid #667eea;
            padding-left: 10px;
        }

        .gauge-container {
            text-align: center;
            margin: 15px 0;
            position: relative;
        }

        .risk-level {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .risk-level.high { color: #dc3545; }
        .risk-level.moderate { color: #ffc107; }
        .risk-level.low { color: #28a745; }

        .tips-list {
            margin-top: 15px;
            padding-left: 0;
            list-style: none;
        }

        .tips-list li {
            margin: 8px 0;
            color: #555;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tips-list li i {
            color: #28a745;
        }

        .helpline {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 12px;
            border-radius: 10px;
            margin-top: 15px;
            text-align: center;
            border-left: 4px solid #dc3545;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 25px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-save, .btn-download, .btn-reset {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-save { background: #28a745; color: white; }
        .btn-download { background: #17a2b8; color: white; }
        .btn-reset { background: #6c757d; color: white; }

        .btn-save:hover, .btn-download:hover, .btn-reset:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .section-subtitle {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            margin-top: 5px;
        }

        .loading-spinner {
            text-align: center;
            margin-top: 20px;
            color: white;
        }

        .toast-message {
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            color: white;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .form-select-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        /* Existing dashboard styles remain */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }

        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .logo span {
            font-size: 12px;
            display: block;
            color: #666;
        }

        .user-info {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #667eea;
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
            color: #666;
            margin-bottom: 10px;
        }

        .user-badge {
            display: inline-block;
            padding: 4px 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px;
            font-size: 12px;
        }

        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s;
            gap: 12px;
        }

        .nav-link i {
            width: 20px;
            font-size: 18px;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .badge {
            margin-left: auto;
            background: #ff6b6b;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }

        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .page-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .page-title i {
            color: #667eea;
            margin-right: 10px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid #e0e0e0;
            border-radius: 25px;
            width: 250px;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .header-icon {
            position: relative;
            cursor: pointer;
        }

        .header-icon i {
            font-size: 20px;
            color: #666;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff6b6b;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-icon.blue { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .stat-icon.green { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .stat-icon.orange { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .stat-icon.pink { background: rgba(245, 87, 108, 0.1); color: #f5576c; }

        .stat-details {
            flex: 1;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .stat-change {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }

        .stat-change.positive { color: #28a745; }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .section-title i {
            margin-right: 8px;
            color: #667eea;
        }

        .view-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }

        .mood-grid {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .mood-option {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .mood-option i {
            font-size: 30px;
            margin-bottom: 8px;
            display: block;
        }

        .mood-option span {
            font-size: 12px;
            color: #666;
        }

        .mood-option:hover, .mood-option.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-5px);
        }

        .mood-option:hover span, .mood-option.active span {
            color: white;
        }

        .charts-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .chart-filter {
            padding: 5px 10px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 12px;
        }

        .chart-container {
            height: 250px;
        }

        .sessions-list, .mentors-grid, .journal-grid, .activity-feed {
            margin-bottom: 30px;
        }

        .sessions-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .session-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .session-time {
            min-width: 100px;
        }

        .session-time .date {
            font-weight: 600;
            font-size: 14px;
        }

        .session-time .time {
            font-size: 12px;
            color: #666;
        }

        .session-info {
            flex: 1;
        }

        .session-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .session-mentor {
            font-size: 12px;
            color: #666;
        }

        .session-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
        }

        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }

        .mentors-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .mentor-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .mentor-avatar {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            border-radius: 50%;
            overflow: hidden;
        }

        .mentor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mentor-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .mentor-specialty {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .mentor-rating {
            margin-bottom: 15px;
        }

        .mentor-rating i {
            color: #ffc107;
            font-size: 12px;
        }

        .btn-book {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            cursor: pointer;
            width: 100%;
        }

        .journal-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .journal-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .journal-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .journal-title {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .journal-preview {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .journal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .journal-mood {
            font-size: 12px;
            color: #28a745;
        }

        .activity-feed {
            background: white;
            border-radius: 15px;
            padding: 20px;
        }

        .activities-container {
            max-height: 300px;
            overflow-y: auto;
        }

        .activity-item {
            display: flex;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .activity-icon.chat { background: rgba(102, 126, 234, 0.1); color: #667eea; }
        .activity-icon.mentor { background: rgba(40, 167, 69, 0.1); color: #28a745; }
        .activity-icon.journal { background: rgba(255, 193, 7, 0.1); color: #ffc107; }

        .activity-content {
            flex: 1;
        }

        .activity-text {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 11px;
            color: #999;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .quick-action {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .quick-action i {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 8px;
            display: block;
        }

        .quick-action span {
            font-size: 12px;
            color: #555;
        }

        .quick-action:hover {
            background: linear-gradient(135deg, #667eea, #764ba2);
            transform: translateY(-3px);
        }

        .quick-action:hover i,
        .quick-action:hover span {
            color: white;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .charts-row {
                grid-template-columns: 1fr;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- ==================== Sidebar ==================== -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    মেন্টোরা
                    <span>Mentora</span>
                </div>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/avatars/default-avatar.png" alt="User">
                </div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                <div class="user-badge">
                    <?php echo $user_type === 'premium' ? 'প্রিমিয়াম' : 'ফ্রি'; ?>
                </div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link active">
                        <i class="fas fa-home"></i>
                        <span>ড্যাশবোর্ড</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>প্রোফাইল</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="chatbot.php" class="nav-link">
                        <i class="fas fa-robot"></i>
                        <span>মনের বন্ধু</span>
                        <span class="badge">new</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="mood-tracker.php" class="nav-link">
                        <i class="fas fa-smile"></i>
                        <span>মুড ট্র্যাকার</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="journal.php" class="nav-link">
                        <i class="fas fa-book"></i>
                        <span>জার্নাল</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="mentor.php" class="nav-link">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>মেন্টর</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="doctor.php" class="nav-link">
                        <i class="fas fa-user-md"></i>
                        <span>ডাক্তার</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="community.php" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>কমিউনিটি</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="resources.php" class="nav-link">
                        <i class="fas fa-video"></i>
                        <span>রিসোর্স</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="achievements.php" class="nav-link">
                        <i class="fas fa-trophy"></i>
                        <span>অ্যাচিভমেন্ট</span>
                        <span class="badge">5</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="assessment.php" class="nav-link">
                        <i class="fas fa-brain"></i>
                        <span>মানসিক যাচাই</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>সেটিংস</span>
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
        
        <!-- ==================== Main Content ==================== -->
        <div class="main-content">
            <!-- Top Header -->
            <div class="top-header">
                <h1 class="page-title">
                    <i class="fas fa-home"></i>
                    ড্যাশবোর্ড
                </h1>
                
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="খুঁজুন...">
                        <i class="fas fa-search"></i>
                    </div>
                    
                    <div class="header-icon" id="menuToggle">
                        <i class="fas fa-bars"></i>
                    </div>
                    
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    
                    <div class="header-icon">
                        <i class="fas fa-envelope"></i>
                        <span class="notification-badge">2</span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-smile"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">আজকের মুড</div>
                        <div class="stat-value" id="moodScore">7.5</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            গতকাল থেকে ভালো
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">সেশন বাকি</div>
                        <div class="stat-value" id="sessionCount">12</div>
                        <div class="stat-change">
                            <i class="fas fa-clock"></i>
                            ৩ টা পেন্ডিং
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-pen"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">জার্নাল এন্ট্রি</div>
                        <div class="stat-value" id="journalCount">8</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            এই সপ্তাহে +৩
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pink">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-details">
                        <div class="stat-label">অ্যাচিভমেন্ট</div>
                        <div class="stat-value" id="achievementCount">15</div>
                        <div class="stat-change">
                            <i class="fas fa-star"></i>
                            ২ টা নতুন
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- ==================== MENTAL HEALTH ASSESSMENT SECTION (NEW) ==================== -->
            <div class="mental-health-card">
                <div class="section-header" style="margin-bottom: 15px;">
                    <h3 class="section-title" style="color: white;">
                        <i class="fas fa-brain"></i>
                        মানসিক স্বাস্থ্য মূল্যায়ন
                        <span style="background: rgba(255,255,255,0.2); padding: 2px 8px; border-radius: 20px; font-size: 12px; margin-left: 10px;">AI Powered</span>
                    </h3>
                    <p class="section-subtitle">AI-চালিত প্রযুক্তি আপনার মানসিক অবস্থা বিশ্লেষণ করবে (71.65% নির্ভুলতা)</p>
                </div>
                
                <div id="assessmentForm">
                    <div class="form-grid">
                        <!-- Gender -->
                        <div class="form-group">
                            <label><i class="fas fa-venus-mars"></i> লিঙ্গ <span class="required">*</span></label>
                            <select id="gender" class="form-control">
                                <option value="">বাছাই করুন</option>
                                <option value="Male">পুরুষ</option>
                                <option value="Female">নারী</option>
                            </select>
                        </div>
                        
                        <!-- Occupation -->
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> পেশা <span class="required">*</span></label>
                            <select id="occupation" class="form-control">
                                <option value="">বাছাই করুন</option>
                                <option value="Corporate">কর্পোরেট</option>
                                <option value="Business">ব্যবসা</option>
                                <option value="Student">ছাত্র/ছাত্রী</option>
                                <option value="Housewife">গৃহিণী</option>
                                <option value="Others">অন্যান্য</option>
                            </select>
                        </div>
                        
                        <!-- Self Employed -->
                        <div class="form-group">
                            <label><i class="fas fa-user-tie"></i> স্ব-নিযুক্ত</label>
                            <select id="self_employed" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                            </select>
                        </div>
                        
                        <!-- Family History -->
                        <div class="form-group">
                            <label><i class="fas fa-users"></i> পারিবারিক ইতিহাস</label>
                            <select id="family_history" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                            </select>
                        </div>
                        
                        <!-- Days Indoors -->
                        <div class="form-group">
                            <label><i class="fas fa-home"></i> গৃহে অবস্থান</label>
                            <select id="days_indoors" class="form-control">
                                <option value="1-14 days">১-১৪ দিন</option>
                                <option value="15-30 days">১৫-৩০ দিন</option>
                                <option value="31-60 days">৩১-৬০ দিন</option>
                                <option value="More than 2 months">২ মাসের বেশি</option>
                                <option value="Go out Every day">প্রতিদিন বাইরে যাই</option>
                            </select>
                        </div>
                        
                        <!-- Growing Stress -->
                        <div class="form-group">
                            <label><i class="fas fa-chart-line"></i> বর্ধমান চাপ</label>
                            <select id="growing_stress" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Changes Habits -->
                        <div class="form-group">
                            <label><i class="fas fa-exchange-alt"></i> অভ্যাস পরিবর্তন</label>
                            <select id="changes_habits" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Mental Health History -->
                        <div class="form-group">
                            <label><i class="fas fa-history"></i> মানসিক স্বাস্থ্যের ইতিহাস</label>
                            <select id="mental_health_history" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Mood Swings -->
                        <div class="form-group">
                            <label><i class="fas fa-chart-simple"></i> মেজাজের ওঠানামা</label>
                            <select id="mood_swings" class="form-control">
                                <option value="Low">কম</option>
                                <option value="Medium">মাঝারি</option>
                                <option value="High">উচ্চ</option>
                            </select>
                        </div>
                        
                        <!-- Coping Struggles -->
                        <div class="form-group">
                            <label><i class="fas fa-hand-fist"></i> মোকাবিলায় সমস্যা</label>
                            <select id="coping_struggles" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                            </select>
                        </div>
                        
                        <!-- Work Interest -->
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> কাজে আগ্রহ</label>
                            <select id="work_interest" class="form-control">
                                <option value="Yes">হ্যাঁ</option>
                                <option value="No">না</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Social Weakness -->
                        <div class="form-group">
                            <label><i class="fas fa-users-slash"></i> সামাজিক দুর্বলতা</label>
                            <select id="social_weakness" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Mental Health Interview -->
                        <div class="form-group">
                            <label><i class="fas fa-comments"></i> সাক্ষাৎকারে আগ্রহ</label>
                            <select id="mental_health_interview" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Maybe">হতে পারে</option>
                            </select>
                        </div>
                        
                        <!-- Care Options -->
                        <div class="form-group">
                            <label><i class="fas fa-hospital-user"></i> চিকিৎসা সুবিধা</label>
                            <select id="care_options" class="form-control">
                                <option value="No">না</option>
                                <option value="Yes">হ্যাঁ</option>
                                <option value="Not sure">নিশ্চিত নই</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-assess" id="assessBtn" onclick="assessMentalHealth()">
                            <i class="fas fa-brain"></i> মানসিক অবস্থা মূল্যায়ন করুন
                        </button>
                        <div id="loadingSpinner" class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> বিশ্লেষণ করা হচ্ছে...
                        </div>
                    </div>
                </div>
                
                <!-- Results Section -->
                <div id="resultsSection" style="display: none; margin-top: 30px;">
                    <div class="results-header">
                        <h4 style="color: white; margin-bottom: 15px;"><i class="fas fa-chart-bar"></i> আপনার মূল্যায়নের ফলাফল</h4>
                    </div>
                    
                    <div class="results-grid">
                        <div class="result-card">
                            <h5>ঝুঁকি সূচক</h5>
                            <div class="gauge-container">
                                <canvas id="riskGauge" width="200" height="200"></canvas>
                            </div>
                            <div class="risk-level" id="riskLevel"></div>
                        </div>
                        
                        <div class="result-card">
                            <h5>সম্ভাব্যতা বিশ্লেষণ</h5>
                            <canvas id="probabilityChart" width="300" height="200"></canvas>
                        </div>
                        
                        <div class="result-card">
                            <h5>সুপারিশ</h5>
                            <div id="recommendationText"></div>
                            <ul id="tipsList" class="tips-list"></ul>
                            <div id="helplineInfo" class="helpline" style="display: none;">
                                <i class="fas fa-phone-alt"></i>
                                হেল্পলাইন: <strong id="helplineNumber"></strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-save" onclick="saveAssessmentResult()">
                            <i class="fas fa-save"></i> ফলাফল সংরক্ষণ করুন
                        </button>
                        <button class="btn-download" onclick="downloadReport()">
                            <i class="fas fa-download"></i> রিপোর্ট ডাউনলোড
                        </button>
                        <button class="btn-reset" onclick="resetAssessment()">
                            <i class="fas fa-undo"></i> নতুন করে শুরু করুন
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Mood Tracker Section -->
            <div class="mood-section" id="moodSection">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-smile"></i>
                        আজ কেমন লাগছে?
                    </h3>
                    <a href="mood-tracker.php" class="view-link">সব দেখুন →</a>
                </div>
                
                <div class="mood-grid">
                    <div class="mood-option" data-mood="1">
                        <i class="fas fa-sad-tear"></i>
                        <span>খুব খারাপ</span>
                    </div>
                    <div class="mood-option" data-mood="2">
                        <i class="fas fa-frown"></i>
                        <span>খারাপ</span>
                    </div>
                    <div class="mood-option" data-mood="3">
                        <i class="fas fa-meh"></i>
                        <span>সাধারণ</span>
                    </div>
                    <div class="mood-option" data-mood="4">
                        <i class="fas fa-smile"></i>
                        <span>ভালো</span>
                    </div>
                    <div class="mood-option" data-mood="5">
                        <i class="fas fa-grin-stars"></i>
                        <span>দারুণ</span>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="charts-row">
                <!-- Mood Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            সাপ্তাহিক মুড ট্রেন্ড
                        </h4>
                        <select class="chart-filter">
                            <option>এই সপ্তাহ</option>
                            <option>গত সপ্তাহ</option>
                            <option>এই মাস</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="moodChart"></canvas>
                    </div>
                </div>
                
                <!-- Activity Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h4 class="chart-title">
                            <i class="fas fa-chart-pie"></i>
                            অ্যাক্টিভিটি
                        </h4>
                    </div>
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Upcoming Sessions -->
            <div class="sessions-list">
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        আসন্ন সেশন
                    </h3>
                    <a href="appointment.php" class="view-link">সব দেখুন →</a>
                </div>
                
                <div class="sessions-container">
                    <div class="session-item">
                        <div class="session-time">
                            <div class="date">১৫ মার্চ</div>
                            <div class="time">সন্ধ্যা ৭:০০</div>
                        </div>
                        <div class="session-info">
                            <div class="session-title">ক্যারিয়ার কাউন্সেলিং</div>
                            <div class="session-mentor">ড. নাফিজা হক</div>
                        </div>
                        <div class="session-status status-confirmed">নিশ্চিত</div>
                    </div>
                    
                    <div class="session-item">
                        <div class="session-time">
                            <div class="date">১৭ মার্চ</div>
                            <div class="time">রাত ৮:৩০</div>
                        </div>
                        <div class="session-info">
                            <div class="session-title">গ্রুপ থেরাপি</div>
                            <div class="session-mentor">সাদিয়া ইসলাম</div>
                        </div>
                        <div class="session-status status-pending">অপেক্ষমান</div>
                    </div>
                    
                    <div class="session-item">
                        <div class="session-time">
                            <div class="date">২০ মার্চ</div>
                            <div class="time">বিকাল ৪:০০</div>
                        </div>
                        <div class="session-info">
                            <div class="session-title">মেডিকেল চেকআপ</div>
                            <div class="session-mentor">অধ্যাপক মোঃ আনিস</div>
                        </div>
                        <div class="session-status status-confirmed">নিশ্চিত</div>
                    </div>
                </div>
            </div>
            
            <!-- Recommended Mentors -->
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-star"></i>
                    প্রস্তাবিত মেন্টর
                </h3>
                <a href="mentor.php" class="view-link">সব দেখুন →</a>
            </div>
            
            <div class="mentors-grid">
                <div class="mentor-card">
                    <div class="mentor-avatar">
                        <img src="../assets/images/avatars/mentor1.jpg" alt="Dr. Nafiza">
                    </div>
                    <div class="mentor-name">ড. নাফিজা হক</div>
                    <div class="mentor-specialty">ক্লিনিক্যাল সাইকোলজিস্ট</div>
                    <div class="mentor-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(৪.৮)</span>
                    </div>
                    <button class="btn-book" onclick="bookMentor(1)">বুক করুন</button>
                </div>
                
                <div class="mentor-card">
                    <div class="mentor-avatar">
                        <img src="../assets/images/avatars/mentor2.jpg" alt="Prof. Anis">
                    </div>
                    <div class="mentor-name">অধ্যাপক মোঃ আনিস</div>
                    <div class="mentor-specialty">সাইকিয়াট্রিস্ট</div>
                    <div class="mentor-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <span>(৫.০)</span>
                    </div>
                    <button class="btn-book" onclick="bookMentor(2)">বুক করুন</button>
                </div>
                
                <div class="mentor-card">
                    <div class="mentor-avatar">
                        <img src="../assets/images/avatars/mentor3.jpg" alt="Sadia Islam">
                    </div>
                    <div class="mentor-name">সাদিয়া ইসলাম</div>
                    <div class="mentor-specialty">কাউন্সেলর</div>
                    <div class="mentor-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                        <span>(৪.৯)</span>
                    </div>
                    <button class="btn-book" onclick="bookMentor(3)">বুক করুন</button>
                </div>
            </div>
            
            <!-- Recent Journal Entries -->
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-book-open"></i>
                    সাম্প্রতিক জার্নাল
                </h3>
                <a href="journal.php" class="view-link">নতুন লিখুন +</a>
            </div>
            
            <div class="journal-grid">
                <div class="journal-card">
                    <div class="journal-date">১২ মার্চ, ২০২৪</div>
                    <div class="journal-title">আজকের দিনটা ভালো গেল</div>
                    <div class="journal-preview">
                        আজ অফিসে ভালো একটা দিন কাটলো। টিমের সবার সাথে আড্ডা হল...
                    </div>
                    <div class="journal-footer">
                        <div class="journal-mood">
                            <i class="fas fa-smile"></i>
                            <span>মুড: ৮/১০</span>
                        </div>
                        <a href="#" class="view-link">পড়ুন →</a>
                    </div>
                </div>
                
                <div class="journal-card">
                    <div class="journal-date">১০ মার্চ, ২০২৪</div>
                    <div class="journal-title">মেন্টর সেশনের অভিজ্ঞতা</div>
                    <div class="journal-preview">
                        ড. নাফিজার সাথে আজকের সেশনটা ছিল অসাধারণ। অনেক কিছু শিখলাম...
                    </div>
                    <div class="journal-footer">
                        <div class="journal-mood">
                            <i class="fas fa-smile"></i>
                            <span>মুড: ৯/১০</span>
                        </div>
                        <a href="#" class="view-link">পড়ুন →</a>
                    </div>
                </div>
            </div>
            
            <!-- Activity Feed & Quick Actions Row -->
            <div class="charts-row">
                <!-- Activity Feed -->
                <div class="activity-feed">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-clock"></i>
                            সাম্প্রতিক অ্যাক্টিভিটি
                        </h3>
                    </div>
                    
                    <div class="activities-container">
                        <div class="activity-item">
                            <div class="activity-icon chat">
                                <i class="fas fa-robot"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">এআই চ্যাটবট "মনের বন্ধু"-এর সাথে কথা বলেছেন</div>
                                <div class="activity-time">১০ মিনিট আগে</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon mentor">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">মেন্টর সেশন সম্পন্ন: ড. নাফিজার সাথে</div>
                                <div class="activity-time">২ ঘন্টা আগে</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon journal">
                                <i class="fas fa-pen"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">নতুন জার্নাল এন্ট্রি যোগ করা হয়েছে</div>
                                <div class="activity-time">৫ ঘন্টা আগে</div>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text">নতুন অ্যাচিভমেন্ট: "৭ দিন একটানা"</div>
                                <div class="activity-time">গতকাল</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="activity-feed">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-bolt"></i>
                            কুইক অ্যাকশন
                        </h3>
                    </div>
                    
                    <div class="quick-actions">
                        <div class="quick-action" data-action="chat">
                            <i class="fas fa-robot"></i>
                            <span>চ্যাট শুরু</span>
                        </div>
                        
                        <div class="quick-action" data-action="mood">
                            <i class="fas fa-smile"></i>
                            <span>মুড ট্র্যাক</span>
                        </div>
                        
                        <div class="quick-action" data-action="journal">
                            <i class="fas fa-pen"></i>
                            <span>জার্নাল লিখুন</span>
                        </div>
                        
                        <div class="quick-action" data-action="mentor">
                            <i class="fas fa-chalkboard-teacher"></i>
                            <span>মেন্টর খুঁজুন</span>
                        </div>
                        
                        <div class="quick-action" data-action="appointment">
                            <i class="fas fa-calendar-plus"></i>
                            <span>অ্যাপয়েন্টমেন্ট</span>
                        </div>
                        
                        <div class="quick-action" data-action="community">
                            <i class="fas fa-users"></i>
                            <span>কমিউনিটি</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Quote -->
            <div style="text-align: center; margin: 40px 0 20px; color: #999;">
                <i class="fas fa-quote-left"></i>
                আপনার মানসিক স্বাস্থ্যের যাত্রায় আমরা পাশে আছি
                <i class="fas fa-quote-right"></i>
            </div>
        </div>
    </div>
    
    <!-- JavaScript for Mental Health Assessment -->
    <script>
        let currentResult = null;
        let riskGaugeChart = null;
        let probabilityChart = null;
        
        async function assessMentalHealth() {
            // Get form data
            const userData = {
                gender: document.getElementById('gender').value,
                occupation: document.getElementById('occupation').value,
                self_employed: document.getElementById('self_employed').value,
                family_history: document.getElementById('family_history').value,
                Days_Indoors: document.getElementById('days_indoors').value,
                Growing_Stress: document.getElementById('growing_stress').value,
                Changes_Habits: document.getElementById('changes_habits').value,
                Mental_Health_History: document.getElementById('mental_health_history').value,
                Mood_Swings: document.getElementById('mood_swings').value,
                Coping_Struggles: document.getElementById('coping_struggles').value,
                Work_Interest: document.getElementById('work_interest').value,
                Social_Weakness: document.getElementById('social_weakness').value,
                mental_health_interview: document.getElementById('mental_health_interview').value,
                care_options: document.getElementById('care_options').value
            };
            
            // Validation
            if (!userData.gender || !userData.occupation) {
                showToast('দয়া করে লিঙ্গ এবং পেশা নির্বাচন করুন', 'error');
                return;
            }
            
            // Show loading
            document.getElementById('loadingSpinner').style.display = 'block';
            document.getElementById('assessBtn').disabled = true;
            
            try {
                // Simulate API call with Python backend
                // For now, using demo prediction (replace with actual API call)
                const result = await mockPrediction(userData);
                
                if (result.success) {
                    currentResult = result;
                    showResults(result);
                    showToast('মূল্যায়ন সম্পন্ন হয়েছে!', 'success');
                } else {
                    showToast('ত্রুটি: ' + result.error, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('সার্ভারে সংযোগ করতে পারেনি', 'error');
            } finally {
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('assessBtn').disabled = false;
            }
        }
        
        // Mock prediction function (replace with actual API call)
        async function mockPrediction(userData) {
            // Simple mock logic - replace with actual ML model API
            let riskScore = 0;
            
            if (userData.family_history === 'Yes') riskScore += 25;
            if (userData.Mood_Swings === 'High') riskScore += 20;
            if (userData.Mood_Swings === 'Medium') riskScore += 10;
            if (userData.Coping_Struggles === 'Yes') riskScore += 15;
            if (userData.Growing_Stress === 'Yes') riskScore += 15;
            if (userData.Work_Interest === 'No') riskScore += 10;
            if (userData.Social_Weakness === 'Yes') riskScore += 10;
            if (userData.days_indoors === 'More than 2 months') riskScore += 10;
            
            riskScore = Math.min(riskScore, 100);
            
            const prediction = riskScore > 50 ? 1 : 0;
            
            let recommendation;
            if (riskScore > 60) {
                recommendation = {
                    message: '⚠️ আপনার মানসিক স্বাস্থ্যের ঝুঁকি বেশি। দয়া করে একজন বিশেষজ্ঞের সাথে কথা বলুন।',
                    helpline: '01977-855055',
                    tips: ['নিয়মিত ঘুমান', 'পরিবার ও বন্ধুদের সাথে কথা বলুন', 'প্রফেশনাল হেল্প নিন']
                };
            } else if (riskScore > 30) {
                recommendation = {
                    message: '🟡 আপনার মানসিক স্বাস্থ্যের মাঝারি ঝুঁকি রয়েছে। নিয়মিত সেলফ-কেয়ার অনুশীলন করুন।',
                    tips: ['নিয়মিত ব্যায়াম করুন', 'মেডিটেশন করুন', 'জার্নাল লিখুন', 'মনের বন্ধু চ্যাটবট ব্যবহার করুন']
                };
            } else {
                recommendation = {
                    message: '✅ আপনার মানসিক স্বাস্থ্য ভালো অবস্থায় আছে। সুস্থ থাকার অভ্যাস বজায় রাখুন।',
                    tips: ['নিয়মিত মুড ট্র্যাক করুন', 'সুস্থ খাদ্যাভ্যাস বজায় রাখুন', 'সামাজিক যোগাযোগ বজায় রাখুন']
                };
            }
            
            return {
                success: true,
                prediction: prediction === 1 ? 'Treatment Recommended' : 'No Treatment Needed',
                prediction_code: prediction,
                risk_percentage: riskScore,
                probability_no_treatment: 100 - riskScore,
                probability_treatment: riskScore,
                risk_level: riskScore > 60 ? 'High' : (riskScore > 30 ? 'Moderate' : 'Low'),
                recommendation: recommendation
            };
        }
        
        // Actual API call (uncomment when Python server is running)
        /*
        async function actualPrediction(userData) {
            const response = await fetch('http://localhost:5000/api/predict', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(userData)
            });
            return await response.json();
        }
        */
        
        function showResults(result) {
            document.getElementById('resultsSection').style.display = 'block';
            
            const riskLevel = document.getElementById('riskLevel');
            riskLevel.textContent = `ঝুঁকির মাত্রা: ${result.risk_level} (${result.risk_percentage}%)`;
            riskLevel.className = `risk-level ${result.risk_level.toLowerCase()}`;
            
            document.getElementById('recommendationText').innerHTML = `<p>${result.recommendation.message}</p>`;
            
            const tipsList = document.getElementById('tipsList');
            tipsList.innerHTML = result.recommendation.tips.map(tip => 
                `<li><i class="fas fa-check-circle"></i> ${tip}</li>`
            ).join('');
            
            if (result.recommendation.helpline) {
                document.getElementById('helplineNumber').textContent = result.recommendation.helpline;
                document.getElementById('helplineInfo').style.display = 'block';
            } else {
                document.getElementById('helplineInfo').style.display = 'none';
            }
            
            createGaugeChart(result.risk_percentage);
            createProbabilityChart(result.probability_no_treatment, result.probability_treatment);
        }
        
        function createGaugeChart(percentage) {
            const ctx = document.getElementById('riskGauge').getContext('2d');
            const color = percentage > 60 ? '#dc3545' : (percentage > 30 ? '#ffc107' : '#28a745');
            
            if (riskGaugeChart) riskGaugeChart.destroy();
            
            riskGaugeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [percentage, 100 - percentage],
                        backgroundColor: [color, '#e9ecef'],
                        borderWidth: 0,
                        circumference: 180,
                        rotation: 270
                    }]
                },
                options: {
                    cutout: '70%',
                    plugins: { tooltip: { enabled: false }, legend: { display: false } }
                }
            });
            
            ctx.font = 'bold 20px Inter';
            ctx.fillStyle = color;
            ctx.textAlign = 'center';
            ctx.fillText(`${percentage}%`, 100, 120);
        }
        
        function createProbabilityChart(noTreatment, treatment) {
            const ctx = document.getElementById('probabilityChart').getContext('2d');
            
            if (probabilityChart) probabilityChart.destroy();
            
            probabilityChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['চিকিৎসার প্রয়োজন নেই', 'চিকিৎসার প্রয়োজন'],
                    datasets: [{
                        data: [noTreatment, treatment],
                        backgroundColor: ['#28a745', '#dc3545'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw}%` } }
                    }
                }
            });
        }
        
        function saveAssessmentResult() {
            if (!currentResult) return;
            
            const assessmentData = {
                user_id: <?php echo $user_id; ?>,
                gender: document.getElementById('gender').value,
                occupation: document.getElementById('occupation').value,
                self_employed: document.getElementById('self_employed').value,
                family_history: document.getElementById('family_history').value,
                Days_Indoors: document.getElementById('days_indoors').value,
                Growing_Stress: document.getElementById('growing_stress').value,
                Changes_Habits: document.getElementById('changes_habits').value,
                Mental_Health_History: document.getElementById('mental_health_history').value,
                Mood_Swings: document.getElementById('mood_swings').value,
                Coping_Struggles: document.getElementById('coping_struggles').value,
                Work_Interest: document.getElementById('work_interest').value,
                Social_Weakness: document.getElementById('social_weakness').value,
                mental_health_interview: document.getElementById('mental_health_interview').value,
                care_options: document.getElementById('care_options').value,
                prediction_result: currentResult.prediction,
                risk_percentage: currentResult.risk_percentage
            };
            
            fetch('save_assessment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(assessmentData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('ফলাফল সংরক্ষণ করা হয়েছে!', 'success');
                } else {
                    showToast('সংরক্ষণ করতে ব্যর্থ হয়েছে', 'error');
                }
            })
            .catch(err => {
                console.error('Save error:', err);
                showToast('সংরক্ষণ করতে ব্যর্থ হয়েছে', 'error');
            });
        }
        
        function downloadReport() {
            if (!currentResult) return;
            
            const report = `
=========================================
    মানসিক স্বাস্থ্য মূল্যায়ন রিপোর্ট
=========================================

তারিখ: ${new Date().toLocaleString('bn-BD')}

-----------------------------------------
মূল্যায়নের ফলাফল:
-----------------------------------------
অবস্থা: ${currentResult.prediction}
ঝুঁকির মাত্রা: ${currentResult.risk_percentage}%
রিস্ক লেভেল: ${currentResult.risk_level}

-----------------------------------------
সুপারিশ:
-----------------------------------------
${currentResult.recommendation.message}

টিপস:
${currentResult.recommendation.tips.map(tip => `- ${tip}`).join('\n')}

${currentResult.recommendation.helpline ? `হেল্পলাইন: ${currentResult.recommendation.helpline}` : ''}

-----------------------------------------
মেন্টোরা - আপনার মানসিক স্বাস্থ্যের সঙ্গী
=========================================
            `;
            
            const blob = new Blob([report], {type: 'text/plain'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `mental_health_report_${Date.now()}.txt`;
            a.click();
            URL.revokeObjectURL(url);
            
            showToast('রিপোর্ট ডাউনলোড শুরু হয়েছে', 'success');
        }
        
        function resetAssessment() {
            const selects = document.querySelectorAll('#assessmentForm select');
            selects.forEach(select => {
                select.selectedIndex = 0;
            });
            document.getElementById('resultsSection').style.display = 'none';
            document.getElementById('mentalHealthCard').scrollIntoView({ behavior: 'smooth' });
            currentResult = null;
            showToast('ফর্ম রিসেট করা হয়েছে', 'success');
        }
        
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = 'toast-message';
            toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
            toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function bookMentor(id) {
            showToast('মেন্টর বুকিংয়ের জন্য শীঘ্রই যোগাযোগ করা হবে', 'success');
        }
        
        // Sidebar toggle for mobile
        document.getElementById('menuToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Mood tracker
        document.querySelectorAll('.mood-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.mood-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                const moodValue = this.dataset.mood;
                document.getElementById('moodScore').innerText = moodValue * 2;
                showToast(`আপনার মুড রেকর্ড করা হয়েছে: ${this.querySelector('span').innerText}`, 'success');
            });
        });
        
        // Initialize charts
        const moodCtx = document.getElementById('moodChart')?.getContext('2d');
        if (moodCtx) {
            new Chart(moodCtx, {
                type: 'line',
                data: {
                    labels: ['সোম', 'মঙ্গল', 'বুধ', 'বৃহ', 'শুক্র', 'শনি', 'রবি'],
                    datasets: [{
                        label: 'মুড স্কোর',
                        data: [6, 7, 5, 8, 7, 9, 8],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
        
        const activityCtx = document.getElementById('activityChart')?.getContext('2d');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['চ্যাট', 'জার্নাল', 'মেন্টর', 'মুড'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: ['#667eea', '#f093fb', '#f5576c', '#ffc107']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }
        
        // Quick actions
        document.querySelectorAll('.quick-action').forEach(action => {
            action.addEventListener('click', function() {
                const actionType = this.dataset.action;
                if (actionType === 'chat') window.location.href = 'chatbot.php';
                else if (actionType === 'mood') document.getElementById('moodSection').scrollIntoView({ behavior: 'smooth' });
                else if (actionType === 'journal') window.location.href = 'journal.php';
                else if (actionType === 'mentor') window.location.href = 'mentor.php';
                else if (actionType === 'appointment') window.location.href = 'appointment.php';
                else if (actionType === 'community') window.location.href = 'community.php';
            });
        });
        
        console.log('✅ Mental Health Assessment System Loaded Successfully!');
    </script>
</body>
</html>