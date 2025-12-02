<?php
include_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require __DIR__ . '/../includes/connect.php';
require __DIR__ . '/../includes/helpers.php';

if (isAdmin()) {
    redirect('admin_dashboard.php');
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = validateEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) { 
        $message = "¡ALTO! El correo y contraseña son obligatorios.";
    } elseif (!checkLoginAttempts($email)) {
        $message = "Demasiados intentos fallidos. Intenta más tarde.";
    } else {
        $stmt = $conn->prepare("SELECT id_admin, username, email, password FROM administradores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                registerLoginAttempt($email, true);
                setAdminSession($admin);
                redirect('admin_dashboard.php');
            } else {
                registerLoginAttempt($email, false);
                $message = "¡ERROR! Contraseña incorrecta.";
            }
        } else {
            registerLoginAttempt($email, false);
            $message = "¡ERROR! Usuario no encontrado.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Login Admin HQ</title>
<style>
/* Fondo estilo cómic */
body {
    background: linear-gradient(135deg, #ffeb3b, #e53935);
    font-family: 'Comic Sans MS', cursive;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
}

/* Contenedor del login */
form {
    background-color: #1a1a1a;
    padding: 40px 50px;
    border-radius: 15px;
    border: 5px solid #00bcd4;
    box-shadow: 10px 10px 0px #ffeb3b, -5px -5px 0px #e53935;
    text-align: center;
    width: 350px;
}

/* Título estilo cómic */
form h2 {
    color: #00bcd4;
    font-size: 2em;
    margin-bottom: 25px;
    text-shadow: 2px 2px #e53935;
}

/* Mensaje de error/alerta */
.message {
    color: #ffeb3b;
    font-weight: bold;
    margin-bottom: 20px;
    text-shadow: 1px 1px #000;
}

/* Inputs */
input {
    width: 100%;
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 10px;
    border: 3px solid #00bcd4;
    background-color: #0d0d0d;
    color: #fff;
    font-size: 1em;
    box-shadow: 3px 3px 0px #ffeb3b, -2px -2px 0px #e53935;
    transition: 0.2s;
}

input:focus {
    outline: none;
    border-color: #ffeb3b;
    box-shadow: 3px 3px 0px #ffeb3b, -2px -2px 0px #e53935, 0 0 10px #ffeb3b;
}

/* Botón estilo cómic */
button {
    width: 100%;
    padding: 12px;
    font-size: 1.1em;
    font-weight: bold;
    color: #fff;
    background-color: #e53935;
    border: 3px solid #ffeb3b;
    border-radius: 10px;
    cursor: pointer;
    text-shadow: 2px 2px #000;
    box-shadow: 5px 5px 0px #ffeb3b, -3px -3px 0px #000;
    transition: all 0.2s;
}

button:hover {
    background-color: #ffeb3b;
    color: #e53935;
    transform: translate(-3px, -3px);
    box-shadow: 8px 8px 0px #e53935, -4px -4px 0px #000;
}
</style>
</head>
<body>
<form method="POST">
    <h2>LOGIN HQ</h2>
    <?php if($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <button type="submit">¡INGRESAR!</button>
</form>
</body>
</html>
