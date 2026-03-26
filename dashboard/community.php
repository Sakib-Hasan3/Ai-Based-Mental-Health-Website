<?php
// dashboard/community.php
session_start();
require_once '../includes/auth_check.php';

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
    ['value' => 'stress', 'name' => 'স্ট্রেস', 'icon' => 'fa-brain', 'color' => '#ef4444'],
    ['value' => 'anxiety', 'name' => 'উদ্বেগ', 'icon' => 'fa-heartbeat', 'color' => '#f59e0b'],
    ['value' => 'study', 'name' => 'পড়াশোনা', 'icon' => 'fa-book', 'color' => '#10b981'],
    ['value' => 'family', 'name' => 'পরিবার', 'icon' => 'fa-home', 'color' => '#6366f1'],
    ['value' => 'relationship', 'name' => 'সম্পর্ক', 'icon' => 'fa-heart', 'color' => '#ec489a'],
    ['value' => 'motivation', 'name' => 'প্রেরণা', 'icon' => 'fa-fire', 'color' => '#f97316'],
    ['value' => 'sleep', 'name' => 'ঘুম', 'icon' => 'fa-moon', 'color' => '#8b5cf6'],
    ['value' => 'general', 'name' => 'সাধারণ', 'icon' => 'fa-comments', 'color' => '#6b7280']
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কমিউনিটি - মেন্টোরা</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/community.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="logo">🧠 মেন্টোরা<span>Mentora</span></div>
            </div>
            <div class="user-info">
                <div class="user-avatar"><img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="User"></div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                <div class="user-badge">✨ ফ্রি মেম্বর</div>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="mood-tracker.php" class="nav-link"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="assessment.php" class="nav-link"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="journal.php" class="nav-link"><i class="fas fa-book"></i><span>জার্নাল</span></a></li>
                <li class="nav-item"><a href="mentor.php" class="nav-link"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="community.php" class="nav-link active"><i class="fas fa-users"></i><span>কমিউনিটি</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-users"></i> কমিউনিটি</h1>
            </div>
            
            <div class="community-container">
                <!-- Hero Section -->
                <div class="community-hero">
                    <h1>👥 নিরাপদ কমিউনিটি স্পেস</h1>
                    <p>একটি নিরাপদ, সহায়ক পরিবেশ যেখানে আপনি আপনার অনুভূতি শেয়ার করতে পারেন</p>
                </div>
                
                <!-- Create Post Card -->
                <div class="create-post-card">
                    <div class="create-post-header">
                        <div class="avatar">
                            <img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="<?php echo htmlspecialchars($user_name); ?>">
                        </div>
                        <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                    </div>
                    <textarea id="postContent" class="post-content-input" placeholder="আপনার অনুভূতি, চিন্তা বা প্রশ্ন লিখুন..."></textarea>
                    <div class="post-options">
                        <select id="postCategory" class="category-select">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['value']; ?>"><?php echo $cat['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label class="anonymous-option">
                            <input type="checkbox" id="anonymousPost">
                            <span>বেনামী হিসেবে পোস্ট করুন</span>
                        </label>
                        <button class="btn-post" id="createPostBtn">
                            <i class="fas fa-paper-plane"></i> পোস্ট করুন
                        </button>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <button class="filter-btn active" data-filter="recent">সর্বশেষ</button>
                    <button class="filter-btn" data-filter="popular">জনপ্রিয়</button>
                    <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="category" data-category="<?php echo $cat['value']; ?>">
                        <?php echo $cat['name']; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Posts Feed -->
                <div id="postsFeed" class="posts-feed">
                    <div class="empty-state">লোড হচ্ছে...</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/community.js"></script>
</body>
</html>