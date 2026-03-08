<?php
/**
 * MENTORA - Database Connection File
 * Secure MySQLi database connection
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mentora_db');
define('DB_PORT', 3306);

// Create connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Set timezone
date_default_timezone_set('Asia/Dhaka');

/**
 * Execute query
 */
function query($sql) {
    global $conn;
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }
    return $result;
}

/**
 * Get single row
 */
function fetchOne($sql) {
    $result = query($sql);
    return mysqli_fetch_assoc($result);
}

/**
 * Get all rows
 */
function fetchAll($sql) {
    $result = query($sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

/**
 * Insert data
 */
function insert($table, $data) {
    global $conn;
    
    $columns = implode(", ", array_keys($data));
    $values = "'" . implode("', '", array_map(function($value) use ($conn) {
        return mysqli_real_escape_string($conn, $value);
    }, array_values($data))) . "'";
    
    $sql = "INSERT INTO $table ($columns) VALUES ($values)";
    
    if (mysqli_query($conn, $sql)) {
        return mysqli_insert_id($conn);
    }
    return false;
}

/**
 * Update data
 */
function update($table, $data, $where) {
    global $conn;
    
    $sets = [];
    foreach ($data as $col => $val) {
        $val = mysqli_real_escape_string($conn, $val);
        $sets[] = "$col = '$val'";
    }
    
    $sql = "UPDATE $table SET " . implode(", ", $sets) . " WHERE $where";
    return mysqli_query($conn, $sql);
}

/**
 * Delete data
 */
function delete($table, $where) {
    global $conn;
    $sql = "DELETE FROM $table WHERE $where";
    return mysqli_query($conn, $sql);
}

/**
 * Escape string
 */
function escape($str) {
    global $conn;
    return mysqli_real_escape_string($conn, $str);
}

/**
 * Count rows
 */
function countRows($sql) {
    $result = query($sql);
    return mysqli_num_rows($result);
}

/**
 * Get last insert ID
 */
function lastId() {
    global $conn;
    return mysqli_insert_id($conn);
}

// Connection is ready
// echo "Database connected!";
?>