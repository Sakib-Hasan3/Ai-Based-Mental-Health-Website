
<?php
// api/settings/get-settings.php
header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Please login first'
        ]);
        exit();
    }

    // Return user settings from session
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'full_name' => $_SESSION['user_name'] ?? 'User',
            'phone' => $_SESSION['phone'] ?? '',
            'bio' => $_SESSION['bio'] ?? '',
            'profile_image' => $_SESSION['profile_image'] ?? 'default-avatar.png',
            'notifications' => [
                'email' => 1,
                'push' => 1,
                'session_reminders' => 1,
                'weekly_report' => 1,
                'marketing_emails' => 0
            ],
            'privacy' => [
                'profile_visibility' => 'public',
                'data_sharing' => 'minimal',
                'show_activity_status' => 1,
                'show_last_seen' => 1
            ]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
