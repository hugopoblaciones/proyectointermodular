<?php
// ============================================================
//  config.php — Constantes globales de seguridad
//  IMPORTANTE: Este archivo NO debe subirse a GitHub público.
//  Añádelo al .gitignore si el repositorio es público.
// ============================================================

// PIMIENTA (pepper): valor secreto fijo que se añade a TODAS
// las contraseñas antes de hacer el hash. No se guarda en la BD.
// Si cambias este valor, todos los usuarios deberán resetear su contraseña.
define('PEPPER', 'A9#kL2$mZ7!qXpR4@vTn');

// Datos de conexión a la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Usuario por defecto en XAMPP
define('DB_PASS', '');           // Contraseña vacía por defecto en XAMPP
define('DB_NAME', 'auto');