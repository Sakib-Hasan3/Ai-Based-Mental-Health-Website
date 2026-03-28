<?php
// doctor/api/get-doctors-by-division.php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/api_auth_check.php';
require_once '../config/database.php';

try {
    $division = isset($_GET['division']) ? trim($_GET['division']) : '';

    $conn = Database::getInstance()->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    if ($division) {
        $sql = "SELECT id, name, specialization, division, district, hospital_name, 
                website_url, profile_image, phone, email, experience_years
                FROM doctors 
                WHERE division = ? AND is_active = 1
                ORDER BY name ASC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $division);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT id, name, specialization, division, district, hospital_name, 
                website_url, profile_image, phone, email, experience_years
                FROM doctors 
                WHERE is_active = 1
                ORDER BY name ASC
                LIMIT 20";
        
        $result = $conn->query($sql);
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
    }

    $doctors = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $doctors,
        'count' => count($doctors)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>