<?php
// dashboard/achievements.php
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
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';
$conn = new mysqli($host, $user, $pass, $dbname);
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

$categories = [
    ['value' => 'all', 'name' => 'সব', 'icon' => 'fa-trophy'],
    ['value' => 'mood_count', 'name' => 'মুড', 'icon' => 'fa-smile'],
    ['value' => 'journal_count', 'name' => 'জার্নাল', 'icon' => 'fa-book'],
    ['value' => 'streak_days', 'name' => 'স্ট্রিক', 'icon' => 'fa-calendar-alt'],
    ['value' => 'assessment_count', 'name' => 'অ্যাসেসমেন্ট', 'icon' => 'fa-brain'],
    ['value' => 'community_posts', 'name' => 'কমিউনিটি', 'icon' => 'fa-users'],
    ['value' => 'session_count', 'name' => 'মেন্টর', 'icon' => 'fa-chalkboard-teacher']
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাচিভমেন্ট - মেন্টোরা</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/achievements.css">
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
                <li class="nav-item"><a href="achievements.php" class="nav-link active"><i class="fas fa-trophy"></i><span>অ্যাচিভমেন্ট</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-trophy"></i> অ্যাচিভমেন্ট</h1>
            </div>
            
            <div class="achievements-container">
                <!-- Hero Section -->
                <div class="achievements-hero">
                    <h1>🏆 আপনার অগ্রগতি ও পুরস্কার</h1>
                    <p>অ্যাচিভমেন্ট সম্পূর্ণ করুন এবং পয়েন্ট অর্জন করুন</p>
                </div>
                
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-star"></i></div>
                        <div class="stat-value" id="totalPoints">0</div>
                        <div class="stat-label">মোট পয়েন্ট</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="stat-value" id="completedCount">0</div>
                        <div class="stat-label">সম্পন্ন হয়েছে</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-gift"></i></div>
                        <div class="stat-value" id="claimedCount">0</div>
                        <div class="stat-label">ক্লেইম করা হয়েছে</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-lock"></i></div>
                        <div class="stat-value" id="lockedCount">0</div>
                        <div class="stat-label">লক করা</div>
                    </div>
                </div>
                
                <!-- Category Tabs -->
                <div class="category-tabs">
                    <?php foreach ($categories as $cat): ?>
                    <button class="category-btn <?php echo $cat['value'] == 'all' ? 'active' : ''; ?>" data-category="<?php echo $cat['value']; ?>">
                        <i class="fas <?php echo $cat['icon']; ?>"></i> <?php echo $cat['name']; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Achievements Grid -->
                <div id="achievementsGrid" class="achievements-grid">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>অ্যাচিভমেন্ট লোড হচ্ছে...</p>
                    </div>
                </div>
                
                <!-- Recent Achievements -->
                <div class="recent-section">
                    <div class="section-title">
                        <i class="fas fa-clock"></i> সাম্প্রতিক অ্যাচিভমেন্ট
                    </div>
                    <div id="recentList" class="recent-list"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/achievements.js"></script>
</body>
</html>