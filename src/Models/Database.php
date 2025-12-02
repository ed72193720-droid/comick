<?php

namespace App\Models;

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $config = include __DIR__ . '/../../config/config.php';
        
        $this->conn = new \mysqli(
            $config['db_host'],
            $config['db_user'],
            $config['db_pass'],
            $config['db_name']
        );
        
        if ($this->conn->connect_error) {
            error_log("Database connection error: " . $this->conn->connect_error);
            throw new \Exception("Database connection failed");
        }
        
        $this->conn->set_charset($config['db_charset']);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    private function __clone() {}
}
