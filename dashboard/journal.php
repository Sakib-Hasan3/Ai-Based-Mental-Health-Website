<?php
// dashboard/journal.php
session_start();
require_once '../includes/auth_check.php';
require_once '../includes/journal_helper.php';

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

// Categories for dropdown
$categories = [
    ['value' => 'general', 'label' => 'সাধারণ অনুভূতি', 'icon' => 'fa-feather-alt'],
    ['value' => 'work', 'label' => 'কাজ/পড়াশোনা', 'icon' => 'fa-briefcase'],
    ['value' => 'family', 'label' => 'পরিবার', 'icon' => 'fa-home'],
    ['value' => 'relationship', 'label' => 'সম্পর্ক', 'icon' => 'fa-heart'],
    ['value' => 'goals', 'label' => 'লক্ষ্য/প্রেরণা', 'icon' => 'fa-bullseye'],
    ['value' => 'stress', 'label' => 'স্ট্রেস/উদ্বেগ', 'icon' => 'fa-brain'],
    ['value' => 'gratitude', 'label' => 'কৃতজ্ঞতা', 'icon' => 'fa-hands-helping'],
    ['value' => 'reflection', 'label' => 'ব্যক্তিগত প্রতিফলন', 'icon' => 'fa-moon'],
    ['value' => 'other', 'label' => 'অন্যান্য', 'icon' => 'fa-ellipsis-h']
];

// Mood options
$moodOptions = [
    ['score' => 1, 'label' => 'খুব খারাপ', 'emoji' => '😢'],
    ['score' => 2, 'label' => 'খারাপ', 'emoji' => '😞'],
    ['score' => 3, 'label' => 'অসন্তোষজনক', 'emoji' => '😕'],
    ['score' => 4, 'label' => 'মোটামুটি', 'emoji' => '😐'],
    ['score' => 5, 'label' => 'সাধারণ', 'emoji' => '😌'],
    ['score' => 6, 'label' => 'ভালো', 'emoji' => '🙂'],
    ['score' => 7, 'label' => 'খুব ভালো', 'emoji' => '😊'],
    ['score' => 8, 'label' => 'দারুণ', 'emoji' => '😄'],
    ['score' => 9, 'label' => 'চমৎকার', 'emoji' => '😍'],
    ['score' => 10, 'label' => 'অসাধারণ', 'emoji' => '🤩']
];
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>জার্নাল - মেন্টোরা</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/journal.css">
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
                <div class="user-badge">✨ ফ্রি মেম্বর</div>
            </div>
            
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="index.php" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>ড্যাশবোর্ড</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-user-circle"></i>
                        <span>প্রোফাইল</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="mood-tracker.php" class="nav-link">
                        <i class="fas fa-smile"></i>
                        <span>মুড ট্র্যাকার</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="assessment.php" class="nav-link">
                        <i class="fas fa-brain"></i>
                        <span>মানসিক স্বাস্থ্য যাচাই</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="journal.php" class="nav-link active">
                        <i class="fas fa-book"></i>
                        <span>জার্নাল</span>
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
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-header">
                <h1 class="page-title">
                    <i class="fas fa-book"></i>
                    জার্নাল
                </h1>
            </div>
            
            <div class="journal-container">
                <!-- Hero Section -->
                <div class="journal-hero">
                    <h1>📔 আপনার ব্যক্তিগত ডায়েরি</h1>
                    <p>নিজের অনুভূতি, চিন্তা ও অভিজ্ঞতা লিখে রাখুন</p>
                </div>
                
                <!-- Create Entry Card -->
                <div class="create-card">
                    <div class="section-title">
                        <i class="fas fa-pen"></i>
                        নতুন এন্ট্রি লিখুন
                    </div>
                    
                    <form id="journalForm">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> শিরোনাম (ঐচ্ছিক)</label>
                            <input type="text" id="journalTitle" class="form-input" placeholder="আজকের দিনের শিরোনাম...">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-paragraph"></i> আপনার অনুভূতি লিখুন <span style="color: red;">*</span></label>
                            <textarea id="journalContent" class="form-textarea" rows="6" placeholder="আজ আপনার দিনটি কেমন কাটলো? কী অনুভব করছেন? কী ভাবছেন?"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-smile"></i> আজকের মুড (ঐচ্ছিক)</label>
                            <div class="mood-selector" id="moodSelector">
                                <?php foreach ($moodOptions as $mood): ?>
                                <div class="mood-option" data-score="<?php echo $mood['score']; ?>" data-label="<?php echo $mood['label']; ?>">
                                    <?php echo $mood['emoji']; ?> <?php echo $mood['label']; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" id="selectedMoodScore">
                            <input type="hidden" id="selectedMoodLabel">
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> ক্যাটাগরি (ঐচ্ছিক)</label>
                            <select id="journalCategory" class="form-select">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['value']; ?>"><?php echo $cat['label']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn-primary" id="saveJournalBtn">
                            <i class="fas fa-save"></i> জার্নাল সংরক্ষণ করুন
                        </button>
                    </form>
                </div>
                
                <!-- Search & Filter Card -->
                <div class="filter-card">
                    <div class="section-title">
                        <i class="fas fa-search"></i>
                        অনুসন্ধান
                    </div>
                    <div class="filter-row">
                        <input type="text" id="searchInput" class="filter-input" placeholder="শিরোনাম বা কন্টেন্টে খুঁজুন...">
                        <select id="categoryFilter" class="filter-input">
                            <option value="">সব ক্যাটাগরি</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['value']; ?>"><?php echo $cat['label']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="moodFilter" class="filter-input">
                            <option value="">সব মুড</option>
                            <?php for($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?>/10</option>
                            <?php endfor; ?>
                        </select>
                        <select id="sortFilter" class="filter-input">
                            <option value="recent">সর্বশেষ প্রথমে</option>
                            <option value="oldest">পুরনো প্রথমে</option>
                        </select>
                    </div>
                </div>
                
                <!-- Recent Entries Section -->
                <div class="section-title">
                    <i class="fas fa-history"></i>
                    সাম্প্রতিক জার্নাল
                </div>
                <div id="entriesContainer" class="entries-grid"></div>
            </div>
        </div>
    </div>
    
    <!-- Modal for View Entry -->
    <div id="entryModal" class="modal">
        <div class="modal-content">
            <h3 id="modalTitle"></h3>
            <div id="modalDate"></div>
            <div id="modalContent"></div>
            <div id="modalMood" style="margin: 15px 0;"></div>
            <div id="modalCategory" style="margin-bottom: 20px;"></div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button class="btn-primary" id="editEntryBtn" onclick="editEntry(currentEditingId)">
                    <i class="fas fa-edit"></i> সম্পাদনা করুন
                </button>
                <button class="btn-primary" id="deleteEntryBtn" style="background: var(--danger);" onclick="deleteEntry(currentEditingId)">
                    <i class="fas fa-trash-alt"></i> ডিলিট করুন
                </button>
                <button class="btn-primary" id="closeModalBtn" style="background: var(--gray-500);">
                    <i class="fas fa-times"></i> বন্ধ করুন
                </button>
            </div>
        </div>
    </div>
    
    <!-- Custom JS -->
    <script src="../assets/js/journal.js"></script>
</body>
</html>