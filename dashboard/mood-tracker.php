<?php
// dashboard/mood-tracker.php
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

// Mood options
$moodOptions = [
    ['score' => 1, 'label' => 'খুব খারাপ', 'emoji' => '😢', 'color' => '#ef4444'],
    ['score' => 2, 'label' => 'খারাপ', 'emoji' => '😞', 'color' => '#f97316'],
    ['score' => 3, 'label' => 'অসন্তোষজনক', 'emoji' => '😕', 'color' => '#f59e0b'],
    ['score' => 4, 'label' => 'মোটামুটি', 'emoji' => '😐', 'color' => '#eab308'],
    ['score' => 5, 'label' => 'সাধারণ', 'emoji' => '😌', 'color' => '#84cc16'],
    ['score' => 6, 'label' => 'ভালো', 'emoji' => '🙂', 'color' => '#22c55e'],
    ['score' => 7, 'label' => 'খুব ভালো', 'emoji' => '😊', 'color' => '#10b981'],
    ['score' => 8, 'label' => 'দারুণ', 'emoji' => '😄', 'color' => '#14b8a6'],
    ['score' => 9, 'label' => 'চমৎকার', 'emoji' => '😍', 'color' => '#06b6d4'],
    ['score' => 10, 'label' => 'অসাধারণ', 'emoji' => '🤩', 'color' => '#3b82f6']
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মুড ট্র্যাকার - মেন্টোরা</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/mood-tracker.css">
    
    <style>
        /* Dashboard sidebar styles */
        body {
            font-family: 'Inter', 'Noto Sans Bengali', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f6 100%);
            min-height: 100vh;
        }
        
        .dashboard-container { display: flex; min-height: 100vh; }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header { padding: 30px 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo { font-size: 28px; font-weight: 800; }
        .logo span { font-size: 12px; opacity: 0.7; display: block; margin-top: 5px; }
        
        .user-info { padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .user-avatar { width: 90px; height: 90px; border-radius: 50%; margin: 0 auto 15px; border: 3px solid white; overflow: hidden; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-name { font-size: 18px; font-weight: 600; margin-bottom: 5px; }
        .user-email { font-size: 12px; opacity: 0.8; }
        
        .nav-menu { list-style: none; padding: 20px 0; }
        .nav-item { margin: 5px 15px; }
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
        .nav-link:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-link.active { background: white; color: #4f46e5; }
        
        .main-content { flex: 1; margin-left: 280px; padding: 30px; }
        
        .top-header {
            background: white;
            border-radius: 24px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title { font-size: 24px; font-weight: 700; color: var(--gray-800); }
        .page-title i { color: var(--primary); margin-right: 10px; }
        
        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .sidebar .logo span, .sidebar .user-info, .nav-link span { display: none; }
            .main-content { margin-left: 70px; padding: 15px; }
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
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="mood-tracker.php" class="nav-link active"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="assessment.php" class="nav-link"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-smile"></i> মুড ট্র্যাকার</h1>
            </div>
            
            <div class="mood-container">
                <!-- Hero Section -->
                <div class="mood-hero">
                    <h1>📊 আপনার মুড ট্র্যাক করুন</h1>
                    <p>প্রতিদিন আপনার মানসিক অবস্থা ট্র্যাক করুন এবং উন্নতির ধারা বুঝুন</p>
                </div>
                
                <!-- Date Picker -->
                <div class="date-picker-section">
                    <div>
                        <label><i class="fas fa-calendar-alt"></i> তারিখ নির্বাচন করুন: </label>
                        <input type="date" id="moodDate" class="date-input">
                    </div>
                    <button class="btn-today" id="todayBtn"><i class="fas fa-calendar-day"></i> আজ</button>
                </div>
                
                <!-- Mood Selection -->
                <div class="mood-section">
                    <div class="mood-section-title">
                        <i class="fas fa-smile"></i> আজ কেমন লাগছে?
                    </div>
                    <div class="mood-grid">
                        <?php foreach ($moodOptions as $mood): ?>
                        <div class="mood-card" data-score="<?php echo $mood['score']; ?>" data-label="<?php echo $mood['label']; ?>" data-emoji="<?php echo $mood['emoji']; ?>">
                            <div class="mood-emoji"><?php echo $mood['emoji']; ?></div>
                            <div class="mood-label"><?php echo $mood['label']; ?></div>
                            <div class="mood-score"><?php echo $mood['score']; ?>/10</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <input type="hidden" id="selectedMoodScore">
                    <input type="hidden" id="selectedMoodLabel">
                    <input type="hidden" id="selectedMoodEmoji">
                    
                    <!-- Activities -->
                    <div class="mood-section-title">
                        <i class="fas fa-running"></i> আজকের কার্যক্রম
                    </div>
                    <div class="activity-grid">
                        <label class="activity-checkbox">
                            <input type="checkbox" id="exercise">
                            <span><i class="fas fa-dumbbell"></i> ব্যায়াম করেছি</span>
                        </label>
                        <label class="activity-checkbox">
                            <input type="checkbox" id="meditation">
                            <span><i class="fas fa-spa"></i> মেডিটেশন করেছি</span>
                        </label>
                        <label class="activity-checkbox">
                            <input type="checkbox" id="socialContact">
                            <span><i class="fas fa-users"></i> বন্ধু/পরিবারের সাথে সময় কাটিয়েছি</span>
                        </label>
                    </div>
                    
                    <!-- Sleep Hours -->
                    <div class="sleep-section">
                        <label><i class="fas fa-moon"></i> ঘুমের সময় (ঘন্টা):</label>
                        <input type="number" id="sleepHours" class="sleep-input" step="0.5" min="0" max="24" placeholder="যেমন: 7.5">
                    </div>
                    
                    <!-- Notes -->
                    <div class="notes-section">
                        <label><i class="fas fa-pen"></i> নোট (ঐচ্ছিক):</label>
                        <textarea id="moodNotes" class="notes-textarea" rows="3" placeholder="আজকের দিনটি কেমন কাটলো? আপনার অনুভূতি লিখুন..."></textarea>
                    </div>
                    
                    <!-- Save Button -->
                    <button class="btn-save" id="saveMoodBtn">
                        <i class="fas fa-save"></i> মুড সংরক্ষণ করুন
                    </button>
                </div>
                
                <!-- Statistics & Chart -->
                <div class="stats-section">
                    <div class="stats-header">
                        <div class="stats-title">
                            <i class="fas fa-chart-line"></i> মুড ট্রেন্ড
                        </div>
                        <div class="period-selector">
                            <button class="period-btn active" data-period="week">সাপ্তাহিক</button>
                            <button class="period-btn" data-period="month">মাসিক</button>
                            <button class="period-btn" data-period="year">বার্ষিক</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="moodChart"></canvas>
                    </div>
                    
                    <div class="summary-stats">
                        <div class="summary-card">
                            <div class="summary-value" id="avgMood">--</div>
                            <div class="summary-label">গড় মুড</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-value" id="bestMood">--</div>
                            <div class="summary-label">সেরা মুড</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-value" id="worstMood">--</div>
                            <div class="summary-label">খারাপ মুড</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-value" id="totalEntries">0</div>
                            <div class="summary-label">মোট এন্ট্রি</div>
                        </div>
                    </div>
                </div>
                
                <!-- History Table -->
                <div class="history-section">
                    <div class="history-header">
                        <div class="stats-title">
                            <i class="fas fa-history"></i> মুড ইতিহাস
                        </div>
                    </div>
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>তারিখ</th>
                                <th>মুড</th>
                                <th>লেবেল</th>
                                <th>ঘুম</th>
                                <th>কার্যক্রম</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="historyBody">
                            <tr><td colspan="6" style="text-align: center;">লোড হচ্ছে...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Custom JS -->
    <script src="../assets/js/mood-tracker.js"></script>
</body>
</html>