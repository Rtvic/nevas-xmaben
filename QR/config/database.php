<?php
// config/database.php

define('DB_HOST', 'localhost');
define('DB_NAME', 'qr_tracking_db');
define('DB_USER', 'root'); // Actualizar con las credenciales de Hostinger
define('DB_PASS', '');     // Actualizar con las credenciales de Hostinger

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
