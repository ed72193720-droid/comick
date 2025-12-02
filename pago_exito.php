<?php
require 'includes/session.php';
require 'includes/connect.php';

$pedido_id = isset($_GET['pedido']) ? intval($_GET['pedido']) : 0;
$metodo_pago = '';
$total = 0;
$estado = '';
$instrucciones_pago = '';

if ($pedido_id > 0) {
    // 1. BUSCAR INFORMACIÓN DEL PEDIDO en la base de datos
    $sql_pedido = "SELECT total, metodo_pago, estado FROM pedidos WHERE id = ?";
    $stmt_pedido = $conn->prepare($sql_pedido);
    $stmt_pedido->bind_param("i", $pedido_id);
    $stmt_pedido->execute();
    $result_pedido = $stmt_pedido->get_result();

    if ($result_pedido->num_rows > 0) {
        $data = $result_pedido->fetch_assoc();
        $metodo_pago = $data['metodo_pago'];
        $total = $data['total'];
        $estado = $data['estado'];

        // 2. DEFINIR INSTRUCCIONES BASADO EN EL MÉTODO DE PAGO
        switch ($metodo_pago) {
            case 'Tarjeta':
                $instrucciones_pago = "Para completar tu pago con **Tarjeta**, se requiere que ingreses los datos en la siguiente sección segura. (En una implementación real, aquí se incrustaría el formulario de la pasarela de pago).";
                break;
            case 'Transferencia':
                $instrucciones_pago = "Por favor, realiza la transferencia a la siguiente cuenta bancaria y envía el comprobante por WhatsApp/Correo:<br>
                                        **Banco:** El Banco Burger S.A.<br>
                                        **CLABE:** 012345678901234567<br>
                                        **Monto:** $". number_format($total, 2) . " MXN<br>
                                        **IMPORTANTE:** Tu pedido se confirmará cuando recibamos el comprobante.";
                break;
            case 'Oxxo':
                $instrucciones_pago = "Acude a cualquier tienda **Oxxo** y realiza un pago de servicio. Envía el comprobante para la confirmación:<br>
                                        **serie de referencia:** 4578 9012 3456 7890<br>
                                        **Monto:** $". number_format($total, 2) . " MXN";
                break;
            case 'PayPal':
                $instrucciones_pago = "Serás redirigido a la página segura de **PayPal** para completar la transacción. Si el pago es exitoso, tu pedido se confirmará automáticamente.";
                break;
            case 'Efectivo':
                $instrucciones_pago = "Tu pedido será cobrado al momento de la entrega por el repartidor. ¡Asegúrate de tener cambio!";
                break;
            default:
                $instrucciones_pago = "No se encontraron instrucciones de pago para este método. Contacta a soporte para ayuda.";
        }
    } else {
        $instrucciones_pago = "Error: No se encontró el pedido con ID: $pedido_id.";
    }
    $stmt_pedido->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Confirmación de Pedido</title>
</head>
<body style="background:#0a0a0a;color:#fff;font-family:Arial;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#1a1a1a;padding:30px;border-radius:10px;text-align:center;">
        
        <?php if ($pedido_id > 0 && $metodo_pago != ''): ?>
            <h1 style="color:#4CAF50;">🎉 ¡Pedido Registrado!</h1>
            <p style="font-size:1.2em;">Tu pedido **#<?php echo $pedido_id; ?>** fue registrado con éxito.</p>
            
            <h2 style="color:#FFA500; border-bottom: 2px solid #FFA500; padding-bottom: 10px; margin-top: 30px;">Resumen del Pago</h2>
            <p style="text-align: left; font-size: 1.1em;">
                **Método Elegido:** <?php echo $metodo_pago; ?><br>
                **Total a Pagar:** <span style="font-weight: bold; color: #fff;">$<?php echo number_format($total, 2); ?> MXN</span><br>
                **Estado Inicial:** <span style="color: <?php echo ($estado == 'Realizado') ? '#4CAF50' : '#FF9800'; ?>; font-weight: bold;"><?php echo $estado; ?></span>
            </p>

            <hr style="border-top: 1px dashed #FF9800; margin: 25px 0;">
            
            <h3 style="color:#FF9800;">Instrucciones para Completar el Pago</h3>
            <div style="text-align: left; background:#222; padding:15px; border-radius:10px; line-height: 1.6;">
                <?php echo $instrucciones_pago; ?>
            </div>

        <?php else: ?>
            <h1 style="color:red;">Error al cargar el Pedido</h1>
            <p>No se pudo encontrar la información del pedido. Por favor, revisa tus pedidos o contacta a soporte.</p>
        <?php endif; ?>

        
        <a href="galeria.php" style="background:orange;color:#fff;padding:10px 15px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:20px;">Volver a la Galería</a>
        <a href="index.php" style="background:orange;color:#fff;padding:10px 15px;border-radius:6px;text-decoration:none;display:inline-block;margin-top:20px;">Volver a inicio</a>
    </div>
</body>
</html>