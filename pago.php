<?php
session_start();
require 'includes/connect.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php?redirect=pago.php");
    exit();
}

// Verificar si el carrito está vacío
if (empty($_SESSION['carrito'])) {
    header("Location: galeria.php");
    exit();
}

$id_cliente = $_SESSION['cliente_id'];
$metodo_pago = $_SESSION['metodo_pago'] ?? null;

// Redirigir al método de pago si no está seleccionado
if (!$metodo_pago) {
    header("Location: metodo_pago.php");
    exit();
}

$productos_orden = [];
$total = 0;
$ids = array_column($_SESSION['carrito'], 'id');
$ids = array_filter($ids, 'is_numeric');

if (!empty($ids)) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT id_producto, nombre, precio FROM productos WHERE id_producto IN ($placeholders)";
    $stmt = $conn->prepare($sql);

    $tipos = str_repeat('i', count($ids));
    $params = array_merge([$tipos], $ids);
    call_user_func_array([$stmt, 'bind_param'], array_by_ref($params));

    $stmt->execute();
    $consulta = $stmt->get_result();

    while ($row = $consulta->fetch_assoc()) {
        foreach ($_SESSION['carrito'] as $item) {
            if ($item['id'] == $row['id_producto']) {
                $cantidad = intval($item['cantidad']);
                $subtotal = $row['precio'] * $cantidad;
                $total += $subtotal;
                $row['cantidad'] = $cantidad;
                $row['subtotal'] = $subtotal;
                $productos_orden[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Estado de la Orden - Comick Burger</title>
<style>
/* ... (Estilos CSS existentes) ... */
body { margin:0; padding:0; font-family: "Comic Sans MS", cursive; background: #0d0d0d; color: #fff; text-align: center; }
.container { width: 90%; max-width: 600px; margin: 40px auto; background: #1b1b1b; padding: 30px; border-radius: 18px; box-shadow: 5px 5px 0 #ff0000, -5px -5px 0 #0047ff; }
h2 { font-size: 30px; margin-bottom: 25px; text-shadow: 2px 2px #000; color: #ff9800; }
.total-final { font-size: 28px; margin: 20px 0; font-weight: bold; }
.mensaje-estado { padding: 15px; border-radius: 10px; margin: 20px 0; font-size: 20px; font-weight: bold; }
.pendiente { background: #ff9800; color: #000; border: 3px solid orange; }
.realizado { background: #28a745; color: white; border: 3px solid green; }
.cancelado { background: #e53935; color: white; border: 3px solid red; }
.btn { display: inline-block; padding: 10px 25px; margin-top: 20px; text-decoration: none; background: #ff9800; color: #000; border-radius: 10px; font-weight: bold; transition: 0.2s; }
.btn:hover { background: #ffa726; }
/* 🛑 NUEVO ESTILO PARA INSTRUCCIONES */
.instrucciones-box {
    text-align: left; 
    background: #2b2b2b; 
    padding: 20px; 
    border-radius: 10px; 
    border: 1px solid #ff9800; 
    margin-top: 30px; 
    line-height: 1.6;
}
</style>
</head>
<body>

<div class="container">
    <h2>🍔 Estado de tu Orden</h2>
    
    <p>ID de Pedido: **<?php echo $id_pedido ?? 'N/A'; ?>**</p>
    <p>Método de Pago Seleccionado: **<?php echo htmlspecialchars($metodo_pago ?? 'N/A'); ?>**</p>

    <div class="mensaje-estado <?php echo $estado_clase ?? 'cancelado'; ?>">
        <?php echo $estado_mensaje ?? 'Hubo un error desconocido.'; ?>
    </div>

    <?php if (isset($total) && $total > 0): ?>
        <p class="total-final">Total de la Orden: $<?php echo number_format($total, 2); ?> MXN</p>
    <?php endif; ?>
    
    <?php 
    // 🛑 NUEVO BLOQUE: MOSTRAR INSTRUCCIONES
    if (isset($id_pedido) && $id_pedido > 0): ?>
        <h3 style="color: #ff9800; margin-top: 30px;">Instrucciones para Completar </h3>
        <div class="instrucciones-box">
            <?php echo $instrucciones_pago; ?>
        </div>
    <?php endif; ?>
    
    <a href="galeria.php" class="btn">Volver a la Galería</a>
    <a href="index.php" class="btn">Volver a inicio</a>

</div>

</body>
</html>