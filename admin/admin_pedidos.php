<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión segura
require __DIR__ . '/../includes/connect.php'; 

// Protección de sesión de administrador
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: admin_login.php");
        exit();
    }
}
check_admin_auth();

// Actualizar estado de pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $id_pedido = intval($_POST['id_pedido'] ?? 0);
    $nuevo_estado = $_POST['estado'] ?? 'Pendiente';

    if ($id_pedido > 0) {
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
        $stmt->bind_param("si", $nuevo_estado, $id_pedido);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: admin_pedidos.php");
    exit();
}

// Obtener pedidos
$pedidos = [];
$result = $conn->query("SELECT id_pedido, id_cliente, fecha, total, metodo_pago, estado FROM pedidos ORDER BY fecha DESC");

if ($result) {
    // Verificar si existe la tabla detalle_pedidos
    $tabla_detalle = $conn->query("SHOW TABLES LIKE 'detalle_pedidos'")->num_rows == 1;

    while ($pedido = $result->fetch_assoc()) {
        $imagen_producto = null;

        if ($tabla_detalle) {
            $stmt = $conn->prepare("
                SELECT pr.imagen 
                FROM detalle_pedidos dp 
                JOIN productos pr ON dp.id_producto = pr.id_producto 
                WHERE dp.id_pedido = ? LIMIT 1
            ");
            $stmt->bind_param("i", $pedido['id_pedido']);
            $stmt->execute();
            $stmt->bind_result($imagen_producto);
            $stmt->fetch();
            $stmt->close();
        }

        $pedido['imagen'] = $imagen_producto;
        $pedidos[] = $pedido;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Órdenes de Misión - HQ</title>
<style>
body { background-color: #1a1a1a; color: #fff; font-family: 'Comic Sans MS', cursive; margin: 0; }
.header-hq { background-color: #e53935; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid #ffeb3b; }
.header-hq h1 { color: #fff; margin: 0; text-shadow: 2px 2px #000; }
.sidebar { width: 200px; background-color: #333; height: calc(100vh - 55px); position: fixed; padding-top: 20px; border-right: 3px solid #00bcd4; }
.sidebar a { display: block; padding: 15px; color: #fff; text-decoration: none; border-bottom: 1px solid #444; transition: 0.3s; }
.sidebar a:hover { background-color: #e53935; color: #ffeb3b; }
.content-hq { margin-left: 220px; padding: 30px; }
h2 { color: #00bcd4; text-shadow: 2px 2px #e53935; margin-bottom: 20px; }
table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 2px solid #555; padding: 12px; text-align: left; vertical-align: middle; }
th { background-color: #e53935; color: #ffeb3b; text-transform: uppercase; }
tr:nth-child(even) { background-color: #2a2a2a; }
.status-pendiente { color: #ffeb3b; font-weight: bold; }
.status-completado { color: #28a745; font-weight: bold; }
.status-cancelado { color: #f44336; font-weight: bold; }
.action-form select, .action-form button { padding: 5px; border-radius: 5px; font-weight: bold; font-size: 0.95em; }
.action-form button { background-color: #00bcd4; border: none; cursor: pointer; color: #000; transition: transform 0.1s, box-shadow 0.1s; }
.action-form button:hover { transform: translate(-2px, -2px); box-shadow: 3px 3px #000; }
img.producto-img { max-width: 60px; border-radius: 5px; }
</style>
</head>
<body>
<div class="header-hq">
    <h1>ÓRDENES DE MISIÓN (PEDIDOS)</h1>
</div>

<div class="sidebar">
    <a href="admin_dashboard.php">📊 Resumen General</a>
    <a href="admin_pedidos.php" style="background-color: #e53935; color: #ffeb3b;">🍔 Órdenes de Misión</a>
    <a href="admin_productos.php">🔧 Arsenal de Productos</a>
</div>

<div class="content-hq">
    <h2>🍔 Órdenes en Espera</h2>

    <?php if (empty($pedidos)): ?>
        <p style="color: #ffeb3b;">¡Alerta! No hay misiones (pedidos) pendientes. El HQ está tranquilo.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>ID Cliente</th>
                    <th>Fecha de Misión</th>
                    <th>Total</th>
                    <th>Método</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['id_cliente']); ?></td>
                    <td><?php echo htmlspecialchars($pedido['fecha']); ?></td>
                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                    <td><?php echo htmlspecialchars($pedido['metodo_pago']); ?></td>
                    <td>
                        <?php if ($pedido['imagen'] && file_exists(__DIR__ . "/../uploads/".$pedido['imagen'])): ?>
                            <img src="../uploads/<?php echo $pedido['imagen']; ?>" class="producto-img" alt="Producto">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="status-<?php echo strtolower(trim($pedido['estado'])); ?>">
                        <?php echo htmlspecialchars($pedido['estado']); ?>
                    </td>
                    <td>
                        <form method="POST" class="action-form">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                            <select name="estado">
                                <option value="Pendiente" <?php echo ($pedido['estado'] == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="Completado" <?php echo ($pedido['estado'] == 'Completado') ? 'selected' : ''; ?>>Completado</option>
                                <option value="Cancelado" <?php echo ($pedido['estado'] == 'Cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                            <button type="submit">¡ACTUALIZAR!</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>

