<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/helpers.php';

use App\Models\Producto;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('galeria.php');
}

$id_producto = validateNumber($_POST['id_producto'] ?? 0);
$cantidad = validateNumber($_POST['cantidad'] ?? 1);

if (!$id_producto || $cantidad < 1) {
    redirect('galeria.php');
}

$productoModel = new Producto();
$producto = $productoModel->findById($id_producto);

if (!$producto) {
    redirect('galeria.php');
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

redirect('galeria.php');