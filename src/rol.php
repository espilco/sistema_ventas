<?php
session_start();
require_once "../conexion.php";
$id = $_GET['id'];

// Usar PDO para obtener los permisos
$sqlpermisos = $conexion->query("SELECT * FROM permisos");
$usuarios = $conexion->prepare("SELECT * FROM usuario WHERE idusuario = :id");
$usuarios->bindParam(':id', $id, PDO::PARAM_INT);
$usuarios->execute();
$consulta = $conexion->prepare("SELECT * FROM detalle_permisos WHERE id_usuario = :id");
$consulta->bindParam(':id', $id, PDO::PARAM_INT);
$consulta->execute();

$resultUsuario = $usuarios->rowCount();

if (empty($resultUsuario)) {
    header("Location: usuarios.php");
}

$datos = array();
foreach ($consulta as $asignado) {
    $datos[$asignado['id_permiso']] = true;
}

if (isset($_POST['permisos'])) {
    $id_user = $_GET['id'];
    $permisos = $_POST['permisos'];

    // Eliminar permisos existentes
    $delete = $conexion->prepare("DELETE FROM detalle_permisos WHERE id_usuario = :id_user");
    $delete->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $delete->execute();

    if ($permisos != "") {
        // Insertar los nuevos permisos
        foreach ($permisos as $permiso) {
            $insert = $conexion->prepare("INSERT INTO detalle_permisos(id_usuario, id_permiso) VALUES (:id_user, :permiso)");
            $insert->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $insert->bindParam(':permiso', $permiso, PDO::PARAM_INT);
            $insert->execute();
        }
        $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Permisos Asignados
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
    }
}

include_once "includes/header.php";
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card shadow-lg">
            <div class="card-header card-header-primary">
                Permisos
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <?php echo (isset($alert)) ? $alert : ''; ?>
                    <?php while ($row = $sqlpermisos->fetch(PDO::FETCH_ASSOC)) { ?>
                        <div class="form-check form-check-inline m-4">
                            <label for="permisos" class="p-2 text-uppercase"><?php echo $row['nombre']; ?></label>
                            <input id="permisos" type="checkbox" name="permisos[]" value="<?php echo $row['id']; ?>" <?php
                                                                                                                        if (isset($datos[$row['id']])) {
                                                                                                                            echo "checked";
                                                                                                                        }
                                                                                                                        ?>>
                        </div>
                    <?php } ?>
                    <br>
                    <button class="btn btn-primary btn-block" type="submit">Modificar</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
