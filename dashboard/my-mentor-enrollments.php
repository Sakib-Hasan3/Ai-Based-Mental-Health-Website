<?php
// dashboard/my-mentor-enrollments.php
session_start();
require_once '../includes/auth_check.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$user_id = $_SESSION['user_id'];

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
    <title>আমার সেশন - মেন্টোরা</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/mentor.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header"><div class="logo">🧠 মেন্টোরা<span>Mentora</span></div></div>
            <div class="user-info">
                <div class="user-avatar"><img src="../assets/images/avatars/<?php echo htmlspecialchars($profile_image); ?>" alt="User"></div>
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
                <div class="user-badge">✨ ফ্রি মেম্বর</div>
            </div>
         <ul class="nav-menu">
                <li class="nav-item"><a href="/mental%20health/dashboard/index.php" class="nav-link"><i class="fas fa-home"></i><span>ড্যাশবোর্ড</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/profile.php" class="nav-link"><i class="fas fa-user-circle"></i><span>প্রোফাইল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mood-tracker.php" class="nav-link"><i class="fas fa-smile"></i><span>মুড ট্র্যাকার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/assessment.php" class="nav-link"><i class="fas fa-brain"></i><span>মানসিক স্বাস্থ্য যাচাই</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/journal.php" class="nav-link"><i class="fas fa-book"></i><span>জার্নাল</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/mentor.php" class="nav-link active"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/community.php" class="nav-link"><i class="fas fa-users"></i><span>কমিউনিটি</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/doctor.php" class="nav-link"><i class="fas fa-user-md"></i><span>ডাক্তার</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/resources.php" class="nav-link"><i class="fas fa-book-open"></i><span>রিসোর্স</span></a></li>
                <li class="nav-item"><a href="/mental%20health/dashboard/achievements.php" class="nav-link"><i class="fas fa-trophy"></i><span>অ্যাচিভমেন্ট</span></a></li>
                <li class="nav-item"><a href="/mental%20health/auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-calendar-check"></i> আমার সেশন</h1>
            </div>
            
            <div class="mentor-container">
                <div class="mentor-hero">
                    <h1>📅 আপনার বুক করা সেশন</h1>
                    <p>মেন্টরদের সাথে আপনার সেশন ইতিহাস ও স্ট্যাটাস দেখুন</p>
                </div>
                
                <div id="enrollmentsContainer" class="enrollments-container"></div>
            </div>
        </div>
    </div>
    
    <!-- Rating Modal -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-star"></i> আপনার অভিজ্ঞতা রেটিং দিন</h3>
            <form id="ratingForm">
                <div class="form-group">
                    <label>রেটিং</label>
                    <div class="rating-stars-input">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" id="selectedRating">
                </div>
                <div class="form-group">
                    <label>রিভিউ (ঐচ্ছিক)</label>
                    <textarea id="ratingReview" rows="3" placeholder="আপনার অভিজ্ঞতা লিখুন..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-enroll" onclick="submitRating()">সাবমিট করুন</button>
                    <button type="button" class="btn-view" onclick="closeModal()">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/mentor.js"></script>
    <script>
        $(document).ready(function() {
            loadMyEnrollments();
        });
    </script>
</body>
</html>