<?php
require __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/connect.php';

requireAdmin('admin_login.php');

// CONSULTAS PARA ESTADÍSTICAS
// Total ventas
$result_total_ventas = $conn->query("SELECT SUM(total) AS total_ventas FROM pedidos WHERE estado='Completado'");
$total_ventas = $result_total_ventas->fetch_assoc()['total_ventas'] ?? 0;

// Pedidos pendientes
$result_pendientes = $conn->query("SELECT COUNT(*) AS pedidos_pendientes FROM pedidos WHERE estado='Pendiente'");
$pedidos_pendientes = $result_pendientes->fetch_assoc()['pedidos_pendientes'] ?? 0;

// Nuevos clientes
$result_clientes = $conn->query("SELECT COUNT(*) AS nuevos_clientes FROM clientes");
$nuevos_clientes = $result_clientes->fetch_assoc()['nuevos_clientes'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard HQ - Comick Burger</title>
<style>
body { background-color: #1a1a1a; color: #fff; font-family: 'Comic Sans MS', cursive; margin: 0; }
.header-hq { background-color: #e53935; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid #ffeb3b; }
.header-hq h1 { color: #fff; margin: 0; text-shadow: 2px 2px #000; }
.header-hq a { color: #ffeb3b; text-decoration: none; font-weight: bold; padding: 5px 10px; border: 2px solid #ffeb3b; border-radius: 5px; transition: background-color 0.3s; }
.header-hq a:hover { background-color: #c62828; }
.sidebar { width: 200px; background-color: #333; height: calc(100vh - 55px); position: fixed; padding-top: 20px; border-right: 3px solid #00bcd4; }
.sidebar a { display: block; padding: 15px; color: #fff; text-decoration: none; border-bottom: 1px solid #444; transition: background-color 0.3s, color 0.3s; }
.sidebar a:hover { background-color: #e53935; color: #ffeb3b; }
.content-hq { margin-left: 220px; padding: 30px; }
.welcome-message { background: #00bcd4; padding: 20px; border-radius: 10px; margin-bottom: 30px; color: #000; font-size: 1.2em; border: 3px solid #ffeb3b; }
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.stat-box { background: #555; padding: 25px; border-radius: 8px; border: 3px solid #e53935; text-align: center; }
.stat-box h3 { margin-top: 0; color: #ffeb3b; }
.stat-box p { font-size: 2em; font-weight: bold; color: #fff; }
</style>
</head>
<body>

<div class="header-hq">
    <h1>COMICK BURGER HQ - DASHBOARD</h1>
    <a href="../login.php">CERRAR SESIÓN (¡ESCAPE!)</a>
</div>

<div class="sidebar">
    <a href="admin_dashboard.php" style="background-color: #e53935; color: #ffeb3b;">📊 Resumen General</a>
    <a href="admin_pedidos.php">🍔 Órdenes de Misión</a>
    <a href="admin_productos.php">🔧 Arsenal de Productos</a>
</div>

<div class="content-hq">
    <div class="welcome-message">
        ¡Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario_admin']); ?></strong>! El HQ está bajo tu control.
    </div>

    <h2>📊 Estadísticas de Batalla</h2>
    <div class="stats-grid">
        <div class="stat-box">
            <h3>Ventas Totales (POW!)</h3>
            <p>$<?php echo number_format($total_ventas, 2); ?></p>
        </div>
        <div class="stat-box">
            <h3>Pedidos Pendientes (¡ZAP!)</h3>
            <p><?php echo $pedidos_pendientes; ?></p>
        </div>
        <div class="stat-box">
            <h3>Nuevos Héroes (Clientes)</h3>
            <p><?php echo $nuevos_clientes; ?></p>
        </div>
    </div>
</div>

</body>
</html>
