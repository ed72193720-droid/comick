<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'])) {
    $id = intval($_POST['id_producto']);
    $cantidad = max(1,intval($_POST['cantidad']));
    if (isset($_SESSION['carrito'][$id])) {
        $_SESSION['carrito'][$id] = $cantidad;
    }
}
header('Location: carrito.php');
exit;
