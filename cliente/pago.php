<?php
session_start();
require 'includes/connect.php';

// Asegurar que el usuario y el método de pago existan
if (!isset($_SESSION['id_cliente']) || !isset($_SESSION['metodo_pago']) || empty($_SESSION['carrito'])) {
    header("Location: galeria.php");
    exit();
}

$id_cliente = $_SESSION['id_cliente'];
$metodo_pago = $_SESSION['metodo_pago'];

// Función auxiliar necesaria para bind_param
function array_by_ref($arr) {
    if (strnatcmp(phpversion(), '5.3') >= 0) {
        $refs = array();
        foreach($arr as $key => $value)
            $refs[$key] = &$arr[$key];
        return $refs;
    }
    return $arr;
}

// 1. CALCULAR EL TOTAL DE LA ORDEN
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
                break;
            }
        }
    }
    $stmt->close();
}

// 2. GUARDAR LA ORDEN EN LA BASE DE DATOS
if ($total == 0 || empty($productos_orden)) {
    $estado_mensaje = "❌ Error: No se pudo procesar la orden o el total es cero.";
    $estado_clase = "cancelado";
} else {
    // Determinar el estado inicial
    $estado_inicial = ($metodo_pago == 'Efectivo') ? 'Realizado' : 'Pendiente';

    // 2.1 Insertar en la tabla 'pedidos'
    $stmt_orden = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha, total, metodo_pago, estado) VALUES (?, NOW(), ?, ?, ?)");
    $stmt_orden->bind_param("idss", $id_cliente, $total, $metodo_pago, $estado_inicial);

    if ($stmt_orden->execute()) {
        $id_pedido = $conn->insert_id; 
        $stmt_orden->close();
        
        // 2.2 Insertar en la tabla 'detalle_pedido'
        $stmt_detalle = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        
        foreach ($productos_orden as $p) {
            $id_producto = $p['id_producto'];
            $cantidad = $p['cantidad'];
            $precio_unitario = $p['precio'];

            $stmt_detalle->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio_unitario);
            $stmt_detalle->execute();
        }
        $stmt_detalle->close();

        // 3. VACIAR EL CARRITO Y ESTABLECER MENSAJE DE ÉXITO
        unset($_SESSION['carrito']);
        unset($_SESSION['metodo_pago']);

        $estado_mensaje = "✅ ¡Orden procesada! Tu pago está como **$estado_inicial** por **$metodo_pago**.";
        $estado_clase = ($estado_inicial == 'Realizado') ? 'realizado' : 'pendiente';
        
    } else {
        $estado_mensaje = "❌ Error al guardar la orden: " . $conn->error;
        $estado_clase = "cancelado";
    }
}

// 🛑 NUEVO BLOQUE: DEFINIR INSTRUCCIONES DE PAGO
$instrucciones_pago = ""; 

if (isset($total) && $total > 0 && isset($metodo_pago)) {
    switch ($metodo_pago) {
        case 'Tarjeta':
            $instrucciones_pago = "Para completar tu pago con **Tarjeta**, esta sección debería integrarse con una pasarela de pago segura (Stripe, Mercado Pago, etc.) donde se soliciten los datos de la tarjeta.";
            break;
        case 'Transferencia':
            $instrucciones_pago = "Por favor, realiza la transferencia electrónica a la siguiente cuenta y **envía el comprobante**:<br>
                                    **Banco:** El Banco Burger S.A.<br>
                                    **CLABE:** 012345678901234567<br>
                                    **Monto Exacto:** $" . number_format($total, 2) . " MXN<br>
                                    El pedido se procesará tan pronto como el pago sea verificado.";
            break;
        case 'Oxxo':
            $instrucciones_pago = "Acude a cualquier tienda **Oxxo** y realiza un pago de servicio. Envía el comprobante para la confirmación:<br>
                                        **serie de referencia:** 4578 9012 3456 7890<br>
                                        **Monto:** $". number_format($total, 2) . " MXN";
            break;
        case 'PayPal':
            $instrucciones_pago = "Para finalizar, haz clic en el botón de abajo para ser **redirigido a la página segura de PayPal** y completa la transacción.";
            break;
        case 'Efectivo':
            $instrucciones_pago = "Tu pedido está listo para ser preparado. Pagarás el monto de **$" . number_format($total, 2) . " MXN** al repartidor en el momento de la entrega.";
            break;
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