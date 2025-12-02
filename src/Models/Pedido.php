<?php

namespace App\Models;

class Pedido extends BaseModel {
    protected $table = 'pedidos';
    
    public function create($data) {
        $stmt = $this->conn->prepare(
            "INSERT INTO pedidos (id_cliente, fecha, total, metodo_pago, estado) VALUES (?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param(
            "isdss",
            $data['id_cliente'],
            $data['fecha'],
            $data['total'],
            $data['metodo_pago'],
            $data['estado']
        );
        
        $success = $stmt->execute();
        $id = $success ? $stmt->insert_id : null;
        $stmt->close();
        
        return $id;
    }
    
    public function createDetalle($idPedido, $productos) {
        $stmt = $this->conn->prepare(
            "INSERT INTO pedido_detalle (id_pedido, id_producto, cantidad, subtotal) VALUES (?, ?, ?, ?)"
        );
        
        foreach ($productos as $producto) {
            $stmt->bind_param(
                "iiid",
                $idPedido,
                $producto['id'],
                $producto['cantidad'],
                $producto['subtotal']
            );
            $stmt->execute();
        }
        
        $stmt->close();
        return true;
    }
    
    public function findByCliente($idCliente, $limit = null) {
        $sql = "SELECT * FROM pedidos WHERE id_cliente = ? ORDER BY fecha DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function findByIdAndCliente($idPedido, $idCliente) {
        $stmt = $this->conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_cliente = ?");
        $stmt->bind_param("ii", $idPedido, $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_assoc();
    }
    
    public function getDetalle($idPedido) {
        $stmt = $this->conn->prepare(
            "SELECT pd.*, p.nombre, p.imagen, p.precio 
             FROM pedido_detalle pd
             INNER JOIN productos p ON p.id_producto = pd.id_producto
             WHERE pd.id_pedido = ?"
        );
        
        $stmt->bind_param("i", $idPedido);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function updateEstado($id, $estado) {
        $stmt = $this->conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $estado, $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    public function cancelar($idPedido, $idCliente) {
        $pedido = $this->findByIdAndCliente($idPedido, $idCliente);
        
        if ($pedido && $pedido['estado'] === 'Pendiente') {
            return $this->updateEstado($idPedido, 'Cancelado');
        }
        
        return false;
    }
    
    public function findAll($limit = null, $offset = null) {
        $sql = "SELECT * FROM pedidos ORDER BY fecha DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . intval($limit);
            if ($offset !== null) {
                $sql .= " OFFSET " . intval($offset);
            }
        }
        
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
