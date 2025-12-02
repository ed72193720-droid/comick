<?php

namespace App\Controllers;

use App\Models\Producto;

class ProductoController extends BaseController {
    private $productoModel;
    
    public function __construct() {
        $this->productoModel = new Producto();
    }
    
    public function galeria() {
        $categoria = $_GET['categoria'] ?? 'todos';
        
        if ($categoria === 'todos') {
            $productos = $this->productoModel->findAll();
        } else {
            $productos = $this->productoModel->findByCategory($categoria);
        }
        
        $this->render('cliente/galeria', [
            'productos' => $productos,
            'categoria' => $categoria
        ]);
    }
    
    public function agregarCarrito() {
        require_once __DIR__ . '/../../includes/helpers.php';
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('galeria.php');
            return;
        }

        $id_producto = validateNumber($_POST['id_producto'] ?? 0);
        $cantidad = validateNumber($_POST['cantidad'] ?? 1);

        if (!$id_producto || $cantidad < 1) {
            $this->redirect('galeria.php');
            return;
        }

        $producto = $this->productoModel->findById($id_producto);

        if (!$producto) {
            $this->redirect('galeria.php');
            return;
        }

        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = [
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen'],
                'cantidad' => $cantidad
            ];
        }

        $this->redirect('galeria.php');
    }
}
