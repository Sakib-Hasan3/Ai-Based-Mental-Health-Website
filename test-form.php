<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 999; // Test user
    $_SESSION['username'] = 'test_user';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Form Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body { padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 30px; border-radius: 8px; }
        #log { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 300px; overflow-y: auto; margin-top: 20px; }
        .log-line { margin: 2px 0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🧪 Assessment Form Test</h1>
    <p class="text-muted">Check browser console (F12) and this page for real-time logs</p>
    
    <form id="testForm" class="mt-4">
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control" required>
                <option value="">Select...</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Test Value</label>
            <input type="text" name="test_field" class="form-control" value="test123" />
        </div>
        <button type="submit" class="btn btn-primary">📤 Send Test Request</button>
        <button type="button" id="clearLogBtn" class="btn btn-secondary">Clear Log</button>
    </form>
    
    <div id="log">
        <div class="log-line">🚀 Page loaded...</div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const logElement = document.getElementById('log');

function addLog(message, type = 'info') {
    const timestamp = new Date().toLocaleTimeString();
    const levelIcon = {
        'info': 'ℹ️',
        'success': '✅',
        'error': '❌',
        'warning': '⚠️'
    }[type] || 'ℹ️';
    
    const line = document.createElement('div');
    line.className = 'log-line';
    line.textContent = `[${timestamp}] ${levelIcon} ${message}`;
    logElement.appendChild(line);
    logElement.scrollTop = logElement.scrollHeight;
    
    console.log(`[${type.toUpperCase()}]`, message);
}

document.getElementById('clearLogBtn').addEventListener('click', function() {
    logElement.innerHTML = '';
    addLog('Log cleared');
});

$(document).ready(function() {
    addLog('jQuery loaded, setting up form...');
    
    $('#testForm').on('submit', function(e) {
        e.preventDefault();
        addLog('Form submitted!', 'info');
        
        const formData = {
            gender: $('[name="gender"]').val(),
            test_field: $('[name="test_field"]').val()
        };
        
        addLog(`Form data: ${JSON.stringify(formData)}`, 'info');
        addLog('Sending AJAX request to api/predict.php...', 'info');
        
        $.ajax({
            url: 'api/predict.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            beforeSend: function() {
                addLog('AJAX: beforeSend hook triggered', 'info');
            },
            success: function(response) {
                addLog(`AJAX Success! Response: ${JSON.stringify(response).substring(0, 200)}`, 'success');
            },
            error: function(xhr, status, error) {
                addLog(`AJAX Error [${xhr.status}]: ${error} - ${xhr.responseText.substring(0, 100)}`, 'error');
            },
            complete: function() {
                addLog('AJAX: complete hook triggered', 'info');
            }
        });
    });
});
</script>
</body>
</html>
