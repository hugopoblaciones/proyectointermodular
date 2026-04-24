<?php
$conn = new mysqli('localhost', 'root', '', 'auto');
if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}

$sql = file_get_contents('C:/backup_proyecto/20262404_1227/db_20262404_1227.sql');
$queries = explode(';', $sql);

foreach($queries as $query) {
    $query = trim($query);
    if (!empty($query) && strpos($query, 'LOCK TABLES') === false && strpos($query, 'UNLOCK TABLES') === false) {
        $conn->query($query);
    }
}

echo "Restauracion completada\n";

$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$row = $result->fetch_assoc();
echo "Usuarios restaurados: " . $row['total'];
$conn->close();