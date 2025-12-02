<?php

namespace App\Controllers;

use App\Models\Cliente;

class AuthController extends BaseController {
    private $clienteModel;
    
    public function __construct() {
        $this->clienteModel = new Cliente();
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processLogin();
        }
        
        $this->render('cliente/login', ['message' => '']);
    }
    
    private function processLogin() {
        require_once __DIR__ . '/../../includes/helpers.php';
        
        $email = validateEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || empty($password)) {
            $this->render('cliente/login', ['message' => 'Por favor ingresa un correo válido y contraseña.']);
            return;
        }

        if (!checkLoginAttempts($email)) {
            $this->render('cliente/login', ['message' => 'Demasiados intentos fallidos. Intenta más tarde.']);
            return;
        }

        $cliente = $this->clienteModel->authenticate($email, $password);

        if ($cliente) {
            registerLoginAttempt($email, true);
            setClienteSession($cliente);
            
            $redirect = isset($_SESSION['carrito']) && !empty($_SESSION['carrito']) ? 'carrito.php' : 'galeria.php';
            $this->redirect($redirect);
        } else {
            registerLoginAttempt($email, false);
            $this->render('cliente/login', ['message' => 'Credenciales incorrectas.']);
        }
    }
    
    public function registro() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->processRegistro();
        }
        
        $this->render('cliente/registro', ['message' => '']);
    }
    
    private function processRegistro() {
        require_once __DIR__ . '/../../includes/helpers.php';
        
        $nombre = sanitizeInput($_POST['nombre'] ?? '', 100);
        $celular = sanitizeInput($_POST['celular'] ?? '', 20);
        $direccion = sanitizeInput($_POST['direccion'] ?? '', 200);
        $email = validateEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($nombre) || empty($email) || empty($password) || empty($celular) || empty($direccion)) {
            $this->render('cliente/registro', ['message' => 'Por favor completa todos los campos.']);
            return;
        }

        if (!$email) {
            $this->render('cliente/registro', ['message' => 'El correo no es válido.']);
            return;
        }

        if (!validatePassword($password)) {
            $this->render('cliente/registro', ['message' => 'La contraseña debe tener al menos 6 caracteres.']);
            return;
        }

        if ($password !== $confirm_password) {
            $this->render('cliente/registro', ['message' => 'Las contraseñas no coinciden.']);
            return;
        }

        if ($this->clienteModel->emailExists($email)) {
            $this->render('cliente/registro', ['message' => 'Ya existe una cuenta con este correo.']);
            return;
        }

        $clienteId = $this->clienteModel->create([
            'nombre' => $nombre,
            'celular' => $celular,
            'direccion' => $direccion,
            'email' => $email,
            'password' => $password
        ]);

        if ($clienteId) {
            setClienteSession([
                'id_cliente' => $clienteId,
                'nombre' => $nombre,
                'email' => $email
            ]);

            $redirect = isset($_SESSION['carrito']) && !empty($_SESSION['carrito']) ? 'carrito.php' : 'galeria.php';
            $this->redirect($redirect);
        } else {
            $this->render('cliente/registro', ['message' => 'Error al registrar el usuario.']);
        }
    }
    
    public function logout() {
        destroySession();
        $this->redirect('index.php');
    }
}
