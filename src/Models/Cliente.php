<?php

namespace App\Models;

class Cliente extends BaseModel {
    protected $table = 'clientes';
    
    public function create($data) {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare(
            "INSERT INTO clientes (nombre, email, celular, direccion, password) VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "sssss",
            $data['nombre'],
            $data['email'],
            $data['celular'],
            $data['direccion'],
            $hash
        );
        
        $success = $stmt->execute();
        $id = $success ? $stmt->insert_id : null;
        $stmt->close();
        
        return $id;
    }
    
    public function authenticate($email, $password) {
        $stmt = $this->conn->prepare(
            "SELECT id_cliente, nombre, email, password FROM clientes WHERE email = ?"
        );
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows === 1) {
            $cliente = $result->fetch_assoc();
            
            if (password_verify($password, $cliente['password'])) {
                unset($cliente['password']);
                return $cliente;
            }
        }
        
        return false;
    }
    
    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id_cliente FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
}
