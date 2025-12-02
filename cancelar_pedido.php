<?php
require 'includes/session.php';
require 'includes/connect.php';
require 'includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('mis_pedidos.php');
}

requireLogin('login.php');

$cliente_id = $_SESSION['cliente_id'];
$id_pedido = validateNumber($_POST['id_pedido'] ?? 0, 1);

if (!$id_pedido) {
    redirect('mis_pedidos.php');
}

$stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id_pedido = ? AND id_cliente = ?");
$stmt->bind_param("ii", $id_pedido, $cliente_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    $stmt->close();
    redirect('mis_pedidos.php');
}

$row = $res->fetch_assoc();
$stmt->close();

if ($row['estado'] === 'Pendiente') {
    $upd = $conn->prepare("UPDATE pedidos SET estado = 'Cancelado' WHERE id_pedido = ? AND id_cliente = ?");
    $upd->bind_param("ii", $id_pedido, $cliente_id);
    
    if ($upd->execute()) {
        $_SESSION['mensaje'] = "Pedido #{$id_pedido} cancelado correctamente.";
    }
    
    $upd->close();
}

redirect('mis_pedidos.php');
