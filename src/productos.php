<?php
session_start();
include "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "productos";

// Verificación de permisos usando PDO
$sql = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$sql->execute([':id_user' => $id_user, ':permiso' => $permiso]);
$existe = $sql->fetchAll();
if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
    exit;
}

// Manejo de formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alert = "";
    $id = $_POST['id'];
    $codigo = $_POST['codigo'];
    $producto = $_POST['producto'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    // Validación de campos
    if (empty($codigo) || empty($producto) || empty($precio) || $precio < 0 || empty($cantidad) || $cantidad < 0) {
        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todos los campos son obligatorios
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    } else {
        // Si es un nuevo producto
        if (empty($id)) {
            $query = $conexion->prepare("SELECT * FROM producto WHERE codigo = :codigo");
            $query->execute([':codigo' => $codigo]);
            $result = $query->fetch();
            if ($result) {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            El código ya existe
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            } else {
                $query_insert = $conexion->prepare("INSERT INTO producto(codigo, descripcion, precio, existencia) VALUES (:codigo, :producto, :precio, :cantidad)");
                $insert_result = $query_insert->execute([
                    ':codigo' => $codigo, 
                    ':producto' => $producto, 
                    ':precio' => $precio, 
                    ':cantidad' => $cantidad
                ]);
                if ($insert_result) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Producto registrado exitosamente
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                } else {
                    $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error al registrar el producto
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                }
            }
        } else {
            // Si es una actualización de producto
            $query_update = $conexion->prepare("UPDATE producto SET codigo = :codigo, descripcion = :producto, precio = :precio, existencia = :cantidad WHERE codproducto = :id");
            $update_result = $query_update->execute([
                ':codigo' => $codigo, 
                ':producto' => $producto, 
                ':precio' => $precio, 
                ':cantidad' => $cantidad,
                ':id' => $id
            ]);
            if ($update_result) {
                $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Producto modificado exitosamente
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            } else {
                $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                            Error al modificar el producto
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            }
        }
    }
}

include_once "includes/header.php";
?>

<div class="card shadow-lg">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form action="" method="post" autocomplete="off" id="formulario">
                    <?php echo isset($alert) ? $alert : ''; ?>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="codigo" class="text-dark font-weight-bold"><i class="fas fa-barcode"></i> Código de Barras</label>
                                <input type="text" placeholder="Ingrese código de barras" name="codigo" id="codigo" class="form-control">
                                <input type="hidden" id="id" name="id">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="producto" class="text-dark font-weight-bold">Producto</label>
                                <input type="text" placeholder="Ingrese nombre del producto" name="producto" id="producto" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="precio" class="text-dark font-weight-bold">Precio</label>
                                <input type="text" placeholder="Ingrese precio" class="form-control" name="precio" id="precio">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="cantidad" class="text-dark font-weight-bold">Cantidad</label>
                                <input type="number" placeholder="Ingrese cantidad" class="form-control" name="cantidad" id="cantidad">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
                            <input type="button" value="Nuevo" onclick="limpiar()" class="btn btn-success" id="btnNuevo">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="tbl">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Obtener productos con PDO
                        $query = $conexion->prepare("SELECT * FROM producto");
                        $query->execute();
                        $productos = $query->fetchAll();
                        foreach ($productos as $data) { ?>
                            <tr>
                                <td><?php echo $data['codproducto']; ?></td>
                                <td><?php echo $data['codigo']; ?></td>
                                <td><?php echo $data['descripcion']; ?></td>
                                <td><?php echo $data['precio']; ?></td>
                                <td><?php echo $data['existencia']; ?></td>
                                <td>
                                    <a href="#" onclick="editarProducto(<?php echo $data['codproducto']; ?>)" class="btn btn-primary"><i class='fas fa-edit'></i></a>
                                    <form action="eliminar_producto.php?id=<?php echo $data['codproducto']; ?>" method="post" class="confirmar d-inline">
                                        <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
