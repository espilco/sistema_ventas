<?php
session_start();
require_once "../conexion.php";
$id_user = $_SESSION['idUser'];
$permiso = "ventas";

// Usar PDO para la consulta de permisos
$sql = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->bindParam(':permiso', $permiso, PDO::PARAM_STR);
$sql->execute();
$existe = $sql->fetchAll();

if (empty($existe) && $id_user != 1) {
    header('Location: permisos.php');
}

// Usar PDO para la consulta de ventas
$query = $conexion->prepare("SELECT v.*, c.idcliente, c.nombre FROM ventas v INNER JOIN cliente c ON v.id_cliente = c.idcliente");
$query->execute();

include_once "includes/header.php";
?>
<div class="card">
    <div class="card-header">
        Historial ventas
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-light" id="tbl">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $query->fetch(PDO::FETCH_ASSOC)) { ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['nombre']; ?></td>
                            <td><?php echo $row['total']; ?></td>
                            <td><?php echo $row['fecha']; ?></td>
                            <td>
                                <a href="pdf/generar.php?cl=<?php echo $row['id_cliente'] ?>&v=<?php echo $row['id'] ?>" target="_blank" class="btn btn-danger"><i class="fas fa-file-pdf"></i></a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>
