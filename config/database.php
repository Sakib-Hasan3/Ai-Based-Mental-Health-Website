<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // আপনার ডাটাবেজ ইউজার
define('DB_PASS', '');           // আপনার ডাটাবেজ পাসওয়ার্ড
define('DB_NAME', 'mentora_db');

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $conn;
    private static $instance = null;
    
    private function __construct() {
        $this->connect();
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    private function connect() {
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            
            // Check connection
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // For API calls, return JSON error
            if (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Database connection error']);
                exit();
            }
            die("Database Connection Error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Fixed query method with error handling
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            // Check if prepare failed
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error . " - SQL: " . $sql);
            }
            
            if (!empty($params)) {
                // Dynamically determine parameter types
                $types = '';
                foreach ($params as $param) {
                    if (is_int($param)) {
                        $types .= 'i';
                    } elseif (is_float($param)) {
                        $types .= 'd';
                    } elseif (is_string($param)) {
                        $types .= 's';
                    } else {
                        $types .= 's'; // default to string
                    }
                }
                
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            
            // Check for execution errors
            if ($stmt->error) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            return $stmt;
            
        } catch (Exception $e) {
            error_log("Database Query Error: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Fixed insert method
    public function insert($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $insert_id = $this->conn->insert_id;
            $stmt->close();
            return $insert_id;
        } catch (Exception $e) {
            error_log("Insert Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Get single record
    public function getSingle($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row;
        } catch (Exception $e) {
            error_log("Get Single Error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get all records
    public function getAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            $result = $stmt->get_result();
            $rows = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $rows;
        } catch (Exception $e) {
            error_log("Get All Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Escape string
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
    
    // Check if table exists
    public function tableExists($table_name) {
        $result = $this->conn->query("SHOW TABLES LIKE '$table_name'");
        return $result->num_rows > 0;
    }
}
?>