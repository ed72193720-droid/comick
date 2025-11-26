<?php
session_start();
require '../includes/connect.php';

// Protección de sesión de admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Agregar o eliminar producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio'] ?? 0);
        $imagen = $_FILES['imagen']['name'] ?? '';

        if ($nombre && $precio > 0) {

            // Subir imagen
            if ($imagen && $_FILES['imagen']['error'] === 0) {

                $target_dir = "../imagenes/"; // CORRECTO (sube un nivel)
                
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                $ext = strtolower(pathinfo($imagen, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                if (in_array($ext, $allowed_ext)) {

                    $new_name = uniqid('prod_') . '.' . $ext;
                    $target_file = $target_dir . $new_name;

                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
                        $imagen = $new_name;
                    } else {
                        $imagen = '';
                    }

                } else {
                    $imagen = '';
                }

            } else {
                $imagen = '';
            }

            $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssds", $nombre, $descripcion, $precio, $imagen);
            $stmt->execute();
            $stmt->close();
        }

    } elseif ($_POST['action'] === 'delete') {

        $id_producto = intval($_POST['id_producto']);

        if ($id_producto > 0) {
            // Buscar la imagen
            $stmt = $conn->prepare("SELECT imagen FROM productos WHERE id_producto=?");
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $stmt->bind_result($img_borrar);
            $stmt->fetch();
            $stmt->close();

            // Borrar imagen física
            if ($img_borrar && file_exists("../imagenes/" . $img_borrar)) {
                unlink("../imagenes/" . $img_borrar);
            }

            // Eliminar registro
            $stmt = $conn->prepare("DELETE FROM productos WHERE id_producto=?");
            $stmt->bind_param("i", $id_producto);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: admin_productos.php");
    exit();
}

// Obtener productos
$productos = [];
$result = $conn->query("SELECT * FROM productos ORDER BY id_producto DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Arsenal de Productos - HQ</title>

<style>
body { background-color: #1a1a1a; color: #fff; font-family: 'Comic Sans MS', cursive; margin: 0; }
.header-hq { background-color: #e53935; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; border-bottom: 5px solid #ffeb3b; }
.header-hq h1 { color: #fff; margin: 0; text-shadow: 2px 2px #000; }
.header-hq a { color: #ffeb3b; text-decoration: none; font-weight: bold; padding: 5px 10px; border: 2px solid #ffeb3b; border-radius: 5px; transition: 0.3s; }
.header-hq a:hover { background-color: #c62828; }

.sidebar { width: 200px; background-color: #333; height: calc(100vh - 55px); position: fixed; padding-top: 20px; border-right: 3px solid #00bcd4; }
.sidebar a { display: block; padding: 15px; color: #fff; text-decoration: none; border-bottom: 1px solid #444; transition: 0.3s; }
.sidebar a:hover { background-color: #e53935; color: #ffeb3b; }

.content-hq { margin-left: 220px; padding: 30px; }
h2 { color: #00bcd4; text-shadow: 2px 2px #e53935; margin-bottom: 20px; }

form { margin-bottom: 30px; }
input, button { padding: 10px; margin: 5px 0; border-radius: 5px; font-family: 'Comic Sans MS', cursive; }
input { border: 3px solid #00bcd4; background: #0d0d0d; color: #fff; }
button { background: #e53935; color: #fff; border: none; font-weight: bold; cursor: pointer; transition: 0.2s; }
button:hover { background: #c62828; transform: translate(-2px, -2px); }

table { width: 100%; border-collapse: collapse; margin-top: 20px; }
th, td { border: 2px solid #555; padding: 12px; text-align: left; }
th { background-color: #e53935; color: #ffeb3b; text-transform: uppercase; }
tr:nth-child(even) { background-color: #2a2a2a; }
img { max-width: 100px; border-radius: 5px; }

button.delete-btn { background-color: #ff5722; }
button.delete-btn:hover { background-color: #f44336; }
</style>

</head>
<body>
<div class="header-hq">
    <div style="display:flex; align-items:center;">
        <img src="../imagenes/bum.png" alt="Logo" style="height:50px; margin-right:15px; border-radius:8px;">
        <h1>ÓRDENES DE MISIÓN (PEDIDOS)</h1>
    </div>
</div>
<div class="header-hq">
    <h1>🔧 Arsenal de Productos</h1>
    <a href="admin_logout.php">CERRAR SESIÓN</a>
</div>

<div class="sidebar">
    <a href="admin_dashboard.php">📊 Resumen General</a>
    <a href="admin_pedidos.php">🍔 Órdenes de Misión</a>
    <a href="admin_productos.php" style="background-color:#e53935;color:#ffeb3b;">🔧 Arsenal de Productos</a>
</div>

<div class="content-hq">
    <h2>➕ Agregar Nuevo Producto</h2>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">

        <input type="text" name="nombre" placeholder="Nombre del producto" required><br>
        <input type="text" name="descripcion" placeholder="Descripción"><br>
        <input type="number" step="0.01" name="precio" placeholder="Precio" required><br>
        <input type="file" name="imagen" accept="image/*"><br>

        <button type="submit">¡AGREGAR PRODUCTO!</button>
    </form>

    <h2>🛒 Productos Actuales</h2>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Imagen</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($productos as $producto): ?>
            <tr>
                <td><?php echo $producto['id_producto']; ?></td>
                <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td>$<?php echo number_format($producto['precio'], 2); ?></td>

                <td>
                    <?php 
                    if ($producto['imagen']) {
                        echo '<img src="../imagenes/' . $producto['imagen'] . '" alt="imagen">';
                    } else {
                        echo 'Sin imagen';
                    }
                    ?>
                </td>

                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                        <button type="submit" class="delete-btn">🗑️ ELIMINAR</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

</div>

</body>
</html>
