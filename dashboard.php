<?php
// ============================================================
//  dashboard.php — Área privada (solo usuarios logueados)
// ============================================================

session_start();

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi cuenta — Automóviles de Barcelona</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .dashboard-container {
            max-width: 700px;
            margin: 80px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dashboard-container h2 { color: #2563eb; margin-bottom: 15px; }
        .dashboard-container p  { color: #555; margin-bottom: 25px; font-size: 18px; }
        .btn-logout {
            background-color: #dc2626;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin: 5px;
        }
        .btn-inicio {
            background-color: #2563eb;
            color: white;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin: 5px;
        }
        .btn-logout:hover { background-color: #b91c1c; }
        .btn-inicio:hover { background-color: #1e40af; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h2>👋 ¡Bienvenido, <?= htmlspecialchars($_SESSION['usuario_nombre']) ?>!</h2>
    <p>Has iniciado sesión correctamente como <strong><?= htmlspecialchars($_SESSION['usuario_email']) ?></strong></p>
    <p style="color:#888; font-size:14px;">Aquí podrías ver tus coches guardados, citas programadas, etc.</p>
    <a href="index.php" class="btn-inicio">🏠 Ir al inicio</a>
    <a href="logout.php" class="btn-logout">🚪 Cerrar sesión</a>
</div>

</body>
</html>