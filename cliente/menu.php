<?php
session_start();

// Validar que esté logueado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: cliente_login.php");
    exit();
}

$nombre = $_SESSION['cliente_nombre'] ?? "Cliente";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Menú principal</title>

<style>
body{
    background:#0a0a0a;
    color:white;
    font-family:Arial;
    text-align:center;
    padding:20px;
}

/* Título */
h1{
    font-size:40px;
    margin-bottom:20px;
}

/* GRID */
.grid{
    width:90%;
    max-width:900px;
    margin:auto;
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap:25px;
}

/* TARJETA */
.card{
    background:#1f1f1f;
    border-radius:15px;
    padding:20px;
    box-shadow:0 0 15px rgba(0,0,0,0.7);
    transition:.3s;
}

.card:hover{
    transform: scale(1.05);
}

/* IMAGEN */
.card img{
    width:100%;
    height:160px;
    object-fit:cover;
    border-radius:12px;
}

/* BOTÓN */
.btn{
    background:orange;
    border:none;
    padding:12px;
    width:100%;
    margin-top:15px;
    border-radius:10px;
    font-size:18px;
    font-weight:bold;
    color:black;
    cursor:pointer;
    text-decoration:none;
    display:block;
}

.btn:hover{
    background:#ff9500;
}

/* LOGO */
.logo{
    width:150px;
    margin-bottom:20px;
}

</style>
</head>
<body>

<img src="assets/logo1.jpg" class="logo">

<h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?></h1>

<div class="grid">

    <!-- 1. Ver Galería -->
    <div class="card">
        <img src="assets/menu_galeria.jpg" alt="Galería">
        <a href="galeria.php" class="btn">🍔 Ver Galería</a>
    </div>

    <!-- 2. Mis pedidos -->
    <div class="card">
        <img src="assets/menu_pedidos.jpg" alt="Pedidos">
        <a href="historial_pedidos.php" class="btn">📦 Mis pedidos</a>
    </div>

    <!-- 3. Carrito -->
    <div class="card">
        <img src="assets/menu_carrito.jpg" alt="Carrito">
        <a href="carrito.php" class="btn">🛒 Mi carrito</a>
    </div>

    <!-- 4. Cerrar sesión -->
    <div class="card">
        <img src="assets/menu_salir.jpg" alt="Salir">
        <a href="logout.php" class="btn">🚪 Cerrar sesión</a>
    </div>

</div>

</body>
</html>
