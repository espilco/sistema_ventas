<?php
require_once "../conexion.php";
session_start();

if (isset($_GET['q'])) {
    $datos = array();
    $nombre = $_GET['q'];

    // Usando PDO
    $query = $conexion->prepare("SELECT * FROM cliente WHERE nombre LIKE :nombre");
    $query->bindValue(':nombre', '%' . $nombre . '%');
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $data['id'] = $row['idcliente'];
        $data['label'] = $row['nombre'];
        $data['direccion'] = $row['direccion'];
        $data['telefono'] = $row['telefono'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
} else if (isset($_GET['pro'])) {
    $datos = array();
    $nombre = $_GET['pro'];

    // Usando PDO
    $query = $conexion->prepare("SELECT * FROM producto WHERE codigo LIKE :nombre OR descripcion LIKE :nombre");
    $query->bindValue(':nombre', '%' . $nombre . '%');
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $data['id'] = $row['codproducto'];
        $data['label'] = $row['codigo'] . ' - ' . $row['descripcion'];
        $data['value'] = $row['descripcion'];
        $data['precio'] = $row['precio'];
        $data['existencia'] = $row['existencia'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
} else if (isset($_GET['detalle'])) {
    $id = $_SESSION['idUser'];
    $datos = array();

    // Usando PDO
    $query = $conexion->prepare("SELECT d.*, p.codproducto, p.descripcion FROM detalle_temp d INNER JOIN producto p ON d.id_producto = p.codproducto WHERE d.id_usuario = :id_usuario");
    $query->bindValue(':id_usuario', $id);
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $data['id'] = $row['id'];
        $data['descripcion'] = $row['descripcion'];
        $data['cantidad'] = $row['cantidad'];
        $data['descuento'] = $row['descuento'];
        $data['precio_venta'] = $row['precio_venta'];
        $data['sub_total'] = $row['total'];
        array_push($datos, $data);
    }
    echo json_encode($datos);
    die();
} else if (isset($_GET['delete_detalle'])) {
    $id_detalle = $_GET['id'];

    // Usando PDO
    $query = $conexion->prepare("DELETE FROM detalle_temp WHERE id = :id_detalle");
    $query->bindValue(':id_detalle', $id_detalle);
    $query->execute();

    if ($query) {
        $msg = "ok";
    } else {
        $msg = "Error";
    }
    echo $msg;
    die();
} else if (isset($_GET['procesarVenta'])) {
    $id_cliente = $_GET['id'];
    $id_user = $_SESSION['idUser'];

    // Usando PDO
    $query = $conexion->prepare("SELECT total, SUM(total) AS total_pagar FROM detalle_temp WHERE id_usuario = :id_usuario");
    $query->bindValue(':id_usuario', $id_user);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);
    $total = $result['total_pagar'];

    $insertar = $conexion->prepare("INSERT INTO ventas(id_cliente, total, id_usuario) VALUES (:id_cliente, :total, :id_usuario)");
    $insertar->bindValue(':id_cliente', $id_cliente);
    $insertar->bindValue(':total', $total);
    $insertar->bindValue(':id_usuario', $id_user);
    $insertar->execute();

    if ($insertar) {
        $id_maximo = $conexion->prepare("SELECT MAX(id) AS total FROM ventas");
        $id_maximo->execute();
        $resultId = $id_maximo->fetch(PDO::FETCH_ASSOC);
        $ultimoId = $resultId['total'];

        $consultaDetalle = $conexion->prepare("SELECT * FROM detalle_temp WHERE id_usuario = :id_usuario");
        $consultaDetalle->bindValue(':id_usuario', $id_user);
        $consultaDetalle->execute();

        while ($row = $consultaDetalle->fetch(PDO::FETCH_ASSOC)) {
            $id_producto = $row['id_producto'];
            $cantidad = $row['cantidad'];
            $desc = $row['descuento'];
            $precio = $row['precio_venta'];
            $total = $row['total'];

            $insertarDet = $conexion->prepare("INSERT INTO detalle_venta (id_producto, id_venta, cantidad, precio, descuento, total) 
                                               VALUES (:id_producto, :id_venta, :cantidad, :precio, :descuento, :total)");
            $insertarDet->bindValue(':id_producto', $id_producto);
            $insertarDet->bindValue(':id_venta', $ultimoId);
            $insertarDet->bindValue(':cantidad', $cantidad);
            $insertarDet->bindValue(':precio', $precio);
            $insertarDet->bindValue(':descuento', $desc);
            $insertarDet->bindValue(':total', $total);
            $insertarDet->execute();

            // Actualizando stock
            $stockActual = $conexion->prepare("SELECT * FROM producto WHERE codproducto = :id_producto");
            $stockActual->bindValue(':id_producto', $id_producto);
            $stockActual->execute();
            $stockNuevo = $stockActual->fetch(PDO::FETCH_ASSOC);
            $stockTotal = $stockNuevo['existencia'] - $cantidad;

            $stock = $conexion->prepare("UPDATE producto SET existencia = :stockTotal WHERE codproducto = :id_producto");
            $stock->bindValue(':stockTotal', $stockTotal);
            $stock->bindValue(':id_producto', $id_producto);
            $stock->execute();
        }

        // Eliminar detalles temporales
        $eliminar = $conexion->prepare("DELETE FROM detalle_temp WHERE id_usuario = :id_usuario");
        $eliminar->bindValue(':id_usuario', $id_user);
        $eliminar->execute();

        $msg = array('id_cliente' => $id_cliente, 'id_venta' => $ultimoId);
    } else {
        $msg = array('mensaje' => 'error');
    }
    echo json_encode($msg);
    die();
} else if (isset($_GET['descuento'])) {
    $id = $_GET['id'];
    $desc = $_GET['desc'];

    // Usando PDO
    $consulta = $conexion->prepare("SELECT * FROM detalle_temp WHERE id = :id");
    $consulta->bindValue(':id', $id);
    $consulta->execute();
    $result = $consulta->fetch(PDO::FETCH_ASSOC);

    $total_desc = $desc + $result['descuento'];
    $total = $result['total'] - $desc;

    $insertar = $conexion->prepare("UPDATE detalle_temp SET descuento = :total_desc, total = :total WHERE id = :id");
    $insertar->bindValue(':total_desc', $total_desc);
    $insertar->bindValue(':total', $total);
    $insertar->bindValue(':id', $id);
    $insertar->execute();

    if ($insertar) {
        $msg = array('mensaje' => 'descontado');
    } else {
        $msg = array('mensaje' => 'error');
    }

    echo json_encode($msg);
    die();
} else if (isset($_GET['editarCliente'])) {
    $idcliente = $_GET['id'];

    // Usando PDO
    $sql = $conexion->prepare("SELECT * FROM cliente WHERE idcliente = :idcliente");
    $sql->bindValue(':idcliente', $idcliente);
    $sql->execute();
    $data = $sql->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarUsuario'])) {
    $idusuario = $_GET['id'];

    // Usando PDO
    $sql = $conexion->prepare("SELECT * FROM usuario WHERE idusuario = :idusuario");
    $sql->bindValue(':idusuario', $idusuario);
    $sql->execute();
    $data = $sql->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
} else if (isset($_GET['editarProducto'])) {
    $id = $_GET['id'];

    // Usando PDO
    $sql = $conexion->prepare("SELECT * FROM producto WHERE codproducto = :id");
    $sql->bindValue(':id', $id);
    $sql->execute();
    $data = $sql->fetch(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit;
}

if (isset($_POST['regDetalle'])) {
    $id = $_POST['id'];
    $cant = $_POST['cant'];
    $precio = $_POST['precio'];
    $id_user = $_SESSION['idUser'];
    $total = $precio * $cant;

    // Usando PDO
    $verificar = $conexion->prepare("SELECT * FROM detalle_temp WHERE id_producto = :id AND id_usuario = :id_usuario");
    $verificar->bindValue(':id', $id);
    $verificar->bindValue(':id_usuario', $id_user);
    $verificar->execute();
    $result = $verificar->rowCount();
    $datos = $verificar->fetch(PDO::FETCH_ASSOC);

    if ($result > 0) {
        $cantidad = $datos['cantidad'] + $cant;
        $total_precio = ($cantidad * $total);
        $query = $conexion->prepare("UPDATE detalle_temp SET cantidad = :cantidad, total = :total_precio WHERE id_producto = :id AND id_usuario = :id_usuario");
        $query->bindValue(':cantidad', $cantidad);
        $query->bindValue(':total_precio', $total_precio);
        $query->bindValue(':id', $id);
        $query->bindValue(':id_usuario', $id_user);
        $query->execute();

        if ($query) {
            $msg = "actualizado";
        } else {
            $msg = "Error al ingresar";
        }
    } else {
        $query = $conexion->prepare("INSERT INTO detalle_temp(id_usuario, id_producto, cantidad ,precio_venta, total) VALUES (:id_user, :id, :cant, :precio, :total)");
        $query->bindValue(':id_user', $id_user);
        $query->bindValue(':id', $id);
        $query->bindValue(':cant', $cant);
        $query->bindValue(':precio', $precio);
        $query->bindValue(':total', $total);
        $query->execute();

        if ($query) {
            $msg = "registrado";
        } else {
            $msg = "Error al ingresar";
        }
    }
    echo json_encode($msg);
    die();
} else if (isset($_POST['cambio'])) {
    if (empty($_POST['actual']) || empty($_POST['nueva'])) {
        $msg = 'Los campos estan vacios';
    } else {
        $id = $_SESSION['idUser'];
        $actual = md5($_POST['actual']);
        $nueva = md5($_POST['nueva']);

        // Usando PDO
        $consulta = $conexion->prepare("SELECT * FROM usuario WHERE clave = :clave AND idusuario = :idusuario");
        $consulta->bindValue(':clave', $actual);
        $consulta->bindValue(':idusuario', $id);
        $consulta->execute();
        $result = $consulta->rowCount();

        if ($result == 1) {
            $query = $conexion->prepare("UPDATE usuario SET clave = :nueva WHERE idusuario = :idusuario");
            $query->bindValue(':nueva', $nueva);
            $query->bindValue(':idusuario', $id);
            $query->execute();

            if ($query) {
                $msg = 'ok';
            } else {
                $msg = 'error';
            }
        } else {
            $msg = 'dif';
        }
    }
    echo $msg;
    die();
}
?>
