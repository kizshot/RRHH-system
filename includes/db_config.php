<?php
// includes/db_config.php
// Configuración de conexión a MySQL usando mysqli
// Asegúrate de crear la BD con el script database.sql

$DB_HOST = '127.0.0.1';
$DB_PORT = 3306;
$DB_USER = 'root';
$DB_PASS = '1234'; // Contraseña según especificación
$DB_NAME = 'hr365_db';

// Crear conexión (con puerto)
$GLOBALS['conn'] = mysqli_init();
$connected = mysqli_real_connect($GLOBALS['conn'], $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if (!$connected) {
    http_response_code(500);
    die('Error de conexión a la base de datos: ' . mysqli_connect_error());
}

// Configura el conjunto de caracteres
if (!mysqli_set_charset($GLOBALS['conn'], 'utf8mb4')) {
    http_response_code(500);
    die('Error configurando charset: ' . mysqli_error($GLOBALS['conn']));
}

// Iniciar sesión PHP si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

