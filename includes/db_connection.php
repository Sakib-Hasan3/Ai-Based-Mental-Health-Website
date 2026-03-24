<?php
// db_connection.php - ডাটাবেজ কানেকশন ফাইল

// ডাটাবেজ কনফিগারেশন
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP এ ডিফল্ট ইউজার root
define('DB_PASS', '');            // XAMPP এ ডিফল্ট পাসওয়ার্ড খালি
define('DB_NAME', 'mentora_db');  // আপনার ডাটাবেজের নাম

// MySQLi কানেকশন তৈরি
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// কানেকশন চেক
if (!$conn) {
    die("সংযোগ ব্যর্থ: " . mysqli_connect_error());
}

// UTF-8 চারসেট সেট করুন (বাংলা সাপোর্টের জন্য)
mysqli_set_charset($conn, "utf8mb4");

// PDO কানেকশনও তৈরি করুন (যদি PDO ব্যবহার করতে চান)
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    // PDO না থাকলে শুধু MySQLi ব্যবহার করবেন
    // error_log("PDO Connection failed: " . $e->getMessage());
}

// ফাংশন: টেবিল তৈরি করার জন্য
function createMentalHealthTable($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS mental_health_assessments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        assessment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        gender VARCHAR(20),
        occupation VARCHAR(50),
        self_employed VARCHAR(10),
        family_history VARCHAR(10),
        days_indoors VARCHAR(50),
        growing_stress VARCHAR(10),
        changes_habits VARCHAR(10),
        mental_health_history VARCHAR(10),
        mood_swings VARCHAR(20),
        coping_struggles VARCHAR(10),
        work_interest VARCHAR(10),
        social_weakness VARCHAR(10),
        mental_health_interview VARCHAR(10),
        care_options VARCHAR(20),
        prediction_result VARCHAR(30),
        risk_percentage DECIMAL(5,2),
        recommendation TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_assessment_date (assessment_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $sql)) {
        return true;
    } else {
        error_log("Table creation failed: " . mysqli_error($conn));
        return false;
    }
}

// টেবিল তৈরি করুন (যদি না থাকে)
createMentalHealthTable($conn);

// নোট: এখানে `$conn` ভেরিয়েবল ব্যবহার করুন যেখানে MySQLi দরকার
// এবং `$pdo` ভেরিয়েবল ব্যবহার করুন যেখানে PDO দরকার
?>