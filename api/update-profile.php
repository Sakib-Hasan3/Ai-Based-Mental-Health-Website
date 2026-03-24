<?php
// api/update-profile.php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Database connection
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'mentora_db';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

// Validate inputs
$full_name = trim($input['full_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$date_of_birth = $input['date_of_birth'] ?? null;
$gender = $input['gender'] ?? '';
$address = trim($input['address'] ?? '');
$city = trim($input['city'] ?? '');
$bio = trim($input['bio'] ?? '');

$errors = [];

// Validation
if (empty($full_name)) {
    $errors['full_name'] = 'নাম দিন';
} elseif (strlen($full_name) < 2) {
    $errors['full_name'] = 'নাম কমপক্ষে ২ অক্ষরের হতে হবে';
}

if (empty($email)) {
    $errors['email'] = 'ইমেইল দিন';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'সঠিক ইমেইল দিন';
} else {
    // Check if email exists for other users
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors['email'] = 'এই ইমেইল ইতিমধ্যে নিবন্ধিত আছে';
    }
    $check_stmt->close();
}

if (!empty($phone)) {
    if (!preg_match('/^(?:\+88)?01[0-9]{9}$/', $phone)) {
        $errors['phone'] = 'সঠিক ফোন নম্বর দিন (01XXXXXXXXX)';
    } else {
        // Check if phone exists for other users
        $check_sql = "SELECT id FROM users WHERE phone = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $phone, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors['phone'] = 'এই ফোন নম্বর ইতিমধ্যে নিবন্ধিত আছে';
        }
        $check_stmt->close();
    }
}

// If errors exist
if (!empty($errors)) {
    echo json_encode(['success' => false, 'errors' => $errors]);
    $conn->close();
    exit();
}

// Update user profile
$update_sql = "UPDATE users SET 
                full_name = ?, 
                email = ?, 
                phone = ?, 
                date_of_birth = ?, 
                gender = ?, 
                address = ?, 
                city = ?, 
                bio = ?,
                updated_at = NOW()
                WHERE id = ?";

$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("ssssssssi", 
    $full_name, 
    $email, 
    $phone, 
    $date_of_birth, 
    $gender, 
    $address, 
    $city, 
    $bio, 
    $user_id
);

if ($update_stmt->execute()) {
    // Update session data
    $_SESSION['user_name'] = $full_name;
    $_SESSION['user_email'] = $email;
    
    echo json_encode([
        'success' => true,
        'message' => '✅ প্রোফাইল আপডেট হয়েছে'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'আপডেট করতে ব্যর্থ হয়েছে'
    ]);
}

$update_stmt->close();
$conn->close();
?>