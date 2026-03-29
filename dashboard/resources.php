<?php
// dashboard/resources.php
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

$filterTypes = [
    ['type' => 'all', 'name' => 'সব', 'icon' => 'fa-th-large'],
    ['type' => 'article', 'name' => 'আর্টিকেল', 'icon' => 'fa-newspaper'],
    ['type' => 'video', 'name' => 'ভিডিও', 'icon' => 'fa-video'],
    ['type' => 'pdf', 'name' => 'পিডিএফ', 'icon' => 'fa-file-pdf'],
    ['type' => 'breathing', 'name' => 'শ্বাস-প্রশ্বাস', 'icon' => 'fa-lungs'],
    ['type' => 'meditation', 'name' => 'মেডিটেশন', 'icon' => 'fa-head-side-medical'],
    ['type' => 'helpline', 'name' => 'হেল্পলাইন', 'icon' => 'fa-phone-alt']
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রিসোর্স লাইব্রেরি - মেন্টোরা</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/resources.css">
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
                <li class="nav-item"><a href="resources.php" class="nav-link active"><i class="fas fa-book-open"></i><span>রিসোর্স</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-book-open"></i> রিসোর্স লাইব্রেরি</h1>
            </div>
            
            <div class="resources-container">
                <!-- Hero Section -->
                <div class="resources-hero">
                    <h1>📚 মানসিক স্বাস্থ্য রিসোর্স লাইব্রেরি</h1>
                    <p>নিরাপদ ও সহায়ক কন্টেন্ট - আপনার মানসিক সুস্থতার সঙ্গী</p>
                </div>
                
                <!-- Search Section -->
                <div class="search-section">
                    <div class="search-box">
                        <input type="text" id="searchInput" class="search-input" placeholder="রিসোর্স খুঁজুন (টাইটেল, বিবরণ, ক্যাটাগরি)...">
                        <button id="searchBtn" class="btn-search">
                            <i class="fas fa-search"></i> খুঁজুন
                        </button>
                    </div>
                </div>
                
                <!-- Filter Tabs -->
                <div class="filter-tabs">
                    <?php foreach ($filterTypes as $filter): ?>
                    <button class="filter-btn <?php echo $filter['type'] == 'all' ? 'active' : ''; ?>" data-type="<?php echo $filter['type']; ?>">
                        <i class="fas <?php echo $filter['icon']; ?>"></i> <?php echo $filter['name']; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <!-- Emergency Banner (always visible) -->
                <div class="emergency-banner">
                    <div class="emergency-info">
                        <h3><i class="fas fa-exclamation-triangle"></i> জরুরি মানসিক স্বাস্থ্য সহায়তা</h3>
                        <p>২৪/৭ মানসিক স্বাস্থ্য সংকটে কল করুন</p>
                    </div>
                    <div class="emergency-numbers">
                        <a href="tel:01977855055" class="emergency-number"><i class="fas fa-phone-alt"></i> 01977-855055</a>
                        <a href="tel:01712555555" class="emergency-number"><i class="fas fa-phone-alt"></i> 01712-555555</a>
                    </div>
                </div>
                
                <!-- Resources Grid -->
                <div id="resourcesGrid" class="resources-grid">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p style="margin-top: 15px;">রিসোর্স লোড হচ্ছে...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/resources.js"></script>
</body>
</html>