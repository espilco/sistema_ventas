<?php
include("../conexion.php");

if ($_POST['action'] == 'sales') {
    $arreglo = array();

    // Usando PDO para la consulta de productos con existencia <= 10
    $query = $conexion->prepare("SELECT descripcion, existencia FROM producto WHERE existencia <= 10 ORDER BY existencia ASC LIMIT 10");
    $query->execute();

    while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
        $arreglo[] = $data;
    }

    echo json_encode($arreglo);
    die();
}

if ($_POST['action'] == 'polarChart') {
    $arreglo = array();

    // Usando PDO para la consulta de productos vendidos
    $query = $conexion->prepare("SELECT p.codproducto, p.descripcion, d.id_producto, d.cantidad, SUM(d.cantidad) as total 
                                 FROM producto p 
                                 INNER JOIN detalle_venta d 
                                 WHERE p.codproducto = d.id_producto 
                                 GROUP BY d.id_producto 
                                 ORDER BY d.cantidad DESC 
                                 LIMIT 5");
    $query->execute();

    while ($data = $query->fetch(PDO::FETCH_ASSOC)) {
        $arreglo[] = $data;
    }

    echo json_encode($arreglo);
    die();
}
?>
