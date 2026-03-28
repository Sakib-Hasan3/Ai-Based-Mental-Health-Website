<?php
// dashboard/doctor.php
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

$divisions = [
    'Dhaka', 'Chittagong', 'Rajshahi', 'Khulna', 
    'Barisal', 'Sylhet', 'Rangpur', 'Mymensingh'
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ডাক্তার ডিরেক্টরি - মেন্টোরা</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/doctor.css">
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
                <li class="nav-item"><a href="doctor.php" class="nav-link active"><i class="fas fa-user-md"></i><span>ডাক্তার</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-user-md"></i> ডাক্তার ডিরেক্টরি</h1>
            </div>
            
            <div class="doctor-container">
                <!-- Hero Section -->
                <div class="doctor-hero">
                    <h1>👨‍⚕️ বিশ্বস্ত ডাক্তার খুঁজুন</h1>
                    <p>আপনার এলাকার বিশেষজ্ঞ ডাক্তারদের তথ্য ও ওয়েবসাইট লিংক</p>
                </div>
                
                <!-- Division Selector -->
                <div class="division-selector">
                    <label><i class="fas fa-map-marker-alt"></i> বিভাগ নির্বাচন করুন:</label>
                    <select id="divisionSelect" class="division-dropdown">
                        <option value="">সব বিভাগ</option>
                        <?php foreach ($divisions as $division): ?>
                        <option value="<?php echo $division; ?>"><?php echo $division; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="searchDoctors" class="btn-search">
                        <i class="fas fa-search"></i> ডাক্তার খুঁজুন
                    </button>
                </div>
                
                <!-- Doctors Grid -->
                <div id="doctorsGrid" class="doctors-grid">
                    <div class="loading">
                        <div class="spinner"></div>
                        <p style="margin-top: 15px; color: var(--gray-500);">ডাক্তার লোড হচ্ছে...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/doctor.js"></script>
</body>
</html>