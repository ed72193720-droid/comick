<?php
session_start();
require 'includes/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: mis_pedidos.php');
    exit;
}

if (!isset($_SESSION['cliente_id'])) {
    header('Location: cliente_login.php');
    exit;
}

$cliente_id = intval($_SESSION['cliente_id']);
$id_pedido = isset($_POST['id_pedido']) ? intval($_POST['id_pedido']) : 0;

if ($id_pedido <= 0) {
    header('Location: mis_pedidos.php');
    exit;
}

/* Verificar que el pedido pertenece al cliente y está en Pendiente */
$stmt = $conn->prepare("SELECT estado FROM pedidos WHERE id_pedido = ? AND id_cliente = ?");
$stmt->bind_param("ii", $id_pedido, $cliente_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    // No existe o no es del cliente
    $stmt->close();
    header('Location: mis_pedidos.php');
    exit;
}

$row = $res->fetch_assoc();
$estado = $row['estado'];
$stmt->close();

if ($estado !== 'Pendiente') {
    // Solo se permite cancelar si está pendiente
    header('Location: mis_pedidos.php');
    exit;
}

/* Actualizar estado a Cancelado */
$upd = $conn->prepare("UPDATE pedidos SET estado = 'Cancelado' WHERE id_pedido = ? AND id_cliente = ?");
$upd->bind_param("ii", $id_pedido, $cliente_id);
$ok = $upd->execute();
$upd->close();

if ($ok) {
    // opcional: puedes enviar notificación por email o registrar historial
    $_SESSION['mensaje'] = "Pedido #{$id_pedido} cancelado correctamente.";
}

header('Location: mis_pedidos.php');
exit;
