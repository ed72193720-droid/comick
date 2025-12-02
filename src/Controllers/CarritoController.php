<?php

namespace App\Controllers;

class CarritoController extends BaseController {
    public function verCarrito() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $carrito = $_SESSION['carrito'] ?? [];
        $total = getCarritoTotal();
        
        $this->render('cliente/carrito', [
            'carrito' => $carrito,
            'total' => $total
        ]);
    }
    
    public function actualizar() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('carrito.php');
            return;
        }

        $id_producto = validateNumber($_POST['id_producto'] ?? 0);
        $cantidad = validateNumber($_POST['cantidad'] ?? 0);

        if (!$id_producto || $cantidad < 1) {
            $this->redirect('carrito.php');
            return;
        }

        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] = $cantidad;
        }

        $this->redirect('carrito.php');
    }
    
    public function eliminar() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('carrito.php');
            return;
        }

        $id_producto = validateNumber($_POST['id_producto'] ?? 0);

        if (!$id_producto) {
            $this->redirect('carrito.php');
            return;
        }

        if (isset($_SESSION['carrito'][$id_producto])) {
            unset($_SESSION['carrito'][$id_producto]);
        }

        $this->redirect('carrito.php');
    }
    
    public function vaciar() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $_SESSION['carrito'] = [];
        $this->redirect('carrito.php');
    }
}
