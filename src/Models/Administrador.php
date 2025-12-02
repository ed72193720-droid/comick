<?php

namespace App\Models;

class Administrador extends BaseModel {
    protected $table = 'administradores';
    
    public function create($data) {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare(
            "INSERT INTO administradores (username, email, password) VALUES (?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sss",
            $data['username'],
            $data['email'],
            $hash
        );
        
        $success = $stmt->execute();
        $id = $success ? $stmt->insert_id : null;
        $stmt->close();
        
        return $id;
    }
    
    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare(
            "SELECT id_admin, username, email, password FROM administradores WHERE email = ?"
        );
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                unset($admin['password']);
                return $admin;
            }
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM administradores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT id_admin, username, email FROM administradores WHERE id_admin = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
}
