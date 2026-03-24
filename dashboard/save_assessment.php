<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// চেক করুন ইউজার লগইন আছে কিনা
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit();
}

// ডেটা পান
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'No data received']);
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL query
$sql = "INSERT INTO mental_health_assessments (
    user_id, gender, occupation, self_employed, family_history,
    days_indoors, growing_stress, changes_habits, mental_health_history,
    mood_swings, coping_struggles, work_interest, social_weakness,
    mental_health_interview, care_options, prediction_result, 
    risk_percentage, recommendation
) VALUES (
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "isssssssssssssssds",
    $user_id,
    $data['gender'],
    $data['occupation'],
    $data['self_employed'],
    $data['family_history'],
    $data['Days_Indoors'],
    $data['Growing_Stress'],
    $data['Changes_Habits'],
    $data['Mental_Health_History'],
    $data['Mood_Swings'],
    $data['Coping_Struggles'],
    $data['Work_Interest'],
    $data['Social_Weakness'],
    $data['mental_health_interview'],
    $data['care_options'],
    $data['prediction_result'],
    $data['risk_percentage'],
    $data['recommendation']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>