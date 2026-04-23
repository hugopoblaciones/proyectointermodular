<?php
// ============================================================
//  db.php — Conexión a la base de datos MySQL
// ============================================================

require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Comprobar si la conexión ha fallado
if ($conn->connect_error) {
    die("Error de conexión con la base de datos: " . $conn->connect_error);
}

// Establecer charset UTF-8 para evitar problemas con tildes y caracteres especiales
$conn->set_charset("utf8mb4");