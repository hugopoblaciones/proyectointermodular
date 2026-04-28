<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

require_once 'db.php';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Este correo ya está registrado.';
        } else {
            $salt          = bin2hex(random_bytes(32));
            $password_hash = password_hash(PEPPER . $password . $salt, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password_hash, salt) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $salt);

            if ($stmt->execute()) {
                $success = '¡Cuenta creada! Ya puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar. Inténtalo de nuevo.';
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
    <title>Registro</title>
    <link rel="stylesheet" href="registro.css">
</head>
<body>
<div class="form-container">
    <h2>Registro</h2>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
    <?php if (!$success): ?>
    <form method="POST" action="registro.php">
        <div class="input-group">
            <svg class="icon-input" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <input type="text" name="nombre" placeholder="Nombre completo" required>
        </div>
        <div class="input-group">
            <svg class="icon-input" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
            <input type="email" name="email" placeholder="Correo electrónico" required>
        </div>
        <div class="input-group">
            <svg class="icon-input" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <input type="password" name="password" placeholder="Contraseña (mín. 6 caracteres)" required>
        </div>
        <button type="submit">Registrarse</button>
    </form>
    <?php endif; ?>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    <p><a href="index.php">← Volver al inicio</a></p>
</div>
</body>
</html>
