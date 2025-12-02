<?php

namespace App\Models;

abstract class BaseModel {
    protected $db;
    protected $conn;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function findAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset !== null) {
                $sql .= " OFFSET " . intval($offset);
            }
        }
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    protected function executeQuery($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
}
