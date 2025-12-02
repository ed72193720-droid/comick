<?php
require 'includes/session.php';
require 'includes/connect.php';
require 'includes/helpers.php';

requireLogin('login.php');

$pago_realizado = false;
$message = "";
$direccion_entrega = "";
$metodo_pago = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $direccion_entrega = sanitizeInput($_POST['direccion_entrega'] ?? '', 200);

    $metodos_validos = ['Tarjeta', 'Efectivo', 'Transferencia'];
    
    if (empty($metodo_pago) || empty($direccion_entrega)) {
        $message = "¡ALTO! Faltan datos para la Misión de Pago.";
    } elseif (!in_array($metodo_pago, $metodos_validos)) {
        $message = "Método de pago inválido.";
    } else {
        $id_cliente = $_SESSION['cliente_id'];
        $total = getCarritoTotal();

        if ($total > 0) {
            $estado = "Pendiente";
            $fecha = date("Y-m-d H:i:s");

            $stmt_pedido = $conn->prepare("INSERT INTO pedidos (id_cliente, fecha, total, metodo_pago, estado) VALUES (?, ?, ?, ?, ?)");
            $stmt_pedido->bind_param("isdss", $id_cliente, $fecha, $total, $metodo_pago, $estado);

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

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Método de Pago - Comick Burger</title>
<style>
body { 
    background-color: #000; 
    color: #fff; 
    font-family: 'Comic Sans MS', cursive; 
    margin:0; 
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-image: url('imagenes/bumo.png'); 
    background-size: cover;
    background-position: center;
}
.checkout-box {
    background: rgba(0,0,0,0.9);
    padding: 30px;
    border-radius: 20px;
    width: 100%;
    max-width: 600px;
    border: 6px solid #ffeb3b; 
    box-shadow: 12px 12px 0 #e53935; 
    animation: fadeIn 0.5s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
h1 { color: #00bcd4; font-size: 32px; text-shadow: 3px 3px #ff9800; text-align: center; margin-bottom: 25px; border-bottom: 2px dashed #ff9800; padding-bottom: 10px;}
.section-title { color: #ff9800; font-size: 24px; margin-top: 20px; margin-bottom: 15px; padding-left: 10px; border-left: 5px solid #e53935; }
textarea, input[type="text"], select { width:100%; padding:12px; border-radius:8px; border:3px solid #00bcd4; background:#333; color:#fff; box-shadow:3px 3px 0 #ffeb3b; transition: border-color 0.2s; resize:none;}
textarea:focus, input:focus, select:focus { border-color:#ff9800; }

.radio-group label { display:inline-block; background:#222; padding:10px 15px; border-radius:8px; border:2px solid #ff9800; margin-right:15px; cursor:pointer; }
.radio-group label:has(input:checked) { background:#e53935; border-color:#ffeb3b; }

.message-error { color:#ff6347; margin-bottom:15px; font-weight:bold; border:2px dashed #ff6347; padding:8px; background:#220000; }

.btn-confirm { width:100%; padding:18px; background-color:#28a745; border:none; border-radius:15px; font-weight:bold; cursor:pointer; color:#fff; font-size:22px; transition:0.2s; box-shadow:6px 6px 0 #00bcd4; margin-top:30px; }
.btn-confirm:hover { background-color:#218838; transform:translateY(-3px); box-shadow:8px 8px 0 #00bcd4; }

.success-box { text-align:center; padding:30px; background:#1e88e5; border-radius:15px; border:5px solid #ffeb3b; box-shadow:10px 10px 0 #e53935; }
.success-box h2 { color:#ffeb3b; margin-top:0; }
.success-box a { margin-top:20px; background-color:#ff9800; color:#000; padding:10px 20px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block; box-shadow:3px 3px 0 #000; }
</style>
</head>
<body>

<div class="checkout-box">

    <!-- LOGO ARRIBA -->
    <div style="text-align:center; margin-bottom:20px;">
        <img src="imagenes/logo1.png" alt="Comick Burger" style="width:120px;">
    </div>

    <?php if($pago_realizado): ?>
        <div class="success-box">
            <h2>¡MISIÓN COMPLETADA!</h2>
            <p><?php echo htmlspecialchars($message_success); ?></p>
            <p>Tu comida saldrá de la base lo antes posible. ¡Gracias por tu orden!</p>
            <a href="galeria.php">REGRESAR AL INICIO</a>
        </div>
    <?php else: ?>
        <h1>CONFIRMA TU ORDEN DE HÉROE</h1>

        <?php if(!empty($message)) echo '<p class="message-error">'.htmlspecialchars($message).'</p>'; ?>

        <form method="POST" action="">
            <div class="section-title">1. ¿DÓNDE ENTREGAMOS?</div>
            <textarea name="direccion_entrega" rows="3" required><?php echo htmlspecialchars($direccion_entrega); ?></textarea>

            <div class="section-title">2. SELECCIONA TU CÓDIGO DE PAGO</div>
            <div class="radio-group">
                <label><input type="radio" name="metodo_pago" value="Tarjeta" <?php if($metodo_pago=="Tarjeta") echo "checked"; ?>> Tarjeta (Visa/MasterHero)</label>
                <label><input type="radio" name="metodo_pago" value="Efectivo" <?php if($metodo_pago=="Efectivo") echo "checked"; ?>> Efectivo (Pago al Recibir)</label>
                <label><input type="radio" name="metodo_pago" value="Transferencia" <?php if($metodo_pago=="Transferencia") echo "checked"; ?>> Transferencia (Cripto-Moneda)</label>
            </div>

            <button type="submit" class="btn-confirm">¡ZAP! ENVIAR ORDEN Y PAGAR</button>
            <p style="margin-top:15px;"><a href="carrito.php" style="color:#00bcd4;">&larr; Revisar el Botín (Carrito)</a></p>
        </form>
    <?php endif; ?>

</div>

</body>
</html>
