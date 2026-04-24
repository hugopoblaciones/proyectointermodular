<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'auto';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    $conn = new mysqli($host, $user, $pass);
    $conn->query("CREATE DATABASE IF NOT EXISTS $db");
    $conn->query("USE $db");
}

$sql = file_get_contents('C:/backup_proyecto/20262404_1227/db_20262404_1227.sql');
if ($conn->multi_query($sql)) {
    echo "Base de datos restaurada correctamente\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
$conn->close();