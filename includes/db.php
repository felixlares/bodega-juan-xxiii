<?php
$host = 'localhost';
$dbname = 'bodega_juan_xxiii';
$username = 'root'; // Cambiar si es necesario (XAMPP por defecto es root sin password)
$password = '123123'; // Cambiar si es necesario

try {
    // Primero conectamos sin especificar la base de datos para poder crearla si no existe
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);

    // Configurar el modo de errores
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>