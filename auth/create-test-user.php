<?php
// auth/create-test-user.php - টেস্ট ইউজার তৈরি করুন

require_once '../config/database.php';
require_once '../includes/functions.php';

// টেস্ট ক্রেডেনশিয়াল
$test_email = 'test@mentora.com';
$test_phone = '01612345678';
$test_password = 'Test@123456';
$test_name = 'টেস্ট ইউজার';

// ডাটাবেস সংযোগ পান
$db = getDB();
$conn = $db->getConnection();

// পূর্ববর্তী টেস্ট ইউজার চেক করুন
$check = $db->getSingle(
    "SELECT id FROM users WHERE email = ? OR phone = ?",
    [$test_email, $test_phone]
);

if ($check) {
    // আপডেট করুন
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    $update_sql = "UPDATE users SET password = ?, is_verified = 1, is_active = 1, login_attempts = 0 
                   WHERE email = ? OR phone = ?";
    
    if ($conn->prepare($update_sql)) {
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sss", $hashed_password, $test_email, $test_phone);
        
        if ($stmt->execute()) {
            $result = "✓ টেস্ট ইউজার আপডেট করা হয়েছে";
        } else {
            $result = "✗ আপডেট ব্যর্থ: " . $stmt->error;
        }
        $stmt->close();
    }
} else {
    // নতুন ইউজার তৈরি করুন
    $hashed_password = password_hash($test_password, PASSWORD_DEFAULT);
    $insert_sql = "INSERT INTO users (full_name, email, phone, password, user_type, is_verified, is_active, login_attempts) 
                   VALUES (?, ?, ?, ?, 'user', 1, 1, 0)";
    
    if ($conn->prepare($insert_sql)) {
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssss", $test_name, $test_email, $test_phone, $hashed_password);
        
        if ($stmt->execute()) {
            $result = "✓ নতুন টেস্ট ইউজার তৈরি করা হয়েছে";
        } else {
            $result = "✗ তৈরি ব্যর্থ: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>টেস্ট ইউজার তৈরি করুন</title>
    <style>
        body {
            font-family: 'Noto Sans Bengali', Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .info-box {
            background: #f0f9ff;
            border: 2px solid #0284c7;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h3 {
            margin-top: 0;
            color: #0284c7;
        }
        .credentials {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .credential-item {
            padding: 12px;
            background: #f5f5f5;
            border-radius: 6px;
            border-left: 4px solid #0284c7;
        }
        .credential-item label {
            display: block;
            font-weight: 600;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .credential-item .value {
            font-size: 16px;
            color: #333;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .copy-btn {
            background: #0284c7;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
        }
        .copy-btn:hover {
            background: #0369a1;
        }
        .success {
            background: #dcfce7;
            border: 2px solid #22c55e;
            color: #166534;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .error {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
        }
        .btn-primary {
            background: #0284c7;
            color: white;
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #333;
        }
        .btn-secondary:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>টেস্ট ইউজার সেটআপ</h1>
        
        <?php if (isset($result)): ?>
            <div class="<?php echo strpos($result, '✓') !== false ? 'success' : 'error'; ?>">
                <?php echo $result; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h3>📋 লগইন তথ্য</h3>
            <div class="credentials">
                <div class="credential-item">
                    <label>ইমেইল:</label>
                    <div class="value"><?php echo $test_email; ?></div>
                </div>
                
                <div class="credential-item">
                    <label>পাসওয়ার্ড:</label>
                    <div class="value"><?php echo $test_password; ?></div>
                </div>
                
                <div class="credential-item">
                    <label>ফোন (বিকল্প):</label>
                    <div class="value"><?php echo $test_phone; ?></div>
                </div>
            </div>
        </div>
        
        <div class="info-box">
            <h3>✓ স্ট্যাটাস</h3>
            <ul style="margin: 0; padding-left: 20px; color: #333;">
                <li>ইমেইল ভেরিফাইড: ✓</li>
                <li>অ্যাকাউন্ট সক্রিয়: ✓</li>
                <li>লগইন চেষ্টা: ০</li>
            </ul>
        </div>
        
        <div class="action-buttons">
            <a href="login.php" class="btn btn-primary">লগইন করুন</a>
            <a href="../index.php" class="btn btn-secondary">হোম পেজ</a>
        </div>
    </div>
</body>
</html>
