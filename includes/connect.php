<?php

$config = require __DIR__ . '/../config/config.php';

$conn = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($conn->connect_error) {
    die('Conexión fallida: ' . $conn->connect_error);
}

$conn->set_charset($config['db_charset']);

?>
