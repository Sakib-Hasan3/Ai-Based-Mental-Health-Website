<?php
// setup-doctors-table.php - Run this file once in your browser to create the doctors table

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // SQL to create doctors table
    $sql = "CREATE TABLE IF NOT EXISTS doctors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        specialization VARCHAR(100) NOT NULL,
        division VARCHAR(50) NOT NULL,
        district VARCHAR(50),
        hospital_name VARCHAR(150),
        website_url VARCHAR(255),
        profile_image VARCHAR(255) DEFAULT 'default-doctor.png',
        phone VARCHAR(20),
        email VARCHAR(100),
        experience_years INT DEFAULT 0,
        is_active BOOLEAN DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_division (division),
        INDEX idx_specialization (specialization)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Doctors table created successfully!<br><br>";
    } else {
        throw new Exception("Error creating table: " . $conn->error);
    }

    // Check if table already has data
    $check_sql = "SELECT COUNT(*) as count FROM doctors";
    $result = $conn->query($check_sql);
    $row = $result->fetch_assoc();
    $existing_count = $row['count'];

    if ($existing_count > 0) {
        echo "⚠️  Table already has " . $existing_count . " doctors. Skipping insert to avoid duplicates.<br><br>";
        echo "<a href='javascript:history.back()'>Go Back</a>";
        exit();
    }

    // Insert sample doctors
    $insert_sql = "INSERT INTO doctors (name, specialization, division, district, hospital_name, website_url, phone, email, experience_years) VALUES 
    ('ডা. আব্দুল্লাহ আল মামুন', 'Cardiology', 'Dhaka', 'Dhaka', 'Square Hospitals Ltd.', 'https://www.squarehospital.com/doctors/dr-abdullah', '01712345678', 'dr.mamun@squarehospital.com', 15),
    ('ডা. ফাতেমা বেগম', 'Gynecology', 'Dhaka', 'Dhaka', 'Apollo Hospitals Dhaka', 'https://www.evercarebd.com/doctors/dr-fatema', '01712345679', 'fatema@evercarebd.com', 12),
    ('ডা. শাহিদ হোসেন', 'Neurology', 'Dhaka', 'Dhaka', 'United Hospital', 'https://www.unitedhospital.com.bd/doctors/dr-shahid', '01712345680', 'shahid@united.com.bd', 18),
    ('ডা. নাসরিন আক্তার', 'Pediatrics', 'Dhaka', 'Dhaka', 'Dhaka Shishu Hospital', 'https://www.dsh.org.bd/doctors/dr-nasrin', '01712345681', 'nasrin@dsh.org.bd', 10),
    ('ডা. কামরুল হাসান', 'Orthopedics', 'Dhaka', 'Dhaka', 'Popular Medical College', 'https://popularmedicalcollege.com.bd/doctors/dr-kamrul', '01712345682', 'kamrul@popular.com.bd', 14),
    ('ডা. সেলিনা পারভীন', 'Dermatology', 'Chittagong', 'Chittagong', 'Chattogram Metropolitan Hospital', 'https://www.cmhctg.com/doctors/dr-selina', '01812345678', 'selina@cmhctg.com', 8),
    ('ডা. রফিকুল ইসলাম', 'Cardiology', 'Chittagong', 'Chittagong', 'National Heart Foundation', 'https://www.nhf.org.bd/doctors/dr-rafiq', '01812345679', 'rafiq@nhf.org.bd', 16),
    ('ডা. তাসলিমা খানম', 'Gynecology', 'Rajshahi', 'Rajshahi', 'Rajshahi Medical College Hospital', 'https://www.rmch.gov.bd/doctors/dr-taslima', '01912345678', 'taslima@rmch.gov.bd', 11),
    ('ডা. মাহবুবুর রহমান', 'Neurology', 'Khulna', 'Khulna', 'Khulna Medical College Hospital', 'https://www.kmch.gov.bd/doctors/dr-mahbub', '01912345679', 'mahbub@kmch.gov.bd', 13),
    ('ডা. শামীমা আক্তার', 'Pediatrics', 'Sylhet', 'Sylhet', 'Sylhet MAG Osmani Medical College', 'https://www.sosmc.gov.bd/doctors/dr-shamima', '01712345690', 'shamima@sosmc.gov.bd', 9),
    ('ডা. জাহিদ হাসান', 'Orthopedics', 'Barisal', 'Barisal', 'Barisal Medical College Hospital', 'https://www.bmch.gov.bd/doctors/dr-zahid', '01712345691', 'zahid@bmch.gov.bd', 7),
    ('ডা. নাজমুল হক', 'Cardiology', 'Rangpur', 'Rangpur', 'Rangpur Medical College Hospital', 'https://www.rmch.gov.bd/doctors/dr-nazmul', '01712345692', 'nazmul@rmch.gov.bd', 12),
    ('ডা. রেহানা পারভীন', 'Dermatology', 'Mymensingh', 'Mymensingh', 'Mymensingh Medical College Hospital', 'https://www.mmch.gov.bd/doctors/dr-rehana', '01712345693', 'rehana@mmch.gov.bd', 6),
    ('ডা. আনোয়ার হোসেন', 'ENT', 'Dhaka', 'Dhaka', 'Anwer Khan Modern Medical College', 'https://www.akmmc.edu.bd/doctors/dr-anwar', '01712345694', 'anwar@akmmc.edu.bd', 20),
    ('ডা. শাহনাজ পারভীন', 'Ophthalmology', 'Chittagong', 'Chittagong', 'Chittagong Eye Infirmary', 'https://www.cei.org.bd/doctors/dr-shahnaz', '01812345680', 'shahnaz@cei.org.bd', 14)";

    if ($conn->query($insert_sql) === TRUE) {
        echo "✅ 15 doctors inserted successfully!<br><br>";
    } else {
        throw new Exception("Error inserting data: " . $conn->error);
    }

    // Verify the data
    $verify_sql = "SELECT COUNT(*) as total_doctors FROM doctors";
    $result = $conn->query($verify_sql);
    $row = $result->fetch_assoc();
    echo "✅ Total doctors in database: " . $row['total_doctors'] . "<br><br>";

    // Show breakdown by division
    $division_sql = "SELECT division, COUNT(*) as doctors_count FROM doctors GROUP BY division ORDER BY division";
    $result = $conn->query($division_sql);
    echo "📍 Doctors by Division:<br>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>Division</th><th>Count</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['division'] . "</td><td>" . $row['doctors_count'] . "</td></tr>";
    }
    echo "</table>";

    echo "<br><br><a href='dashboard/doctor.php'>✅ Go to Doctor Page</a>";
    $conn->close();

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
