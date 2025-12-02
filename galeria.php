<?php
require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/session.php';

use App\Models\Producto;

$productoModel = new Producto();
$categoria = $_GET['categoria'] ?? 'todos';

if ($categoria === 'todos') {
    $productos = $productoModel->findAll();
} else {
    $productos = $productoModel->findByCategory($categoria);
}

$carrito_count = getCarritoCount();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Comick Burger - Galería</title>
<link rel="icon" type="image/png" href="assets/logo1.png">
<style>
body {
    background-color: #111;
    font-family: 'Comic Sans MS', cursive, sans-serif;
    color: #fff;
    margin: 0;
    padding: 0;
}
header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    background: #000;
    border-bottom: 4px solid #ff9800;
}
header img {
    width: 100px;
    height: auto;
}
header h1 {
    font-size: 30px;
    margin: 0;
    text-shadow: 2px 2px #ff0000;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
    text-decoration: none;
    margin: 5px;
    transition: transform 0.2s, background-color 0.3s;
}
.btn:hover { transform: scale(1.05); }
.btn-orange { background-color: #ff9800; color: #fff; }
.btn-orange:hover { background-color: darkorange; }
.btn-green { background-color: #d34e0bff; color: #fff; }
.btn-green:hover { background-color: #e7b707ff; }

.container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    padding: 20px;
    gap: 20px;
}

.card {
    background-color: #222;
    border-radius: 15px;
    padding: 15px;
    width: 220px;
    box-shadow: 4px 4px 0px #ff0000, -4px -4px 0px #0000ff;
    text-align: center;
    transition: transform 0.2s;
}
.card:hover { transform: scale(1.05); }
.card img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid #ff9800;
    margin-bottom: 10px;
}
.card h2 { font-size: 20px; margin: 10px 0 5px; text-shadow: 1px 1px #000; }
.card p { font-size: 14px; margin: 5px 0 10px; }
.card .price { font-weight: bold; margin-bottom: 10px; font-size: 16px; }

@media (max-width: 600px) {
    .card { width: 90%; }
    header h1 { font-size: 22px; }
}
</style>
</head>

<body>

<header>
    <div style="display:flex; align-items:center; gap:20px;">
        <img src="assets/logo1.png" alt="Comick Burger">
        <h1>Galería de productos</h1>
    </div>
    <div>
        <a href="index.php" class="btn btn-orange">⬅ Inicio</a>
        <a href="carrito.php" class="btn btn-orange">🛒 Carrito (<?php echo $carrito_count; ?>)</a>
    </div>
</header>

<div class="container">
<?php foreach($productos as $row): ?>
    <div class="card">
        <?php 
        $img_final = "assets/" . $row['imagen'];
        $img_server_path = __DIR__ . "/assets/" . $row['imagen'];

        if (empty($row['imagen']) || !file_exists($img_server_path)) {
            $img_final = "assets/Hamburguesa_clasica.jpg";
        }
        ?>

        <img src="<?php echo $img_final; ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">

        <h2><?php echo htmlspecialchars($row['nombre']); ?></h2>
        <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
        <div class="price">$<?php echo number_format($row['precio'],2); ?> MXN</div>

        <form action="agregar_carrito.php" method="POST">
            <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
            <input type="hidden" name="cantidad" value="1">
            <button type="submit" class="btn btn-orange">Agregar al carrito</button>
        </form>
    </div>
<?php endforeach; ?>
</div>

</body>
</html>
