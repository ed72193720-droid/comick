<?php
session_start();
require 'includes/connect.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['cantidad']) && isset($_POST['nombre']) && isset($_POST['precio'])) {
    
    // 2. Saneamiento y validación de los datos
    // Es CRÍTICO asegurarse de que los datos son del tipo esperado.
    $id_producto = filter_var($_POST['id'], FILTER_VALIDATE_INT);
    $cantidad = filter_var($_POST['cantidad'], FILTER_VALIDATE_INT);
    $nombre = htmlspecialchars(trim($_POST['nombre'])); // Sanear el nombre como string
    $precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);

    // 3. Procesar si los datos son válidos y la cantidad es positiva
    if ($id_producto !== false && $cantidad !== false && $cantidad > 0 && $precio !== false && $precio >= 0) {
        
        // 4. Inicializar el carrito en la sesión si aún no existe
        // Usaremos el ID del producto como clave del array principal para facilitar la búsqueda.
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // 5. Lógica de adición:
        if (isset($_SESSION['carrito'][$id_producto])) {
            // El producto ya existe: sumar la nueva cantidad.
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            // El producto es nuevo: añadirlo con todos sus detalles.
            $_SESSION['carrito'][$id_producto] = [
                'id' => $id_producto,
                'nombre' => $nombre,
                'precio' => $precio,
                'cantidad' => $cantidad,
            ];
        }
        
        // Opcional: Recalcular el subtotal total del ítem.
        $_SESSION['carrito'][$id_producto]['subtotal'] = $_SESSION['carrito'][$id_producto]['precio'] * $_SESSION['carrito'][$id_producto]['cantidad'];
    } else {
        // Manejo de error de datos inválidos
    }
}

header('Location: carrito.php');
exit;
?>