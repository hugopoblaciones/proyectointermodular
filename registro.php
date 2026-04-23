<?php
// ============================================================
//  registro.php — Registro de nuevos usuarios
// ============================================================

require_once 'db.php';   // Incluye también config.php a través de db.php

$error   = '';
$success = '';

// Solo procesamos el formulario si se ha enviado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Recoger y sanear los datos del formulario ---
    $nombre   = trim($_POST['nombre']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    // --- 2. Validaciones básicas ---
    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Por favor, rellena todos los campos.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El formato del correo electrónico no es válido.';

    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';

    } else {

        // --- 3. Comprobar si el email ya está registrado ---
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Este correo electrónico ya está registrado.';
            $stmt->close();

        } else {
            $stmt->close();

            // --- 4. Generar la SAL (salt) única para este usuario ---
            // random_bytes(32) genera 32 bytes criptográficamente seguros
            // bin2hex los convierte a 64 caracteres hexadecimales
            $salt = bin2hex(random_bytes(32));

            // --- 5. Crear el hash con SAL + PIMIENTA ---
            // Concatenamos: PEPPER (secreto) + contraseña + SALT (único)
            // password_hash usa bcrypt por defecto (PASSWORD_DEFAULT)
            $password_hash = password_hash(PEPPER . $password . $salt, PASSWORD_DEFAULT);

            // --- 6. Insertar el usuario en la base de datos ---
            $stmt = $conn->prepare(
                "INSERT INTO usuarios (nombre, email, password_hash, salt) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $salt);

            if ($stmt->execute()) {
                $success = '¡Cuenta creada correctamente! Ya puedes iniciar sesión.';
            } else {
                $error = 'Error al registrar el usuario. Inténtalo de nuevo.';
            }
            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="registro.css">
    <style>
        .msg-error   { color: #dc2626; margin: 10px 0; font-size: 14px; }
        .msg-success { color: #16a34a; margin: 10px 0; font-size: 14px; }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registro</h2>

    <?php if ($error):   ?><p class="msg-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p class="msg-success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <?php if (!$success): // Ocultar el formulario si el registro fue exitoso ?>
    <form method="POST" action="registro.php">
        <input type="text"     name="nombre"   placeholder="Nombre completo" required>
        <input type="email"    name="email"    placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña (mín. 6 caracteres)" required>
        <button type="submit">Registrarse</button>
    </form>
    <?php endif; ?>

    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
    <p><a href="index.php">← Volver al inicio</a></p>
</div>

</body>
</html>