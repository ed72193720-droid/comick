<?php

namespace App\Controllers;

use App\Models\Administrador;
use App\Models\Pedido;
use App\Models\Producto;

class AdminController extends BaseController {
    private $administradorModel;
    private $pedidoModel;
    private $productoModel;
    
    public function __construct() {
        $this->administradorModel = new Administrador();
        $this->pedidoModel = new Pedido();
        $this->productoModel = new Producto();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processLogin();
        }
        
        $this->render('admin/login', ['message' => '']);
    }
    
    private function processLogin() {
        require_once __DIR__ . '/../../includes/helpers.php';
        
        $email = validateEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $this->render('admin/login', ['message' => 'Por favor ingresa un correo válido y contraseña.']);
            return;
        }

        if (!checkLoginAttempts($email)) {
            $this->render('admin/login', ['message' => 'Demasiados intentos fallidos. Intenta más tarde.']);
            return;
        }

        $admin = $this->administradorModel->authenticate($email, $password);

        if ($admin) {
            registerLoginAttempt($email, true);
            setAdminSession($admin);
            $this->redirect('admin_dashboard.php');
        } else {
            registerLoginAttempt($email, false);
            $this->render('admin/login', ['message' => 'Credenciales incorrectas.']);
        }
    }
    
    public function dashboard() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        $pedidos = $this->pedidoModel->findAll();
        
        $estadisticas = [
            'total_pedidos' => count($pedidos),
            'pendientes' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'Pendiente')),
            'completados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'Completado')),
            'cancelados' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'Cancelado'))
        ];
        
        $this->render('admin/dashboard', ['estadisticas' => $estadisticas]);
    }
    
    public function pedidos() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        $pedidos = $this->pedidoModel->findAll();
        
        $this->render('admin/pedidos', ['pedidos' => $pedidos]);
    }
    
    public function actualizarEstadoPedido() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin_pedidos.php');
            return;
        }

        $id_pedido = validateNumber($_POST['id_pedido'] ?? 0);
        $estado = sanitizeInput($_POST['estado'] ?? '', 20);

        if (!$id_pedido || !in_array($estado, ['Pendiente', 'En Proceso', 'Completado', 'Cancelado'])) {
            $this->redirect('admin_pedidos.php');
            return;
        }

        if ($this->pedidoModel->updateEstado($id_pedido, $estado)) {
            $this->redirect('admin_pedidos.php');
        } else {
            $this->redirect('admin_pedidos.php');
        }
    }
    
    public function productos() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        $productos = $this->productoModel->findAll();
        
        $this->render('admin/productos', ['productos' => $productos]);
    }
    
    public function agregarProducto() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin_productos.php');
            return;
        }

        $nombre = sanitizeInput($_POST['nombre'] ?? '', 100);
        $descripcion = sanitizeInput($_POST['descripcion'] ?? '', 500);
        $precio = validateNumber($_POST['precio'] ?? 0);
        $categoria = sanitizeInput($_POST['categoria'] ?? '', 50);

        if (empty($nombre) || $precio <= 0 || empty($categoria)) {
            $this->redirect('admin_productos.php');
            return;
        }

        $imagen = 'default.jpg';
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $uploadedImage = uploadImage($_FILES['imagen'], 'productos/');
            if ($uploadedImage) {
                $imagen = $uploadedImage;
            }
        }

        $this->productoModel->create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'categoria' => $categoria,
            'imagen' => $imagen
        ]);

        $this->redirect('admin_productos.php');
    }
    
    public function eliminarProducto() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin_productos.php');
            return;
        }

        $id_producto = validateNumber($_POST['id_producto'] ?? 0);

        if (!$id_producto) {
            $this->redirect('admin_productos.php');
            return;
        }

        $this->productoModel->delete($id_producto);
        $this->redirect('admin_productos.php');
    }
    
    public function logout() {
        destroySession();
        $this->redirect('admin_login.php');
    }
}
