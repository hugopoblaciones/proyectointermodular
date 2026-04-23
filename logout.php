<?php
// ============================================================
//  logout.php — Cerrar sesión del usuario
// ============================================================

session_start();

// Destruir todos los datos de sesión
$_SESSION = [];
session_destroy();

// Redirigir a la página principal
header("Location: index.php");
exit();