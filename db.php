<?php
// db.php - Database Configuration

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mentora_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for Bengali text support
$conn->set_charset("utf8mb4");

// Optional: Global helper function for queries (can be removed if not needed)
if (!function_exists('query')) {
    function query($sql) {
        global $conn;
        return $conn->query($sql);
    }
}
?>