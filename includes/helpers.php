<?php

function sanitizeInput($data, $maxLength = 255) {
    $data = trim($data);
    $data = strip_tags($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return substr($data, 0, $maxLength);
}

function validateEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validateNumber($numero, $min = null, $max = null) {
    $numero = filter_var($numero, FILTER_VALIDATE_INT);
    
    if ($numero === false) {
        return false;
    }
    
    if ($min !== null && $numero < $min) {
        return false;
    }
    
    if ($max !== null && $numero > $max) {
        return false;
    }
    
    return $numero;
}

function validatePassword($password, $minLength = 6) {
    return strlen($password) >= $minLength;
}

function redirect($location) {
    header("Location: $location");
    exit();
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function uploadImage($file, $targetDir, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Error al subir archivo'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'error' => 'Tipo de archivo no permitido'];
    }
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $filename = uniqid('prod_') . '.' . $ext;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'error' => 'Error al mover archivo'];
}

function checkLoginAttempts($email) {
    $config = include __DIR__ . '/../config/config.php';
    $key = "login_attempts_" . md5($email);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['intentos' => 0, 'ultimo' => time()];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    if (time() - $data['ultimo'] > $config['login_block_time']) {
        $_SESSION[$key] = ['intentos' => 0, 'ultimo' => time()];
        return true;
    }
    
    return $data['intentos'] < $config['max_login_attempts'];
}

function registerLoginAttempt($email, $success = false) {
    $key = "login_attempts_" . md5($email);
    
    if ($success) {
        unset($_SESSION[$key]);
    } else {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['intentos' => 0, 'ultimo' => time()];
        }
        $_SESSION[$key]['intentos']++;
        $_SESSION[$key]['ultimo'] = time();
    }
}
