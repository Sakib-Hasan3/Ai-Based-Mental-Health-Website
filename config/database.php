<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mentora_db');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    private $error;
    private static $instance = null;
    
    private function __construct() {
        $this->connect();
    }
    
    // Singleton pattern
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    private function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
        
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
        return $this->conn;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Prepare and execute query
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
    
    // Get single record
    public function getSingle($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
    
    // Get multiple records
    public function getAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Insert and return ID
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->conn->insert_id;
    }
    
    // Escape string
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}
?>