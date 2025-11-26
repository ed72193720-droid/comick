<?php
session_start();

if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    if(isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])){
        foreach($_SESSION['carrito'] as $key => &$producto){
            if($producto['id'] == $id){
                // Reducir cantidad en 1
                $producto['cantidad'] -= 1;

                // Si la cantidad llega a 0, eliminar el producto
                if($producto['cantidad'] <= 0){
                    unset($_SESSION['carrito'][$key]);
                }
                break; // solo afectamos la primera coincidencia
            }
        }
        unset($producto); // buena práctica con referencia
        // Reindexar array
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

header("Location: carrito.php");
exit();
