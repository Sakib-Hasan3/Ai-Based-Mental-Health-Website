<?php
// auth/verify-email-sent.php
require_once("../db.php");

$email = $_GET['email'] ?? '';

// Get the verification token for this email
$token = '';
if($email) {
    $stmt = $conn->prepare("
        SELECT ev.token FROM email_verifications ev
        JOIN users u ON ev.user_id = u.id
        WHERE u.email = ? AND ev.verified_at IS NULL AND ev.expires_at > NOW()
        ORDER BY ev.created_at DESC LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        $token = $row['token'];
    }
}

$verification_url = $token ? "http://" . $_SERVER['HTTP_HOST'] . "/auth/verify-email.php?token=" . $token : '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ইমেইল যাচাইকরণ পাঠানো হয়েছে - মেন্টোরা</title>
    
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
            background: linear-gradient(135deg, #0d9488 0%, #10b981 100%);
            color: white;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            background: linear-gradient(135deg, #fef3c7 0%, #fbeee6 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
            font-weight: 600;
            color: #d97706;
            word-break: break-all;
            border-left: 4px solid #d97706;
        }
        
        .steps {
            text-align: left;
            background: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            color: #64748b;
        }
        
        .steps ol {
            margin: 10px 0;
            padding-left: 20px;
        }
        
        .steps li {
            margin: 10px 0;
            line-height: 1.6;
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
            background: #94a3b8;
            margin-left: 10px;
        }
        
        .button-secondary:hover {
            box-shadow: 0 5px 20px rgba(148, 163, 184, 0.3);
        }
        
        .info-box {
            background: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
            color: #1e40af;
            font-size: 14px;
        }
        
        .resend-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 14px;
        }
        
        .resend-button {
            background: #6366f1;
            padding: 10px 20px;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
        }
        
        .resend-button:hover {
            background: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="verification-card">
            <div class="verification-icon">
                <i class="fas fa-envelope"></i>
            </div>
            
            <h2>ইমেইল যাচাইকরণ</h2>
            
            <p>আপনার অ্যাকাউন্ট সম্পূর্ণ করতে, আপনার ইমেইলে পাঠানো যাচাইকরণ লিঙ্কে ক্লিক করুন।</p>
            
            <div class="email-display">
                <i class="fas fa-envelope-open-text me-2"></i>
                <?php echo htmlspecialchars($email); ?>
            </div>
            
            <div class="steps">
                <strong style="color: #0f172a;">পরবর্তী ধাপ:</strong>
                <ol>
                    <li>আপনার ইনবক্স (বা স্প্যাম ফোল্ডার) চেক করুন</li>
                    <li>"মেন্টোরা - ইমেইল যাচাই করুন" বার্তা খুঁজুন</li>
                    <li>ইমেইলে থাকা লিঙ্কে ক্লিক করুন</li>
                    <li>আপনার অ্যাকাউন্ট সক্রিয় হবে এবং লগইন করতে পারবেন</li>
                </ol>
            </div>
            
            <div class="info-box">
                <i class="fas fa-info-circle me-2"></i>
                <strong>নোট:</strong> যাচাইকরণ লিঙ্কটি ২৪ ঘন্টার জন্য বৈধ থাকবে।
            </div>
            
            <p style="color: #64748b; margin: 20px 0;">
                ইমেইল পাননি? আপনার স্প্যাম বা প্রচার ফোল্ডার চেক করুন। অথবা নীচে ক্লিক করুন।
            </p>
            
            <?php if($verification_url): ?>
                <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;">
                    <p style="color: #065f46; margin: 0 0 15px 0;">
                        <strong>🔗 আপনার যাচাইকরণ লিঙ্ক (Development Mode):</strong>
                    </p>
                    <p style="background: white; padding: 12px; border-radius: 5px; word-break: break-all; color: #0d9488; font-size: 12px; margin: 10px 0;">
                        <?php echo htmlspecialchars($verification_url); ?>
                    </p>
                    <p style="color: #065f46; margin: 10px 0 0 0; font-size: 13px;">
                        নীচে ক্লিক করুন বা উপরের লিঙ্কটি ব্রাউজারে কপি করুন:
                    </p>
                </div>
                
                <a href="<?php echo htmlspecialchars($verification_url); ?>" class="button" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-check-circle me-2"></i>
                    এখনই ইমেইল যাচাই করুন
                </a>
            <?php else: ?>
                <div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p style="color: #7f1d1d; margin: 0;">
                        <strong>⚠️ ত্রুটি:</strong> যাচাইকরণ লিঙ্ক পাওয়া যায়নি। এখনই লগইন করার চেষ্টা করুন।
                    </p>
                </div>
            <?php endif; ?>
            
            <a href="login.php" class="button">
                <i class="fas fa-sign-in-alt me-2"></i>
                এখনই লগইন করুন
            </a>
            
            <a href="register.php" class="button button-secondary">
                <i class="fas fa-arrow-left me-2"></i>
                পেছনে যান
            </a>
            
            <div class="resend-section">
                <p>যদি ইমেইল অভিগম্যতায় সমস্যা হয়?</p>
                <button class="resend-button" onclick="alert('পুনরায় পাঠানোর বৈশিষ্ট্য শীঘ্রই আসছে')">
                    <i class="fas fa-redo me-2"></i>
                    যাচাইকরণ ইমেইল পুনরায় পাঠান
                </button>
            </div>
        </div>
    </div>
</body>
</html>
