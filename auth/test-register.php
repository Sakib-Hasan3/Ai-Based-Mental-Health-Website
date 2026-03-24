<?php
// auth/test-register.php - For debugging registration issues

require_once '../config/database.php';
require_once '../includes/functions.php';

?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>রেজিস্ট্রেশন টেস্ট</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #0d9488; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        button { background: #0d9488; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0d7a5f; }
        input { padding: 10px; margin: 5px 0; width: 100%; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>রেজিস্ট্রেশন সিস্টেম টেস্ট</h1>
        
        <div class="section">
            <h2>সার্ভার চেক</h2>
            <div class="<?php echo (isset($_SERVER['REQUEST_METHOD'])) ? 'success' : 'error'; ?>">
                ✓ PHP চলছে
            </div>
            <div class="<?php echo (class_exists('Database')) ? 'success' : 'error'; ?>">
                <?php echo (class_exists('Database')) ? '✓' : '✗'; ?> ডাটাবেস ক্লাস লোড হয়েছে
            </div>
            <div class="<?php echo (function_exists('generateCSRFToken')) ? 'success' : 'error'; ?>">
                <?php echo (function_exists('generateCSRFToken')) ? '✓' : '✗'; ?> ফাংশন লোড হয়েছে
            </div>
        </div>
        
        <div class="section">
            <h2>ডাটাবেস টেস্ট</h2>
            <?php
            try {
                $db = Database::getInstance();
                $conn = $db->getConnection();
                
                if ($conn) {
                    echo '<div class="success">✓ ডাটাবেস সংযোগ সফল</div>';
                    
                    // Check tables
                    $tables = ['users', 'email_verifications'];
                    foreach ($tables as $table) {
                        $result = $conn->query("SHOW TABLES LIKE '$table'");
                        if ($result->num_rows > 0) {
                            echo '<div class="success">✓ ' . $table . ' টেবিল আছে</div>';
                        } else {
                            echo '<div class="error">✗ ' . $table . ' টেবিল নেই</div>';
                        }
                    }
                } else {
                    echo '<div class="error">✗ ডাটাবেস সংযোগ ব্যর্থ</div>';
                }
            } catch (Exception $e) {
                echo '<div class="error">✗ ত্রুটি: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>
        </div>
        
        <div class="section">
            <h2>টেস্ট রেজিস্ট্রেশন</h2>
            <p>নিচের ফর্মটি আপনার সিস্টেম টেস্ট করতে ব্যবহার করুন:</p>
            
            <form method="POST" action="register-process.php">
                <label>পূর্ণ নাম:</label>
                <input type="text" name="full_name" value="টেস্ট ইউজার" required>
                
                <label>ইমেইল:</label>
                <input type="email" name="email" value="test<?php echo time(); ?>@example.com" required>
                
                <label>ফোন:</label>
                <input type="tel" name="phone" value="01701234567" required>
                
                <label>পাসওয়ার্ড:</label>
                <input type="password" name="password" value="test1234" required>
                
                <label>পাসওয়ার্ড নিশ্চিত করুন:</label>
                <input type="password" name="confirm_password" value="test1234" required>
                
                <label>জন্ম তারিখ:</label>
                <input type="date" name="date_of_birth" value="2000-01-01" required>
                
                <label>লিঙ্গ:</label>
                <select name="gender" required>
                    <option value="male">পুরুষ</option>
                    <option value="female">মহিলা</option>
                    <option value="other">অন্যান্য</option>
                </select>
                
                <label>ঠিকানা:</label>
                <input type="text" name="address" value="ঢাকা" required>
                
                <label>শহর:</label>
                <input type="text" name="city" value="ঢাকা" required>
                
                <label>
                    <input type="checkbox" name="terms" checked required>
                    নিয়মাবলী গ্রহণ করছি
                </label>
                
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <br><br>
                <button type="submit">রেজিস্টার করুন (FORM)</button>
            </form>
        </div>
        
        <div class="section">
            <h2>AJAX টেস্ট</h2>
            <p>AJAX দিয়ে টেস্ট করতে এই বাটনে ক্লিক করুন:</p>
            <button onclick="testAjax()">AJAX টেস্ট চালান</button>
            <div id="ajaxResult"></div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function testAjax() {
            const data = {
                full_name: 'টেস্ট ইউজার ' + new Date().getTime(),
                email: 'test' + new Date().getTime() + '@example.com',
                phone: '01701234567',
                password: 'test1234',
                confirm_password: 'test1234',
                date_of_birth: '2000-01-01',
                gender: 'male',
                address: 'ঢাকা',
                city: 'ঢাকা',
                terms: 'on',
                csrf_token: '<?php echo generateCSRFToken(); ?>'
            };
            
            console.log('Sending data:', data);
            
            $.ajax({
                url: 'register-process.php',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    console.log('Success response:', response);
                    document.getElementById('ajaxResult').innerHTML = 
                        '<div style="color: green;"><h3>সফল!</h3><pre>' + JSON.stringify(response, null, 2) + '</pre></div>';
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    document.getElementById('ajaxResult').innerHTML = 
                        '<div style="color: red;"><h3>ত্রুটি!</h3><p>স্ট্যাটাস: ' + status + '</p><p>ত্রুটি: ' + error + '</p><pre>' + xhr.responseText + '</pre></div>';
                }
            });
        }
    </script>
</body>
</html>
