<?php
// auth/register-process.php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if it's POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    // Get data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $date_of_birth = $_POST['date_of_birth'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    
    // =============== ভ্যালিডেশন ===============
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "নাম দিন";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "সঠিক ইমেইল দিন";
    }
    
    if (empty($phone) || !preg_match('/^(?:\+88|01)?\d{11}$/', $phone)) {
        $errors[] = "সঠিক ফোন নম্বর দিন";
    }
    
    if (empty($password) || strlen($password) < 4) {
        $errors[] = "পাসওয়ার্ড কমপক্ষে ৪ অক্ষরের হতে হবে";
    }
    
    // যদি কোন error থাকে
    if (!empty($errors)) {
        echo json_encode([
            'success' => false, 
            'message' => implode("<br>", $errors)
        ]);
        exit();
    }
    
    // =============== ডাটাবেজে সংযোগ ===============
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // চেক করুন ইমেইল আগে থেকে আছে কিনা
    $check_sql = "SELECT id FROM users WHERE email = ? OR phone = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $phone);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'এই ইমেইল বা ফোন নম্বর ইতিমধ্যে নিবন্ধিত আছে'
        ]);
        exit();
    }
    $check_stmt->close();
    
    // =============== পাসওয়ার্ড হ্যাশ করুন ===============
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // =============== ডাটাবেজে সেভ করুন ===============
    $insert_sql = "INSERT INTO users (full_name, email, phone, password, date_of_birth, gender, address, city, is_verified) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
    
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssssssss", 
        $full_name, 
        $email, 
        $phone, 
        $hashed_password, 
        $date_of_birth, 
        $gender, 
        $address, 
        $city
    );
    
    if ($insert_stmt->execute()) {
        $user_id = $conn->insert_id;
        $insert_stmt->close();
        
        // =============== ইমেইল ভেরিফিকেশন টোকেন তৈরি করুন ===============
        $verification_token = generateEmailToken($user_id);
        
        if ($verification_token) {
            // =============== ভেরিফিকেশন ইমেইল পাঠান ===============
            $email_sent = sendVerificationEmail($email, $verification_token, $full_name);
            
            if ($email_sent) {
                echo json_encode([
                    'success' => true,
                    'message' => 'রেজিস্ট্রেশন সফল! আপনার ইমেইলে যাচাইকরণ লিঙ্ক পাঠানো হয়েছে।',
                    'redirect' => 'verify-email-sent.php?email=' . urlencode($email)
                ]);
            } else {
                // ইমেইল পাঠানো না গেলে
                echo json_encode([
                    'success' => true,
                    'message' => 'রেজিস্ট্রেশন সম্পন্ন হয়েছে কিন্তু ইমেইল পাঠাতে সমস্যা হয়েছে। দয়া করে পরে আবার চেষ্টা করুন।',
                    'redirect' => 'login.php'
                ]);
            }
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'রেজিস্ট্রেশন সম্পন্ন হয়েছে। দয়া করে লগইন করুন।',
                'redirect' => 'login.php'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'রেজিস্ট্রেশন ব্যর্থ হয়েছে: ' . $conn->error
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'সার্ভার ত্রুটি: ' . $e->getMessage()
    ]);
}
?>