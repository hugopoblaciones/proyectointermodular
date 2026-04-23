<?php
// ============================================================
//  login.php — Inicio de sesión de usuarios
// ============================================================

session_start();   // Iniciar el sistema de sesiones de PHP

// Si el usuario ya está logueado, redirigirlo al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Recoger datos ---
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- 2. Validación básica ---
    if (empty($email) || empty($password)) {
        $error = 'Por favor, introduce tu correo y contraseña.';

    } else {

        // --- 3. Buscar el usuario por email ---
        $stmt = $conn->prepare(
            "SELECT id, nombre, email, password_hash, salt FROM usuarios WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // No dar pistas sobre si el email existe o no (seguridad)
            $error = 'Correo o contraseña incorrectos.';

        } else {
            $usuario = $result->fetch_assoc();

            // --- 4. Verificar la contraseña con PEPPER + password + SALT ---
            // Reconstruimos la misma cadena usada al registrar
            $candidato = PEPPER . $password . $usuario['salt'];

            if (password_verify($candidato, $usuario['password_hash'])) {

                // --- 5. Contraseña correcta: crear la sesión ---
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email']  = $usuario['email'];

                // Redirigir al dashboard
                header("Location: dashboard.php");
                exit();

            } else {
                $error = 'Correo o contraseña incorrectos.';
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .msg-error { color: #dc2626; margin: 10px 0; font-size: 14px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Iniciar sesión</h2>

    <?php if ($error): ?>
        <p class="msg-error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input type="email"    name="email"    placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Entrar</button>
    </form>

    <p>¿No tienes cuenta? <a href="registro.php">Regístrate</a></p>
    <p><a href="index.php">← Volver al inicio</a></p>
</div>

</body>
</html>