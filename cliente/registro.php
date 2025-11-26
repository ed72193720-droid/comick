<?php
session_start();
require 'includes/connect.php'; 

// Validación de sesión
if (!isset($_SESSION['id_cliente'])) {
    header("Location: login.php"); // Redirige al login si no está logueado
    exit();
}

$pago_realizado = false;
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $direccion_entrega = trim($_POST['direccion_entrega'] ?? '');

    if (empty($metodo_pago) || empty($direccion_entrega)) {
        $message = "¡ALTO! Faltan datos para la Misión de Pago.";
    } else {
        $id_cliente = $_SESSION['id_cliente'];
        $total = 0;

        // Calcular total del carrito
        if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
            $total = 0;
            foreach ($_SESSION['carrito'] as $item) {
                $total += $item['precio'] * $item['cantidad'];
            }
        }

        if ($total > 0) {
            $estado = "Pendiente";
            $fecha = date("Y-m-d H:i:s");

            $stmt_pedido = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha_pedido, total, metodo_pago, direccion_entrega, estado) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_pedido->bind_param("isdsss", $id_cliente, $fecha, $total, $metodo_pago, $direccion_entrega, $estado);

            if ($stmt_pedido->execute()) {
                unset($_SESSION['carrito']);
                $pago_realizado = true;
                $message_success = "¡POW! Pago exitoso. Tu pedido va en camino a: " . htmlspecialchars($direccion_entrega);
            } else {
                $message = "¡BOOM! Error al procesar el pedido: " . $stmt_pedido->error;
            }

            $stmt_pedido->close();
        } else {
             $message = "¡WHOOSH! No hay productos en el carrito.";
        }
    }
}
?>

