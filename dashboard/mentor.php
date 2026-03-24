<?php
// dashboard/mentor.php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/mentor_helper.php';

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

$specialties = getSpecialties();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>মেন্টর - মেন্টোরা</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/mentor.css">
</head>
<body>
    <div class="dashboard-container">
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
                <li class="nav-item"><a href="mentor.php" class="nav-link active"><i class="fas fa-chalkboard-teacher"></i><span>মেন্টর</span></a></li>
                <li class="nav-item"><a href="my-mentor-enrollments.php" class="nav-link"><i class="fas fa-calendar-check"></i><span>আমার সেশন</span></a></li>
                <li class="nav-item"><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i><span>লগআউট</span></a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title"><i class="fas fa-chalkboard-teacher"></i> মেন্টর</h1>
            </div>
            
            <div class="mentor-container">
                <div class="mentor-hero">
                    <h1>👨‍🏫 আপনার পথপ্রদর্শক</h1>
                    <p>বিশেষজ্ঞ মেন্টরদের সাথে ক্যারিয়ার ও জীবন দক্ষতা অর্জন করুন</p>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label><i class="fas fa-tag"></i> স্পেশালিটি</label>
                            <select id="specialtyFilter" class="filter-select">
                                <option value="">সব</option>
                                <?php foreach ($specialties as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-search"></i> খুঁজুন</label>
                            <input type="text" id="searchFilter" class="filter-input" placeholder="নাম বা বিশেষত্ব...">
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-star"></i> রেটিং</label>
                            <select id="ratingFilter" class="filter-select">
                                <option value="">সব</option>
                                <option value="4">৪+ স্টার</option>
                                <option value="3">৩+ স্টার</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label><i class="fas fa-check-circle"></i> উপলব্ধতা</label>
                            <select id="availabilityFilter" class="filter-select">
                                <option value="">সব</option>
                                <option value="1">উপলব্ধ</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button id="applyFilter" class="btn-filter"><i class="fas fa-filter"></i> ফিল্টার</button>
                            <button id="resetFilter" class="btn-filter" style="background: var(--gray-500);"><i class="fas fa-undo"></i> রিসেট</button>
                        </div>
                    </div>
                </div>
                
                <!-- Mentors Grid -->
                <div id="mentorsGrid" class="mentors-grid"></div>
            </div>
        </div>
    </div>
    
    <!-- Enroll Modal -->
    <div id="enrollModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-calendar-plus"></i> সেশন বুক করুন</h3>
            <form id="enrollForm">
                <div class="form-group">
                    <label>মেন্টর</label>
                    <p id="enrollMentorName" style="font-weight: 600; color: var(--primary);"></p>
                </div>
                <div class="form-group">
                    <label>সেশন ফি</label>
                    <p id="enrollPrice" style="font-weight: 600;"></p>
                </div>
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
                    <label><i class="fas fa-tag"></i> বিষয় (ঐচ্ছিক)</label>
                    <input type="text" id="sessionTopic" placeholder="আপনি কী বিষয়ে আলোচনা করতে চান?">
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-enroll" onclick="submitEnrollment()" id="confirmEnrollBtn">নিশ্চিত করুন</button>
                    <button type="button" class="btn-view" onclick="closeModal()">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Rating Modal -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <h3><i class="fas fa-star"></i> মেন্টরকে রেটিং দিন</h3>
            <form id="ratingForm">
                <div class="form-group">
                    <label>রেটিং *</label>
                    <div class="rating-stars-input">
                        <i class="far fa-star" data-rating="1"></i>
                        <i class="far fa-star" data-rating="2"></i>
                        <i class="far fa-star" data-rating="3"></i>
                        <i class="far fa-star" data-rating="4"></i>
                        <i class="far fa-star" data-rating="5"></i>
                    </div>
                    <input type="hidden" id="selectedRating" value="">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-comment"></i> মন্তব্য (ঐচ্ছিক)</label>
                    <textarea id="ratingReview" placeholder="আপনার অভিজ্ঞতা শেয়ার করুন..." rows="4"></textarea>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn-enroll" onclick="submitRating()">জমা দিন</button>
                    <button type="button" class="btn-view" onclick="closeModal()">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/mentor.js"></script>
</body>
</html>