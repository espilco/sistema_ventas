<?php
    $host = "http://sistemaposventa-server.mysql.database.azure.com";
    $user = "yrsfqizgok";
    $clave = "h$i0cV9XYhAbcRNw";
    $bd = "db_sistema_ventas_upn";
    $port=3306;

    try {
        $conexion = new PDO("mysql:host=$host;dbname=$bd;charset=utf8", $user, $clave);
        // Establecer el modo de error de PDO para manejar excepciones
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "No se pudo conectar a la base de datos: " . $e->getMessage() . " " . $host;
        exit();
    }
?>
