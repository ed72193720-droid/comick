<?php
session_start();
require 'includes/connect.php';

// Verificar que el cliente inició sesión
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login_registro.php");
    exit();
}

$cliente_id = $_SESSION['cliente_id'];

// Obtener pedidos del cliente
$sql = $conn->query("
    SELECT * FROM pedidos 
    WHERE id_cliente = $cliente_id 
    ORDER BY fecha DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Mis pedidos</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #0a0a0a;
    color: white;
    margin: 0;
    padding: 0;
}

.container {
    width: 90%;
    max-width: 800px;
    margin: 30px auto;
    background: #111;
    padding: 20px;
    border-radius: 10px;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

.pedido {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    border-left: 5px solid #FFD700;
}

.pedido p {
    margin: 5px 0;
}

.volver {
    display: block;
    width: fit-content;
    margin: 20px auto;
    padding: 10px 20px;
    background: orange;
    color: black;
    text-decoration: none;
    border-radius: 8px;
    font-weight: bold;
}
.volver:hover {
    background: darkorange;
}
</style>
</head>
<body>

<div class="container">
    <h2>📦 Mis Pedidos</h2>

    <?php if ($sql->num_rows == 0): ?>
        <p style="text-align:center;">No tienes pedidos aún.</p>
    <?php endif; ?>

    <?php while ($p = $sql->fetch_assoc()): ?>
        <div class="pedido">
            <p><strong>ID Pedido:</strong> <?= $p['id_pedido'] ?></p>
            <p><strong>Fecha:</strong> <?= $p['fecha'] ?></p>
            <p><strong>Total:</strong> $<?= number_format($p['total'], 2) ?></p>
            <p><strong>Estado:</strong> <?= $p['estado'] ?></p>

            <a href="ver_pedido.php?id=<?= $p['id_pedido'] ?>" 
               style="color:#FFD700; font-weight:bold;">
               ➤ Ver detalles
            </a>
        </div>
    <?php endwhile; ?>

    <a class="volver" href="menu.php">⬅ Volver al menú</a>
</div>

</body>
</html>
