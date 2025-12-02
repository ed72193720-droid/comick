<?php

if (session_status() === PHP_SESSION_NONE) {
    $config = require __DIR__ . '/../config/config.php';
    
    if ($config && is_array($config)) {
        ini_set('session.gc_maxlifetime', $config['session_lifetime']);
        session_set_cookie_params($config['session_lifetime']);
        session_name($config['session_name']);
    }
    
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['cliente_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit();
    }
}

function requireAdmin($redirectTo = 'admin/admin_login.php') {
    if (!isAdmin()) {
        header("Location: $redirectTo");
        exit();
    }
}

function setClienteSession($cliente) {
    $_SESSION['cliente_id'] = $cliente['id_cliente'];
    $_SESSION['cliente_nombre'] = $cliente['nombre'];
    $_SESSION['cliente_email'] = $cliente['email'] ?? '';
}

function setAdminSession($admin) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['admin_id'] = $admin['id_admin'];
    $_SESSION['usuario_admin'] = $admin['username'];
}

function destroySession() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

function getCarritoCount() {
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return 0;
    }
    
    $count = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $count += intval($item['cantidad']);
    }
    return $count;
}

function getCarritoTotal() {
    if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
        return 0;
    }
    
    $total = 0;
    foreach ($_SESSION['carrito'] as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return $total;
}
