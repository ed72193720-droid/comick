<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comick Burger - Inicio</title>
<link rel="stylesheet" href="css/estilos.css">
<style>
/* Estilos solo para Index */
body {
    background-color: #000;
    color: #fff;
    font-family: 'Comic Sans MS', cursive;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
    background-image: url('imagenes/fondo_index.jpg'); /* Asegúrate de tener una imagen */
    background-size: cover;
    background-position: center;
}
.main-content {
    background: rgba(0,0,0,0.8);
    padding: 40px;
    border-radius: 20px;
    text-align: center;
    border: 5px solid #ff9800;
    box-shadow: 0 0 20px rgba(255, 152, 0, 0.5);
}
.main-content img {
    width: 250px;
    margin-bottom: 20px;
}
h1 {
    color: #ff9800;
    font-size: 40px;
    text-shadow: 4px 4px red;
    margin-bottom: 30px;
}
.nav-buttons a {
    display: block;
    width: 250px;
    padding: 15px;
    margin: 15px auto;
    background-color: #ff9800;
    color: #000;
    text-decoration: none;
    border-radius: 10px;
    font-size: 20px;
    font-weight: bold;
    transition: background-color 0.2s, transform 0.2s;
    box-shadow: 3px 3px 0 #e65100;
}
.nav-buttons a:hover {
    background-color: darkorange;
    transform: translateY(-2px);
}
.logout { background-color: #e53935 !important; color: #fff !important; box-shadow: 3px 3px 0 #b71c1c; }
.logout:hover { background-color: #c62828 !important; }
</style>
</head>
<body>

<div class="main-content">
    <img src="imagenes/logo1.png" alt="Comick Burger Logo">
    <h1>¡Bienvenido a Comick Burger!</h1>
    
    <div class="nav-buttons">
        <!-- El botón "Menú" ahora dirige a la galería de productos -->
        <a href="galeria.php">🍔 Ver Menú / Galería</a>
        
        <?php if (isset($_SESSION['id_cliente'])): ?>
            <!-- Si está logueado, ve el botón de cerrar sesión -->
            <a href="cerrar.php" class="logout">🚪 Cerrar Sesión</a>
        <?php else: ?>
             <!-- Si no está logueado, puede ir a la página unificada de autenticación -->
            <a href="galeria.php">🔑 Iniciar / Registrarse</a>
        <?php endif; ?>

        <!-- El carrito se accede desde la galería -->
        
    </div>
</div>

</body>
</html>
