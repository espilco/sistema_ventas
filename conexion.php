<?php
    $host = "localhost";
    $user = "jhon";
    $clave = "espilco";
    $bd = "db_sistema_ventas";
    $port=3306;

    try {
        $conexion = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $user, $clave);
        // Establecer el modo de error de PDO para manejar excepciones
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la base de datos: " . $e->getMessage();
        exit();
    }
?>
