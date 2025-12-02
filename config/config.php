<?php

// Configuración de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

return [
    'db_host' => 'localhost',
    'db_user' => 'root',
    'db_pass' => '',
    'db_name' => 'burger',
    'db_charset' => 'utf8mb4',
    
    'session_lifetime' => 7200,
    'session_name' => 'comick_burger_session',
    
    'max_login_attempts' => 5,
    'login_block_time' => 900,
    
    'upload_path' => __DIR__ . '/../assets/productos/',
    'upload_max_size' => 5242880,
    'upload_allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
];
