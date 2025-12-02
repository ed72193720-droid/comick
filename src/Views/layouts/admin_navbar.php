<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="admin_dashboard.php">🍔 Panel Admin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_pedidos.php">Pedidos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <span class="nav-link">Admin: <?= htmlspecialchars($_SESSION['admin_nombre']) ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_logout.php">Cerrar Sesión</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
