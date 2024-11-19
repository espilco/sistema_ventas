<?php
session_start();
$permiso = 'usuarios';
$id_user = $_SESSION['idUser'];
include "../conexion.php";

try {
    // Consultar permisos con PDO
    $stmt = $conexion->prepare("SELECT p.*, d.* FROM permisos p INNER JOIN detalle_permisos d ON p.id = d.id_permiso WHERE d.id_usuario = :id_user AND p.nombre = :permiso");
    $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
    $stmt->bindParam(':permiso', $permiso, PDO::PARAM_STR);
    $stmt->execute();
    $existe = $stmt->fetchAll();

    if (empty($existe) && $id_user != 1) {
        header('Location: permisos.php');
    }

    if (!empty($_POST)) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $email = $_POST['correo'];
        $user = $_POST['usuario'];
        $alert = "";
        
        if (empty($nombre) || empty($email) || empty($user)) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Todo los campos son obligatorio
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } else {
            if (empty($id)) {
                $clave = $_POST['clave'];
                if (empty($clave)) {
                    $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                La contraseña es requerido
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                } else {
                    $clave = md5($_POST['clave']);

                    // Verificar si el correo ya existe
                    $stmt = $conexion->prepare("SELECT * FROM usuario WHERE correo = :email");
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($result) {
                        $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    El correo ya existe
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>';
                    } else {
                        // Insertar nuevo usuario
                        $stmt = $conexion->prepare("INSERT INTO usuario (nombre, correo, usuario, clave) VALUES (:nombre, :correo, :usuario, :clave)");
                        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                        $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
                        $stmt->bindParam(':usuario', $user, PDO::PARAM_STR);
                        $stmt->bindParam(':clave', $clave, PDO::PARAM_STR);

                        if ($stmt->execute()) {
                            $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                        Usuario Registrado
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>';
                        } else {
                            $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        Error al registrar
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>';
                        }
                    }
                }
            } else {
                // Actualizar usuario
                $stmt = $conexion->prepare("UPDATE usuario SET nombre = :nombre, correo = :correo, usuario = :usuario WHERE idusuario = :id");
                $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $stmt->bindParam(':correo', $email, PDO::PARAM_STR);
                $stmt->bindParam(':usuario', $user, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Usuario Modificado
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                } else {
                    $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                Error al modificar
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>';
                }
            }
        }
    }

    // Consultar usuarios con PDO
    $stmt = $conexion->prepare("SELECT * FROM usuario");
    $stmt->execute();
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}

include "includes/header.php";
?>
<div class="card">
    <div class="card-body">
        <form action="" method="post" autocomplete="off" id="formulario">
            <?php echo isset($alert) ? $alert : ''; ?>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nombre">Nombre</label>
                        <input type="text" class="form-control" placeholder="Ingrese Nombre" name="nombre" id="nombre">
                        <input type="hidden" id="id" name="id">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="correo">Correo</label>
                        <input type="email" class="form-control" placeholder="Ingrese Correo Electrónico" name="correo" id="correo">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="usuario">Usuario</label>
                        <input type="text" class="form-control" placeholder="Ingrese Usuario" name="usuario" id="usuario">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="clave">Contraseña</label>
                        <input type="password" class="form-control" placeholder="Ingrese Contraseña" name="clave" id="clave">
                    </div>
                </div>
            </div>
            <input type="submit" value="Registrar" class="btn btn-primary" id="btnAccion">
            <input type="button" value="Nuevo" class="btn btn-success" id="btnNuevo" onclick="limpiar()">
        </form>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-hover table-striped table-bordered mt-2" id="tbl">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Usuario</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($usuarios)) {
                foreach ($usuarios as $data) { ?>
                    <tr>
                        <td><?php echo $data['idusuario']; ?></td>
                        <td><?php echo $data['nombre']; ?></td>
                        <td><?php echo $data['correo']; ?></td>
                        <td><?php echo $data['usuario']; ?></td>
                        <td>
                            <a href="rol.php?id=<?php echo $data['idusuario']; ?>" class="btn btn-warning"><i class='fas fa-key'></i></a>
                            <a href="#" onclick="editarUsuario(<?php echo $data['idusuario']; ?>)" class="btn btn-success"><i class='fas fa-edit'></i></a>
                            <form action="eliminar_usuario.php?id=<?php echo $data['idusuario']; ?>" method="post" class="confirmar d-inline">
                                <button class="btn btn-danger" type="submit"><i class='fas fa-trash-alt'></i> </button>
                            </form>
                        </td>
                    </tr>
            <?php }
            } ?>
        </tbody>
    </table>
</div>

<?php include_once "includes/footer.php"; ?>
