<?php

namespace App\Models;

class Producto extends BaseModel {
    protected $table = 'productos';
    
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO productos (nombre, descripcion, precio, categoria, imagen) VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "ssdss",
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['categoria'],
            $data['imagen']
        );
        
        $success = $stmt->execute();
        $id = $success ? $stmt->insert_id : null;
        $stmt->close();
        
        return $id;
    }
    
    public function update($id, $data) {
        $stmt = $this->conn->prepare(
            "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, imagen = ? WHERE id_producto = ?"
        );
        
        $stmt->bind_param(
            "ssdssi",
            $data['nombre'],
            $data['descripcion'],
            $data['precio'],
            $data['categoria'],
            $data['imagen'],
            $id
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function findAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM productos";
        
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
        $stmt = $this->conn->prepare("DELETE FROM productos WHERE id_producto = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    public function findByCategory($categoria) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE categoria = ?");
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
