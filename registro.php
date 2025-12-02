<?php
include_once 'config/config.php';
require_once 'includes/session.php';
require 'includes/connect.php';
require 'includes/helpers.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = sanitizeInput($_POST['nombre'] ?? '', 100);
    $celular = sanitizeInput($_POST['celular'] ?? '', 20);
    $direccion = sanitizeInput($_POST['direccion'] ?? '', 200);
    $email = validateEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password) || empty($celular) || empty($direccion)) {
        $message = "Por favor completa todos los campos.";
    } elseif (!$email) {
        $message = "El correo no es válido.";
    } elseif (!validatePassword($password)) {
        $message = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($password !== $confirm_password) {
        $message = "Las contraseñas no coinciden.";
    } else {
        $stmt = $conn->prepare("SELECT id_cliente FROM clientes WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Ya existe una cuenta con este correo. Intenta iniciar sesión.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt_insert = $conn->prepare("INSERT INTO clientes (nombre, celular, direccion, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("sssss", $nombre, $celular, $direccion, $email, $hash);

            if ($stmt_insert->execute()) {
                $cliente = [
                    'id_cliente' => $stmt_insert->insert_id,
                    'nombre' => $nombre,
                    'email' => $email
                ];
                setClienteSession($cliente);

                redirect(isset($_SESSION['carrito']) && !empty($_SESSION['carrito']) ? 'carrito.php' : 'galeria.php');
            } else {
                $message = "Error al registrar el usuario.";
            }

            $stmt_insert->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registro - Comick Burger</title>
<style>
/* Diseño igual al login */
body {
    margin: 0;
    padding: 0;
    font-family: 'Comic Sans MS', cursive;
    background-color: #111;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    height: 100vh;
    padding-top: 60px;
}

.form-container {
    background: #222;
    padding: 30px 25px 40px 25px;
    border-radius: 15px;
    width: 360px;
    text-align: center;
    color: #fff;
    border: 6px solid #ff9800;
    box-shadow: 10px 10px 0 #e53935;
}

.form-container img {
    width: 120px;
    margin: 10px auto 15px auto;
    display: block;
    border: 3px solid #ffeb3b;
    border-radius: 50%;
    box-shadow: 4px 4px 0 #e53935;
    background-color: #000;
}

.form-container h2 {
    font-size: 28px;
    color: #00bcd4;
    text-shadow: 3px 3px #000;
    margin-bottom: 20px;
}

.form-group {
    text-align: left;
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
    color: #ffeb3b;
    text-shadow: 1px 1px #000;
}

.form-container input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: none;
    border-bottom: 3px solid #ff9800;
    background: #333;
    color: #fff;
    font-size: 0.95em;
    box-sizing: border-box;
    outline: none;
    transition: border-bottom-color 0.2s;
}

.form-container input:focus {
    border-bottom-color: #00bcd4;
}

.form-container button {
    width: 100%;
    padding: 12px;
    background-color: #e53935;
    border: 3px solid #000;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    color: #fff;
    font-size: 18px;
    margin-top: 15px;
    box-shadow: 4px 4px 0 #000;
    transition: all 0.1s;
}

.form-container button:hover {
    background-color: #c62828;
    transform: translate(2px,2px);
    box-shadow: 2px 2px 0 #000;
}

.form-container p {
    margin-top: 25px;
    font-size: 0.9em;
    color: #fff;
}
.form-container a {
    color: #ffeb3b;
    text-decoration: underline;
    font-weight: bold;
    text-shadow: 1px 1px #000;
}

.form-container .message {
    color: #e53935;
    background: #333;
    border: 2px dashed #e53935;
    padding: 8px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: bold;
    font-size: 0.95em;
}
</style>
</head>

<body>

<div class="form-container">
    <img src="assets/bum.png" alt="Comick Burger Logo">
    <h2>REGISTRO AL ESCUADRÓN</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Nombre completo</label>
            <input type="text" name="nombre" required>
        </div>

        <div class="form-group">
            <label>Celular</label>
            <input type="text" name="celular" required>
        </div>

        <div class="form-group">
            <label>Dirección</label>
            <input type="text" name="direccion" required>
        </div>

        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input type="password" name="confirm_password" required>
        </div>

        <button type="submit">¡CREAR MI CUENTA!</button>
    </form>

    <p>¿Ya tienes cuenta? <a href="login.php">¡INICIA SESIÓN AQUÍ!</a></p>
</div>

</body>
</html>
