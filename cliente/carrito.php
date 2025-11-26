<?php
session_start();
include 'includes/connect.php';

// Inicialización
$carrito = $_SESSION['carrito'] ?? [];
$total_orden = 0;

// Si el carrito está vacío, eliminar variable de sesión
if (empty($carrito)) {
    unset($_SESSION['carrito']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>El Botín - Carrito de Comick Burger</title>
<link rel="stylesheet" href="css/estilos.css">
<style>
body { background-color: #111; color: #fff; font-family: 'Comic Sans MS', cursive; margin:0; padding:0; }
header { background: #000; border-bottom:6px solid #ff9800; padding:15px 20px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 5px 10px rgba(0,0,0,0.5);}
header img { width:90px; }
h1 { margin:0; font-size:35px; color:#ffeb3b; text-shadow:4px 4px #e53935; line-height: 1; }
.top-nav a { padding:10px 20px; text-decoration:none; background:#00bcd4; color:#000; border-radius:10px; font-weight:bold; transition:0.2s; box-shadow: 3px 3px 0 #000; border: 2px solid #000; }
.container { max-width: 900px; margin: 30px auto; padding: 20px; background: #222; border-radius: 15px; border: 5px solid #00bcd4; box-shadow: 10px 10px 0 #ffeb3b; }
.cart-item { display:flex; justify-content: space-between; align-items:center; padding:15px; margin-bottom:10px; background:#333; border-radius:10px; border-left:5px solid #ff9800; transition: transform 0.2s; }
.cart-item:hover { transform: translateX(5px); }
.item-details { flex-grow:1; }
.item-controls { display:flex; align-items:center; gap:10px; }
.item-controls input[type="number"] { width:60px; padding:8px; text-align:center; border-radius:5px; border:2px solid #ffeb3b; background:#444; color:#fff; }
.btn-delete { background:#e53935; color:white; border:none; padding:8px 12px; border-radius:5px; cursor:pointer; font-weight:bold; box-shadow:2px 2px 0 #000; }
.btn-delete:hover { background:#c62828; }
.cart-summary { border-top:3px dashed #ff9800; padding-top:20px; display:flex; justify-content:flex-end; align-items:center; font-size:1.5em; font-weight:bold; }
.cart-summary span { color:#ffeb3b; margin-left:10px; }
.action-buttons { display:flex; justify-content: space-between; margin-top:30px; }
.action-buttons-login-register { display:flex; gap:15px; }
.btn-continue, .btn-checkout, .btn-register { padding:15px 30px; text-decoration:none; border-radius:10px; font-weight:bold; font-size:18px; box-shadow:4px 4px 0 #000; transition: transform 0.2s; border:2px solid #000; }
.btn-continue { background:#00bcd4; color:#000; }
.btn-checkout { background:#ff9800; color:#000; }
.btn-register { background:#ffeb3b; color:#000; }
.btn-continue:hover, .btn-checkout:hover, .btn-register:hover { transform:translateY(-2px); box-shadow:6px 6px 0 #e53935; }
.empty-cart-message { text-align:center; padding:40px; background:#333; border-radius:10px; border:3px solid #e53935; font-size:1.2em; }
</style>
</head>
<body>

<header>
    <img src="imagenes/logo1.png" alt="Logo Comick Burger">
    <h1>EL BOTÍN DE LA MISIÓN</h1>
    <div class="top-nav">
        <a href="registro.php">🦸‍♀️ REGISTRARME </a>
    </div>
</header>

<div class="container">

<?php if(empty($carrito)): ?>
    <div class="empty-cart-message">
        <p>¡Zzzzz! Tu botín está vacío. ¡Empieza la misión de compras!</p>
        <a href="galeria.php" class="btn-continue" style="margin-top:20px;">IR A LA GALERÍA</a>
    </div>
<?php else: ?>

    <?php foreach($carrito as $id => $item): 
        $total_orden += $item['subtotal'];
    ?>
    <div class="cart-item">
        <div class="item-details">
            <h3><?php echo htmlspecialchars($item['nombre']); ?></h3>
            <p>Precio Unitario: $<?php echo number_format($item['precio'],2); ?> MXN</p>
        </div>
        <div class="item-controls">
            <!-- Actualizar cantidad -->
            <form method="POST" action="carrito.php">
                <input type="hidden" name="action" value="actualizar">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="number" name="cantidad" value="<?php echo htmlspecialchars($item['cantidad']); ?>" min="1" onchange="this.form.submit()">
            </form>

            <p>Subtotal: $<?php echo number_format($item['subtotal'],2); ?> MXN</p>

            <!-- Eliminar producto -->
            <form method="POST" action="carrito.php">
                <input type="hidden" name="action" value="eliminar">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <button type="submit" class="btn-delete">❌</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="cart-summary">
        TOTAL DE LA MISIÓN: <span>$<?php echo number_format($total_orden,2); ?> MXN</span>
    </div>

    <div class="action-buttons">
        <a href="galeria.php" class="btn-continue">SEGUIR COMPRANDO</a>

        <?php if(isset($_SESSION['id_cliente'])): ?>
            <a href="metodo_pago.php" class="btn-checkout">IR A LA CAJA DE PAGO &gt;</a>
        <?php else: ?>
            <div class="action-buttons-login-register">
                <a href="registro.php" class="btn-register">¡REGÍSTRATE AHORA!</a>
                <a href="login.php" class="btn-checkout">AUTENTICAR Y PAGAR &gt;</a>
            </div>
        <?php endif; ?>
    </div>

<?php endif; ?>

</div>

</body>
</html>
