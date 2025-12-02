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

.modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; justify-content:center; align-items:center; }
.modal-overlay.active { display:flex; }
.modal-content { background:rgba(0,0,0,0.95); border:6px solid #ffeb3b; border-radius:20px; padding:30px; max-width:500px; width:90%; box-shadow:12px 12px 0 #e53935; animation:modalAppear 0.3s ease-out; }
@keyframes modalAppear { from { opacity:0; transform:scale(0.9); } to { opacity:1; transform:scale(1); } }
.modal-header { color:#00bcd4; font-size:28px; text-shadow:2px 2px #ff9800; margin-bottom:20px; text-align:center; border-bottom:2px dashed #ff9800; padding-bottom:10px; }
.modal-field { margin-bottom:15px; }
.modal-label { color:#ff9800; font-weight:bold; display:block; margin-bottom:8px; }
.modal-input { width:100%; padding:12px; border:3px solid #00bcd4; border-radius:8px; background:#333; color:#fff; box-sizing:border-box; font-size:16px; box-shadow:3px 3px 0 #ffeb3b; transition:border-color 0.2s; }
.modal-input:focus { border-color:#ff9800; outline:none; }
.modal-inputs-row { display:flex; gap:10px; }
.modal-inputs-row .modal-input { flex:1; }
.modal-buttons { display:flex; gap:10px; margin-top:25px; }
.modal-btn { flex:1; padding:15px; border:none; border-radius:10px; font-size:16px; font-weight:bold; cursor:pointer; box-shadow:4px 4px 0 #000; transition:0.2s; }
.modal-btn-confirm { background:#28a745; color:#fff; }
.modal-btn-confirm:hover { background:#218838; transform:translateY(-2px); }
.modal-btn-cancel { background:#e53935; color:#fff; }
.modal-btn-cancel:hover { background:#c1392b; transform:translateY(-2px); }
.info-sandbox { background:#1b3a57; border-left:5px solid #00bcd4; padding:12px; margin-bottom:15px; font-size:14px; color:#a0d8ff; border-radius:5px; }

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
                <label><input type="radio" name="metodo_pago" value="Tarjeta" onclick="abrirModalTarjeta()" <?php if($metodo_pago=="Tarjeta") echo "checked"; ?>> Tarjeta (Visa/MasterHero)</label>
                <label><input type="radio" name="metodo_pago" value="Efectivo" onclick="abrirModalEfectivo()" <?php if($metodo_pago=="Efectivo") echo "checked"; ?>> Efectivo (Pago al Recibir)</label>
                <label><input type="radio" name="metodo_pago" value="Transferencia" onclick="abrirModalTransferencia()" <?php if($metodo_pago=="Transferencia") echo "checked"; ?>> Transferencia (Cripto-Moneda)</label>
            </div>

            <button type="submit" class="btn-confirm" id="btn-pagar" style="display: none;">¡ZAP! ENVIAR ORDEN Y PAGAR</button>
            <p style="margin-top:15px;"><a href="carrito.php" style="color:#00bcd4;">&larr; Revisar el Botín (Carrito)</a></p>
        </form>
    <?php endif; ?>

</div>

<div id="modal-tarjeta" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">💳 TARJETA BANCARIA</div>
        <div class="info-sandbox">⚠️ Modo Sandbox - Ingresa cualquier número de tarjeta válido para pruebas</div>
        
        <div class="modal-field">
            <label class="modal-label">Número de Tarjeta</label>
            <input type="text" id="tarjeta-numero" class="modal-input" placeholder="1234 5678 9012 3456" maxlength="19" inputmode="numeric">
        </div>
        
        <div class="modal-field">
            <label class="modal-label">Nombre del Titular</label>
            <input type="text" id="tarjeta-titular" class="modal-input" placeholder="NOMBRE APELLIDO">
        </div>
        
        <div class="modal-inputs-row">
            <div class="modal-field" style="flex:1;">
                <label class="modal-label">Vencimiento (MM/AA)</label>
                <input type="text" id="tarjeta-vencimiento" class="modal-input" placeholder="12/25" maxlength="5">
            </div>
            <div class="modal-field" style="flex:1;">
                <label class="modal-label">CVV</label>
                <input type="text" id="tarjeta-cvv" class="modal-input" placeholder="123" maxlength="4" inputmode="numeric">
            </div>
        </div>
        
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-confirm" onclick="confirmarTarjeta()">✓ CONFIRMAR</button>
            <button class="modal-btn modal-btn-cancel" onclick="cerrarModal('modal-tarjeta')">✕ CANCELAR</button>
        </div>
    </div>
</div>

<div id="modal-efectivo" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">💵 PAGO EN EFECTIVO</div>
        <div class="info-sandbox">⚠️ Modo Sandbox - Confirma para continuar con tu orden</div>
        
        <div style="background:#2b2b2b; padding:15px; border-radius:8px; border-left:5px solid #ffeb3b; margin-bottom:20px;">
            <p style="color:#ffeb3b; margin:0 0 10px 0; font-weight:bold;">Instrucciones de Pago:</p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">✓ El pago se realiza al recibir tu orden</p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">✓ Ten el dinero exacto listo</p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">✓ El repartidor confirmará el monto</p>
        </div>
        
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-confirm" onclick="confirmarEfectivo()">✓ CONFIRMAR PAGO EN EFECTIVO</button>
            <button class="modal-btn modal-btn-cancel" onclick="cerrarModal('modal-efectivo')">✕ CANCELAR</button>
        </div>
    </div>
</div>

<div id="modal-transferencia" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">🔗 TRANSFERENCIA CRYPTO</div>
        <div class="info-sandbox">⚠️ Modo Sandbox - Ingresa un ID de transacción para continuar</div>
        
        <div style="background:#2b2b2b; padding:15px; border-radius:8px; border-left:5px solid #00bcd4; margin-bottom:20px;">
            <p style="color:#00bcd4; margin:0 0 10px 0; font-weight:bold;">Información de Transferencia:</p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">Billetera: <span style="color:#ffeb3b; font-family:monospace;">1A1z7agoat2GPFH9khqjhjgsjh2jk</span></p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">Red: Bitcoin Network</p>
            <p style="color:#fff; margin:5px 0; font-size:14px;">Confirma tu pago con el ID de transacción</p>
        </div>
        
        <div class="modal-field">
            <label class="modal-label">ID de Transacción (TX Hash)</label>
            <input type="text" id="transferencia-id" class="modal-input" placeholder="abc123def456..." maxlength="100">
        </div>
        
        <div class="modal-buttons">
            <button class="modal-btn modal-btn-confirm" onclick="confirmarTransferencia()">✓ CONFIRMAR TRANSFERENCIA</button>
            <button class="modal-btn modal-btn-cancel" onclick="cerrarModal('modal-transferencia')">✕ CANCELAR</button>
        </div>
    </div>
</div>

<script>
function abrirModalTarjeta() {
    document.getElementById('modal-tarjeta').classList.add('active');
}

function abrirModalEfectivo() {
    document.getElementById('modal-efectivo').classList.add('active');
}

function abrirModalTransferencia() {
    document.getElementById('modal-transferencia').classList.add('active');
}

function cerrarModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.querySelectorAll('input[name="metodo_pago"]').forEach(r => r.checked = false);
}

function confirmarTarjeta() {
    const numero = document.getElementById('tarjeta-numero').value.replace(/\s+/g, '');
    const titular = document.getElementById('tarjeta-titular').value.trim();
    const vencimiento = document.getElementById('tarjeta-vencimiento').value.trim();
    const cvv = document.getElementById('tarjeta-cvv').value.trim();

    if (!numero || numero.length < 13) {
        alert('Número de tarjeta inválido');
        return;
    }
    if (!titular) {
        alert('Nombre del titular es requerido');
        return;
    }
    if (!vencimiento || !vencimiento.includes('/')) {
        alert('Formato de vencimiento inválido (MM/AA)');
        return;
    }
    if (!cvv || cvv.length < 3) {
        alert('CVV inválido');
        return;
    }

    document.getElementById('modal-tarjeta').classList.remove('active');
    document.getElementById('btn-pagar').style.display = 'block';
    document.querySelector('input[value="Tarjeta"]').checked = true;
}

function confirmarEfectivo() {
    document.getElementById('modal-efectivo').classList.remove('active');
    document.getElementById('btn-pagar').style.display = 'block';
    document.querySelector('input[value="Efectivo"]').checked = true;
}

function confirmarTransferencia() {
    const txId = document.getElementById('transferencia-id').value.trim();

    if (!txId || txId.length < 10) {
        alert('ID de transacción inválido');
        return;
    }

    document.getElementById('modal-transferencia').classList.remove('active');
    document.getElementById('btn-pagar').style.display = 'block';
    document.querySelector('input[value="Transferencia"]').checked = true;
}

document.getElementById('tarjeta-numero').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\s+/g, '');
    let formatted = value.match(/.{1,4}/g) ? value.match(/.{1,4}/g).join(' ') : value;
    e.target.value = formatted;
});

document.getElementById('tarjeta-vencimiento').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    e.target.value = value;
});
</script>

</body>
</html>
