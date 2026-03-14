<?php
// auth/verify-email.php
require_once '../config/database.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';
$verified = false;
$message = '';
$user_email = '';

if (empty($token)) {
    $message = 'অবৈধ যাচাইকরণ লিঙ্ক';
} else {
    // Verify the token
    $result = verifyEmailToken($token);
    
    if ($result['success']) {
        $verified = true;
        $message = $result['message'];
        
        // Get user email for display
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();
            $sql = "SELECT email FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $result['user_id']);
            $stmt->execute();
            $query_result = $stmt->get_result();
            if ($row = $query_result->fetch_assoc()) {
                $user_email = $row['email'];
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error fetching user email: " . $e->getMessage());
        }
    } else {
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইমেইল যাচাইকরণ - মেন্টোরা</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    
    <style>
        .verification-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .verification-card {
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .verification-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 40px;
        }
        
        .success-icon {
            background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
            color: white;
        }
        
        .error-icon {
            background: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            color: white;
        }
        
        .verification-card h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #0f172a;
        }
        
        .verification-card p {
            font-size: 16px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 20px;
        }
        
        .email-display {
            background: #f1f5f9;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: 600;
            color: #0d9488;
            word-break: break-all;
        }
        
        .success-text {
            color: #10b981;
            font-size: 18px;
            font-weight: 600;
            margin: 20px 0;
        }
        
        .error-text {
            color: #ef4444;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0;
        }
        
        .button {
            display: inline-block;
            padding: 12px 40px;
            background: linear-gradient(135deg, #0d9488 0%, #0d7a5f 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin-top: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(13, 148, 136, 0.3);
        }
        
        .button-secondary {
            background: #6366f1;
        }
        
        .button-secondary:hover {
            box-shadow: 0 5px 20px rgba(99, 102, 241, 0.3);
        }
        
        .countdown {
            margin-top: 30px;
            color: #64748b;
            font-size: 14px;
        }
        
        .countdown strong {
            color: #0d9488;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <?php if ($verified): ?>
                <div class="verification-icon success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h2>সফল!</h2>
                <p>আপনার ইমেইল সফলভাবে যাচাই হয়েছে।</p>
                
                <?php if (!empty($user_email)): ?>
                    <div class="email-display">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($user_email); ?>
                    </div>
                <?php endif; ?>
                
                <p class="success-text">আপনি এখন মেন্টোরায় সম্পূর্ণভাবে যোগ দিয়েছেন!</p>
                
                <p style="color: #64748b; margin: 20px 0;">
                    আপনার অ্যাকাউন্ট এখন সক্রিয় এবং আপনি লগইন করতে পারেন।
                </p>
                
                <a href="login.php" class="button">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    লগইন করুন
                </a>
                
                <div class="countdown">
                    <p>যদি স্বয়ংক্রিয়ভাবে রিডাইরেক্ট না হয়, <strong id="countdown">5</strong> সেকেন্ড অপেক্ষা করুন...</p>
                </div>
            <?php else: ?>
                <div class="verification-icon error-icon">
                    <i class="fas fa-times"></i>
                </div>
                <h2>যাচাইকরণ ব্যর্থ</h2>
                <p class="error-text"><?php echo htmlspecialchars($message); ?></p>
                
                <p style="color: #64748b; margin: 20px 0;">
                    যদি আপনার সমস্যা হয়, তাহলে আবার রেজিস্ট্রেশন করুন বা সাপোর্ট যোগাযোগ করুন।
                </p>
                
                <a href="register.php" class="button button-secondary">
                    <i class="fas fa-user-plus me-2"></i>
                    আবার রেজিস্ট্রেশন করুন
                </a>
                
                <a href="login.php" class="button" style="margin-left: 10px;">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    লগইন পেজ
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($verified): ?>
    <script>
        let countdownValue = 5;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(() => {
            countdownValue--;
            if (countdownElement) {
                countdownElement.textContent = countdownValue;
            }
            
            if (countdownValue <= 0) {
                clearInterval(countdownInterval);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>
