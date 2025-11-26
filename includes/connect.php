<?php
$servername = "localhost";
$username = "root"; // tu usuario de MySQL
$password = "";     // tu contraseña de MySQL
$dbname = "burger"; // tu base de datos

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
