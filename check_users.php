<?php
$conn = new mysqli('localhost', 'root', '', 'auto');
if ($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$row = $result->fetch_assoc();
echo "Usuarios en la base de datos: " . $row['total'];
$conn->close();