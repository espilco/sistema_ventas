<?php
session_start();
require("../conexion.php");
$id_user = $_SESSION['idUser'];
$permiso = "usuarios";

// Usar PDO para la consulta de permisos
$sql = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
$sql->bindParam(':id_user', $id_user, PDO::PARAM_INT);
$sql->bindParam(':permiso', $permiso, PDO::PARAM_STR);
$sql->execute();
$existe = $sql->fetchAll();

if (empty($existe) && $id_user != 1) {
    header("Location: permisos.php");
}

if (!empty($_GET['id'])) {
    $id = $_GET['id'];

    // Usar PDO para eliminar el usuario
    $query_delete = $conexion->prepare("DELETE FROM usuario WHERE idusuario = :id");
    $query_delete->bindParam(':id', $id, PDO::PARAM_INT);
    $query_delete->execute();

    header("Location: usuarios.php");
}
?>
