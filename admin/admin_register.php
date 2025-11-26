<?php
session_start();
require '../includes/connect.php'; // Ajusta la ruta según tu estructura

// Función de protección
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header("Location: admin_login.php");
        exit();
    }
}
check_admin_auth();

$message = "";

// Procesar registro de nuevo admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $message = "¡ALTO! Todos los campos son obligatorios.";
    } elseif ($password !== $confirm_password) {
        $message = "¡ERROR! Las CLAVES SECRETAS no coinciden.";
    } else {
        // Verificar si ya existe el email
        $stmt_check = $conn->prepare("SELECT id_admin FROM administradores WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "¡ALERTA! Este correo ya está registrado.";
        } else {
            // Insertar nuevo admin
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $conn->prepare("INSERT INTO administradores (email, password) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $email, $password_hash);
            if ($stmt_insert->execute()) {
                $message = "¡KAPOW! Administrador registrado con éxito.";
            } else {
                $message = "¡ERROR! No se pudo registrar: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registro HQ - Comick Burger</title>
<style>
body { background-color: #000; color: #fff; font-family: 'Comic Sans MS', cursive; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
.register-box { background: #1c1c1c; padding: 40px; border-radius: 15px; border: 6px solid #ffeb3b; box-shadow: 12px 12px 0 #e53935; width: 100%; max-width: 420px; text-align: center; }
h1 { color: #00bcd4; font-size: 30px; text-shadow: 4px 4px #ff9800; margin-bottom: 30px; border-bottom: 3px dashed #ffeb3b; padding-bottom: 10px; }
input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 8px; border: 4px solid #00bcd4; background: #0d0d0d; color: #fff; font-size: 1.1em; }
button { width: 100%; padding: 15px 20px; border: none; border-radius: 10px; background: #e53935; color: #fff; font-weight: bold; cursor: pointer; font-size: 1.2em; box-shadow: 6px 6px 0 #00bcd4; margin-top: 20px; transition: transform 0.1s; }
button:hover { background: #c62828; transform: translate(-3px, -3px); box-shadow: 9px 9px 0 #0d47a1; }
.message { background: #ffeb3b; color: #e53935; margin-bottom: 20px; padding: 10px; border-radius: 5px; font-weight: bold; border: 2px dashed #e53935; }
</style>
</head>
<body>

<div class="register-box">
    <h1>REGISTRO DE ADMIN</h1>

    <?php if(!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" placeholder="CORREO DE ADMIN" required>
        <input type="password" name="password" placeholder="CLAVE SECRETA" required>
        <input type="password" name="confirm_password" placeholder="CONFIRMAR CLAVE" required>
        <button type="submit">¡REGISTRAR ADMIN!</button>
    </form>
</div>

</body>
</html>
