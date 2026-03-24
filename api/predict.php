<?php
// api/predict.php - Mental Health Assessment Prediction
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log every request
error_log("[PREDICT.PHP] REQUEST: Method=" . $_SERVER['REQUEST_METHOD'] . " Time=" . date('Y-m-d H:i:s'));
error_log("[PREDICT.PHP] POST data: " . print_r($_POST, true));
error_log("[PREDICT.PHP] Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET'));

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("[PREDICT.PHP] ERROR: User not logged in");
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get POST data
$input = null;
$rawInput = file_get_contents('php://input');
error_log("[PREDICT.PHP] Raw input length: " . strlen($rawInput));

if ($rawInput) {
    $input = json_decode($rawInput, true);
}

// Fallback to $_POST
if (!$input || empty($input)) {
    $input = $_POST;
}

if (!$input || empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit();
}

// Call Flask API first
$flaskResponse = callFlaskAPI($input);
if ($flaskResponse) {
    http_response_code(200);
    if (isset($flaskResponse['success'])) {
        echo json_encode($flaskResponse);
    } else if (isset($flaskResponse['risk_percentage'])) {
        echo json_encode(['success' => true, 'data' => $flaskResponse]);
    } else {
        echo json_encode(['success' => true, 'data' => $flaskResponse]);
    }
    exit();
}

// Fallback: Rule-based calculation
$result = calculateFallbackRisk($input);
http_response_code(200);
echo json_encode(['success' => true, 'data' => $result, 'method' => 'fallback']);
exit();

/**
 * Call Flask ML API
 */
function callFlaskAPI($input) {
    $url = 'http://127.0.0.1:5000/api/predict';
    
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
    
    $json = json_encode($data);
    
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $json,
            'timeout' => 8
        ]
    ];
    
    $context = stream_context_create($opts);
    
    $response = @file_get_contents($url, false, $context);
    if ($response === false) {
        error_log("Flask call failed");
        return null;
    }
    
    $result = json_decode($response, true);
    if ($result === null) {
        error_log("Flask JSON decode failed: $response");
        return null;
    }
    
    return $result;
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