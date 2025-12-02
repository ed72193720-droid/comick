<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">🍔 Burger Restaurant</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="galeria.php">Menú</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">🛒 Carrito (<?= getCarritoCount() ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="mis_pedidos.php">Mis Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cerrar_sesion.php">Cerrar Sesión</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Iniciar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-warning" href="registro.php">Registrarse</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
