<?php
/**
 * api/predict.php - Mental Health Assessment Prediction
 * 
 * This endpoint receives assessment data from the frontend and sends it to the
 * ML backend server (ml_server.py) running on port 5000 for prediction.
 * 
 * Fallback: If the ML server is not running, uses rule-based calculation
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log every request
error_log("[PREDICT.PHP] " . date('Y-m-d H:i:s') . " | Method: " . $_SERVER['REQUEST_METHOD']);
error_log("[PREDICT.PHP] User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("[PREDICT.PHP] ERROR: User not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit();
}

// Get POST data from both JSON and form data
$input = null;

// Try JSON first
$rawInput = file_get_contents('php://input');
if ($rawInput) {
    $input = json_decode($rawInput, true);
}

// Fallback to $_POST
if (!$input || empty($input)) {
    $input = $_POST;
}

// Validate input
if (!$input || empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No assessment data received']);
    exit();
}

error_log("[PREDICT.PHP] Data received. Fields: " . implode(', ', array_keys($input)));

// Try Flask ML Server first
$flaskResponse = callFlaskAPI($input);

if ($flaskResponse !== null) {
    // Flask server responded successfully
    error_log("[PREDICT.PHP] ✅ Flask ML Server response received");
    http_response_code(200);
    echo json_encode($flaskResponse);
    exit();
}

// Fallback: Rule-based calculation if Flask server is unavailable
error_log("[PREDICT.PHP] ⚠️ Using fallback rule-based calculation");
$result = calculateFallbackRisk($input);
http_response_code(200);
echo json_encode(['success' => true, 'data' => $result, 'method' => 'fallback', 'warning' => 'ML server not available']);
exit();

/**
 * Call Flask ML API for prediction
 * Server should be running on http://localhost:5000
 */
function callFlaskAPI($input) {
    $flaskUrl = 'http://127.0.0.1:5000/api/predict';
    
    // Build request data with proper field names
    $data = [
        'Gender' => $input['gender'] ?? '',
        'Occupation' => $input['occupation'] ?? '',
        'self_employed' => $input['self_employed'] ?? '',
        'family_history' => $input['family_history'] ?? '',
        'Days_Indoors' => $input['days_indoors'] ?? '',
        'Growing_Stress' => $input['growing_stress'] ?? '',
        'Changes_Habits' => $input['changes_habits'] ?? '',
        'Mental_Health_History' => $input['mental_health_history'] ?? '',
        'Mood_Swings' => $input['mood_swings'] ?? '',
        'Coping_Struggles' => $input['coping_struggles'] ?? '',
        'Work_Interest' => $input['work_interest'] ?? '',
        'Social_Weakness' => $input['social_weakness'] ?? '',
        'mental_health_interview' => $input['mental_health_interview'] ?? '',
        'care_options' => $input['care_options'] ?? ''
    ];
    
    $jsonData = json_encode($data);
    
    error_log("[PREDICT.PHP] 🔄 Calling Flask API at: $flaskUrl");
    error_log("[PREDICT.PHP] Sending: " . implode(', ', array_keys($data)));
    
    // Setup cURL for better error handling
    $ch = curl_init($flaskUrl);
    
    if ($ch === false) {
        error_log("[PREDICT.PHP] ❌ Failed to initialize cURL");
        return null;
    }
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_VERBOSE => false
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Check for cURL errors
    if ($error) {
        error_log("[PREDICT.PHP] ❌ cURL error: $error");
        return null;
    }
    
    // Check HTTP status code
    if ($httpCode !== 200) {
        error_log("[PREDICT.PHP] ❌ Flask returned HTTP $httpCode");
        error_log("[PREDICT.PHP] Response: " . substr($response, 0, 200));
        return null;
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    if ($result === null) {
        error_log("[PREDICT.PHP] ❌ Failed to parse Flask response as JSON");
        error_log("[PREDICT.PHP] Raw response: " . substr($response, 0, 200));
        return null;
    }
    
    // Check if response indicates success
    if (isset($result['success']) && $result['success'] === true) {
        error_log("[PREDICT.PHP] ✅ Flask returned successful prediction");
        return $result;
    }
    
    // Also accept responses with data field directly
    if (isset($result['data'])) {
        error_log("[PREDICT.PHP] ✅ Flask returned prediction in data field");
        return $result;
    }
    
    error_log("[PREDICT.PHP] ❌ Invalid Flask response format");
    return null;
}

/**
 * Fallback rule-based risk calculation
 */
function calculateFallbackRisk($input) {
    $score = 0;
    
    $points = [
        'family_history' => ['Yes' => 25, 'No' => 0],
        'mental_health_history' => ['Yes' => 30, 'Maybe' => 10, 'No' => 0],
        'mood_swings' => ['High' => 35, 'Medium' => 15, 'Low' => 0],
        'coping_struggles' => ['Yes' => 30, 'No' => 0],
        'growing_stress' => ['Yes' => 25, 'Maybe' => 10, 'No' => 0],
        'days_indoors' => ['More than 2 months' => 25, '31-60 days' => 15, '15-30 days' => 10, '1-14 days' => 5, 'Go out Every day' => 0],
        'work_interest' => ['No' => 20, 'Maybe' => 8, 'Yes' => 0],
        'social_weakness' => ['Yes' => 20, 'Maybe' => 5, 'No' => 0],
        'changes_habits' => ['Yes' => 15, 'Maybe' => 5, 'No' => 0],
        'care_options' => ['No' => 10, 'Not sure' => 5, 'Yes' => 0],
        'self_employed' => ['Yes' => 5, 'No' => 0]
    ];
    
    foreach ($points as $field => $vals) {
        $val = $input[$field] ?? '';
        if (isset($vals[$val])) {
            $score += $vals[$val];
        }
    }
    
    $score = min(100, max(0, $score));
    
    $level = $score >= 60 ? 'High Risk' : ($score >= 30 ? 'Moderate Risk' : 'Low Risk');
    
    return [
        'prediction' => $score >= 30 ? 'Yes' : 'No',
        'risk_percentage' => $score,
        'risk_level' => $level,
        'probability_treatment' => $score,
        'probability_no_treatment' => 100 - $score,
        'top_factors' => []
    ];
}
?>