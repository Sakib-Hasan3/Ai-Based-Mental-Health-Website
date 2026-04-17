<?php
// dashboard/assessment.php
session_start();
require_once '../includes/auth_check.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'];

// Database connection for profile image
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);
$profile_image = 'default-avatar.svg';

if (!$conn->connect_error) {
    $sql = "SELECT profile_image FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $profile_image = $row['profile_image'] ?? 'default-avatar.svg';
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
    <title>মানসিক স্বাস্থ্য যাচাই - মেন্টোরা</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
        }
        
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
            z-index: 1000;
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
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 12px;
            gap: 12px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-link.active {
            background: white;
            color: #4f46e5;
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .assessment-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .assessment-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
        }
        
        .assessment-hero h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .assessment-card {
            background: white;
            border-radius: 30px;
            padding: 35px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .form-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--gray-200);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 14px;
        }
        
        .form-group label i {
            color: var(--primary);
            margin-right: 8px;
        }
        
        .form-select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--gray-200);
            border-radius: 12px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }
        
        .form-select:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .btn-assess {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            border: none;
            padding: 16px 35px;
            border-radius: 50px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .btn-assess:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16,185,129,0.3);
        }
        
        .btn-assess:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .results-section {
            display: none;
            animation: fadeIn 0.5s ease;
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .result-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .result-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .risk-score {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .result-message {
            margin: 20px 0;
            font-size: 16px;
            line-height: 1.6;
        }
        
        .results-section.visible {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .gauge-container {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .gauge-wrapper {
            position: relative;
            width: 200px;
            height: 200px;
            margin: 0 auto 20px;
        }
        
        .gauge {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            transition: all 1s ease;
        }
        
        .gauge-value {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 36px;
            font-weight: 800;
        }
        
        .gauge-label {
            font-size: 18px;
            font-weight: 600;
            padding: 8px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        
        .gauge-label.low { background: #d1fae5; color: #065f46; }
        .gauge-label.moderate { background: #fed7aa; color: #92400e; }
        .gauge-label.high { background: #fee2e2; color: #991b1b; }
        
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }
        
        .chart-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
        }
        
        .chart-container {
            height: 250px;
            position: relative;
        }
        
        .factors-list {
            margin-top: 10px;
        }
        
        .factor-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .factor-name {
            width: 120px;
            font-size: 13px;
            color: var(--gray-600);
        }
        
        .factor-bar {
            flex: 1;
            height: 8px;
            background: var(--gray-200);
            border-radius: 4px;
            overflow: hidden;
        }
        
        .factor-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border-radius: 4px;
            width: 0%;
            transition: width 1s ease;
        }
        
        .factor-value {
            width: 50px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .recommendation-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 25px;
            border-left: 8px solid transparent;
        }
        
        .recommendation-card.low { border-left-color: var(--success); }
        .recommendation-card.moderate { border-left-color: var(--warning); }
        .recommendation-card.high { border-left-color: var(--danger); }
        
        .recommendation-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .tips-list {
            list-style: none;
            padding: 0;
        }
        
        .tips-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .helpline {
            background: #fee2e2;
            border: 2px solid #ef4444;
            border-radius: 16px;
            padding: 20px;
            margin-top: 20px;
            text-align: center;
        }
        
        .helpline-number {
            font-size: 24px;
            font-weight: 800;
            color: #dc2626;
        }
        
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .sidebar .logo span,
            .sidebar .user-info,
            .nav-link span {
                display: none;
            }
            .main-content {
                margin-left: 70px;
                padding: 15px;
            }
            .form-grid,
            .charts-grid {
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
                <div class="logo">
                    🧠 মেন্টোরা
                    <span>Mentora</span>
                </div>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="User">
                </div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
            
             <ul class="nav-menu">
                <li class="nav-item"><a href="/mental%20health/dashboard/index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mood-tracker.php" class="nav-link"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/assessment.php" class="nav-link active"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/journal.php" class="nav-link"><i class="fas fa-book"></i><span>জার্নাল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mentor.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/community.php" class="nav-link"><i class="fas fa-users"></i><span>কমিউনিটি</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/doctor.php" class="nav-link"><i class="fas fa-user-md"></i><span>ডাক্তার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/resources.php" class="nav-link"><i class="fas fa-book-open"></i><span>রিসোর্স</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/achievements.php" class="nav-link"><i class="fas fa-trophy"></i><span>অ্যাচিভমেন্ট</span></a></li>
                <li class="nav-item"><a href="/mental%20health/auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-brain"></i> মানসিক স্বাস্থ্য যাচাই</h1>
            </div>
            
            <div class="assessment-container">
                <!-- Hero Section -->
                <div class="assessment-hero">
                    <h1>🧠 মেন্টাল হেলথ সেলফ-অ্যাসেসমেন্ট</h1>
                    <p>নিচের প্রশ্নগুলোর উত্তর দিন এবং আপনার মানসিক স্বাস্থ্যের অবস্থা জানুন</p>
                </div>
                
                <!-- Assessment Form -->
                <div class="assessment-card">
                    <div class="form-title"><i class="fas fa-clipboard-list"></i> আপনার তথ্য দিন</div>
                    <div id="assessmentError" class="error-message" style="display:none; color:#ef4444; padding:12px; background:#fee2e2; border:1px solid #fca5a5; border-radius:6px; margin-bottom:15px;"></div>
                    
                    <form id="assessmentForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label><i class="fas fa-venus-mars"></i> লিঙ্গ / Gender</label>
                                <select class="form-select" id="gender" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Male">পুরুষ (Male)</option>
                                    <option value="Female">মহিলা (Female)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-briefcase"></i> পেশা / Occupation</label>
                                <select class="form-select" id="occupation" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Corporate">কর্পোরেট (Corporate)</option>
                                    <option value="Business">ব্যবসা (Business)</option>
                                    <option value="Student">ছাত্র/ছাত্রী (Student)</option>
                                    <option value="Housewife">গৃহিণী (Housewife)</option>
                                    <option value="Others">অন্যান্য (Others)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-user-tie"></i> স্ব-নিযুক্ত / Self Employed</label>
                                <select class="form-select" id="self_employed" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-history"></i> পারিবারিক ইতিহাস / Family History</label>
                                <select class="form-select" id="family_history" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-home"></i> গৃহে অবস্থান / Days Indoors</label>
                                <select class="form-select" id="days_indoors" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="1-14 days">১-১৪ দিন (1-14 days)</option>
                                    <option value="15-30 days">১৫-৩০ দিন (15-30 days)</option>
                                    <option value="31-60 days">৩১-৬০ দিন (31-60 days)</option>
                                    <option value="More than 2 months">২ মাসের বেশি (More than 2 months)</option>
                                    <option value="Go out Every day">প্রতিদিন বাইরে যাই (Go out Every day)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-chart-line"></i> বর্ধমান চাপ / Growing Stress</label>
                                <select class="form-select" id="growing_stress" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-utensils"></i> অভ্যাস পরিবর্তন / Changes Habits</label>
                                <select class="form-select" id="changes_habits" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-notes-medical"></i> মানসিক স্বাস্থ্যের ইতিহাস / Mental Health History</label>
                                <select class="form-select" id="mental_health_history" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-smile"></i> মেজাজের ওঠানামা / Mood Swings</label>
                                <select class="form-select" id="mood_swings" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Low">কম (Low)</option>
                                    <option value="Medium">মাঝারি (Medium)</option>
                                    <option value="High">উচ্চ (High)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-hand"></i> মোকাবিলায় সমস্যা / Coping Struggles</label>
                                <select class="form-select" id="coping_struggles" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-briefcase"></i> কাজে আগ্রহ / Work Interest</label>
                                <select class="form-select" id="work_interest" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-users"></i> সামাজিক দুর্বলতা / Social Weakness</label>
                                <select class="form-select" id="social_weakness" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-comments"></i> সাক্ষাৎকারে আগ্রহ / Mental Health Interview</label>
                                <select class="form-select" id="mental_health_interview" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Maybe">হতে পারে (Maybe)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-hospital"></i> চিকিৎসা সুবিধা / Care Options</label>
                                <select class="form-select" id="care_options" required>
                                    <option value="">নির্বাচন করুন</option>
                                    <option value="Yes">হ্যাঁ (Yes)</option>
                                    <option value="No">না (No)</option>
                                    <option value="Not sure">নিশ্চিত নই (Not sure)</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-assess" id="submitBtn">
                            <i class="fas fa-brain"></i> আমার মানসিক স্বাস্থ্য যাচাই করুন
                        </button>
                    </form>
                </div>
                
                <!-- Results Section -->
                <div class="results-section" id="resultsSection">
                    <div class="gauge-container">
                        <h4>ঝুঁকির মাত্রা</h4>
                        <div class="gauge-wrapper">
                            <div class="gauge" id="gauge"></div>
                            <div class="gauge-value" id="gaugePercentage">0%</div>
                        </div>
                        <div class="gauge-label" id="riskLevel">-</div>
                    </div>
                    
                    <div class="charts-grid">
                        <div class="chart-card">
                            <h4><i class="fas fa-chart-pie"></i> চিকিৎসার সম্ভাবনা</h4>
                            <div class="chart-container"><canvas id="probabilityChart"></canvas></div>
                        </div>
                        <div class="chart-card">
                            <h4><i class="fas fa-exclamation-triangle"></i> প্রধান ঝুঁকির কারণ</h4>
                            <div class="factors-list" id="factorsList"></div>
                        </div>
                    </div>
                    
                    <div id="recommendations"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    $(document).ready(function() {
        console.log("✅ Page loaded, setting up form...");
        
        // Ensure results section is hidden
        $('#resultsSection').hide();
        console.log("Results section setup:", $('#resultsSection').length);
        
        // Bind form submit - use on() to catch all submits
        $(document).on('submit', '#assessmentForm', function(e) {
            console.log("🔘 Form submit triggered!");
            e.preventDefault();
            
            // Get all selects
            const selects = $('.form-select');
            console.log("Form fields found:", selects.length);
            
            // Check all filled
            let allFilled = true;
            const empty = [];
            
            selects.each(function(idx) {
                const val = $(this).val();
                const label = $(this).closest('.form-group').find('label').text().trim();
                console.log(`Field ${idx}: ${label} = "${val}"`);
                
                if (!val) {
                    allFilled = false;
                    empty.push(label);
                }
            });
            
            console.log("All filled?", allFilled);
            
            if (!allFilled) {
                alert('❌ দয়া করে সব প্রশ্নের উত্তর দিন:\n' + empty.join('\n'));
                return false;
            }
            
            // Collect form data
            const data = {
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
            
            console.log("📤 Sending form data:", data);
            
            const $btn = $('#submitBtn');
            $btn.prop('disabled', true).text('⏳ বিশ্লেষণ করছে...');
            
            // Send AJAX
            $.ajax({
                url: '../api/predict.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                timeout: 30000,
                success: function(response) {
                    console.log("✅ Response received:", response);
                    
                    const result = response.data || response;
                    
                    if (result && result.risk_percentage !== undefined) {
                        console.log("✨ Valid result, showing...");
                        
                        let html = `<h2>📊 আপনার মানসিক স্বাস্থ্য মূল্যায়ন ফলাফল</h2>
                            <div class="result-grid">
                                <div class="result-card">
                                    <h4>ঝুঁকির মাত্রা</h4>
                                    <div class="risk-score">${result.risk_percentage}%</div>
                                    <div class="risk-level">${result.risk_level || 'মূল্যায়ন'}</div>
                                </div>
                                <div class="result-card">
                                    <h4>পূর্বাভাস</h4>
                                    <div class="prediction">${result.prediction || 'বিশেষজ্ঞ পরামর্শ প্রয়োজন'}</div>
                                </div>
                            </div>
                            <div class="result-message">`;
                        
                        if (result.risk_percentage >= 60) {
                            html += '<p style="color: #dc3545;"><strong>⚠️ উচ্চ ঝুঁকি:</strong> একজন মনোরোগ বিশেষজ্ঞের সাথে যোগাযোগ করুন।<br>📞 হটলাইন: <strong>০১৯৭৭-৮৫৫০৫৫</strong></p>';
                        } else if (result.risk_percentage >= 30) {
                            html += '<p style="color: #ffc107;"><strong>🟡 মাঝারি ঝুঁকি:</strong> নিয়মিত স্ব-যত্ন নিন এবং কাউন্সেলরের সাথে যোগাযোগ করুন।</p>';
                        } else {
                            html += '<p style="color: #28a745;"><strong>✅ ভালো অবস্থা:</strong> আপনার মানসিক স্বাস্থ্য ভালো। এই ধারা বজায় রাখুন।</p>';
                        }
                        
                        html += '</div>';
                        
                        $('#resultsSection').html(html).slideDown(300);
                        
                        setTimeout(() => {
                            $('html, body').animate({
                                scrollTop: $('#resultsSection').offset().top - 50
                            }, 500);
                        }, 100);
                    } else {
                        console.error("❌ Invalid response format");
                        alert('ফলাফল পাওয়া যায়নি। আবার চেষ্টা করুন।');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("❌ AJAX error:", status, error);
                    console.error("Response text:", xhr.responseText);
                    console.error("Status code:", xhr.status);
                    
                    alert('❌ সার্ভার ত্রুটি:\n' + (xhr.responseText ? xhr.responseText.substring(0, 200) : error));
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="fas fa-brain"></i> আমার মানসিক স্বাস্থ্য যাচাই করুন');
                }
            });
            
            return false;
        });
        
        console.log("✅ All setup complete!");
    });
    </script>
</body>
</html>