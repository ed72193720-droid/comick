<?php
require 'includes/session.php';
require 'includes/connect.php';
require 'includes/helpers.php';

requireLogin('login.php');

$cliente_id = $_SESSION['cliente_id'];

if (!isset($_GET['id'])) {
    redirect('mis_pedidos.php');
}

$id_pedido = validateNumber($_GET['id'], 1);

if (!$id_pedido) {
    redirect('mis_pedidos.php');
}

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_cliente = ?");
$stmt->bind_param("ii", $id_pedido, $cliente_id);
$stmt->execute();
$pedido = $stmt->get_result();

if ($pedido->num_rows == 0) {
    echo "<h2 style='color:white; text-align:center;'>❌ Pedido no encontrado.</h2>";
    exit();
}

$p = $pedido->fetch_assoc();

$stmt2 = $conn->prepare("
    SELECT pd.*, pr.nombre, pr.imagen, pr.precio 
    FROM pedido_detalle pd
    INNER JOIN productos pr ON pr.id_producto = pd.id_producto
    WHERE pd.id_pedido = ?
");
$stmt2->bind_param("i", $id_pedido);
$stmt2->execute();
$detalle = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalles del Pedido</title>
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

.item {
    background: #1a1a1a;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 8px;
    display: flex;
    gap: 15px;
    align-items: center;
}

.item img {
    width: 90px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.item-info p {
    margin: 3px 0;
}

.total {
    margin-top: 20px;
    font-size: 20px;
    font-weight: bold;
    text-align: right;
    color: #FFD700;
}

.volver {
    display: block;
    margin: 20px auto;
    width: fit-content;
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
    <h2>📦 Detalles del Pedido #<?= $p['id_pedido'] ?></h2>
    <p><strong>Fecha:</strong> <?= $p['fecha'] ?></p>
    <p><strong>Estado:</strong> <?= $p['estado'] ?></p>

    <hr>

    <?php while ($d = $detalle->fetch_assoc()): ?>
        <div class="item">

            <img src="assets/<?= htmlspecialchars($d['imagen']) ?>" alt="">

            <div class="item-info">
                <p><strong><?= htmlspecialchars($d['nombre']) ?></strong></p>
                <p>Cantidad: <?= $d['cantidad'] ?></p>
                <p>Precio unitario: $<?= number_format($d['precio'], 2) ?></p>
                <p>Subtotal: $<?= number_format($d['subtotal'], 2) ?></p>
            </div>

        </div>
    <?php endwhile; ?>

    <p class="total">TOTAL: $<?= number_format($p['total'], 2) ?> MXN</p>

    <a class="volver" href="mis_pedidos.php">⬅ Volver a mis pedidos</a>
</div>

</body>
</html>
