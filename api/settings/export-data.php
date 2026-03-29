<?php
// api/settings/export-data.php
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

require_once '../../config/database.php';

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Please login first");
    }

    $user_id = $_SESSION['user_id'];
    $type = isset($_GET['type']) ? trim($_GET['type']) : 'all';
    $format = isset($_GET['format']) ? trim($_GET['format']) : 'json';
    
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    $export_data = [];

    // Export journal entries
    if ($type === 'journal' || $type === 'all') {
        $sql = "SELECT id, title, content, mood, created_at FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $journals = [];
        while ($row = $result->fetch_assoc()) {
            $journals[] = $row;
        }
        $stmt->close();
        $export_data['journals'] = $journals;
    }

    // Export mood entries
    if ($type === 'mood' || $type === 'all') {
        $sql = "SELECT id, mood, intensity, notes, entry_date FROM mood_entries WHERE user_id = ? ORDER BY entry_date DESC";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $moods = [];
        while ($row = $result->fetch_assoc()) {
            $moods[] = $row;
        }
        $stmt->close();
        $export_data['moods'] = $moods;
    }

    // Set headers based on format BEFORE any output
    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mentora-export-' . date('Y-m-d') . '.csv"');
        
        // CSV export
        echo "Mentora Data Export - " . date('Y-m-d H:i:s') . "\n\n";
        
        if (!empty($export_data['journals'])) {
            echo "JOURNALS\n";
            echo "Title,Date,Mood\n";
            foreach ($export_data['journals'] as $j) {
                echo "\"" . str_replace('"', '""', $j['title']) . "\",\"" . $j['created_at'] . "\",\"" . ($j['mood'] ?? 'N/A') . "\"\n";
            }
            echo "\n";
        }
        
        if (!empty($export_data['moods'])) {
            echo "MOOD ENTRIES\n";
            echo "Date,Mood,Intensity\n";
            foreach ($export_data['moods'] as $m) {
                echo "\"" . $m['entry_date'] . "\",\"" . $m['mood'] . "\",\"" . $m['intensity'] . "\"\n";
            }
        }
    } else {
        // JSON format (default)
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="mentora-export-' . date('Y-m-d') . '.json"');
        echo json_encode([
            'success' => true,
            'export_date' => date('Y-m-d H:i:s'),
            'data' => $export_data
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    $conn->close();

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
