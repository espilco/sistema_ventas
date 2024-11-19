<?php
session_start();
if (!empty($_SESSION['active'])) {
    header('location: src/');
} else {
    if (!empty($_POST)) {
        $alert = '';
        if (empty($_POST['usuario']) || empty($_POST['clave'])) {
            $alert = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Ingrese usuario y contraseña
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>';
        } else {
            require_once "conexion.php";
            $user = $_POST['usuario'];
            $clave = md5($_POST['clave']);
            
            // Consultar usando PDO
            try {
                $stmt = $conexion->prepare("SELECT * FROM usuario WHERE usuario = :usuario AND clave = :clave");
                $stmt->bindParam(':usuario', $user, PDO::PARAM_STR);
                $stmt->bindParam(':clave', $clave, PDO::PARAM_STR);
                $stmt->execute();
                
                // Verificar si existe el usuario
                if ($stmt->rowCount() > 0) {
                    $dato = $stmt->fetch(PDO::FETCH_ASSOC);
                    $_SESSION['active'] = true;
                    $_SESSION['idUser'] = $dato['idusuario'];
                    $_SESSION['nombre'] = $dato['nombre'];
                    $_SESSION['user'] = $dato['usuario'];
                    header('Location: src/');
                } else {
                    $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Contraseña incorrecta
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
                    session_destroy();
                }
            } catch (PDOException $e) {
                $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error en la conexión: ' . $e->getMessage() . '
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
	<link rel="stylesheet" href="assets/css/material-dashboard.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <div style="display: flex;justify-content: space-between;">
        <div class="container" style="display: none;">
            <center><b class="title label mb-2">Login</b></center>
            <form action="" id="login-form" method="POST">
                <div class="user-details">
                    <div class="input-box">
                        <input type="text" class="input-field" name="usuario" id="usuario" placeholder="Usuario" autocomplete="off" required>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" name="clave" id="clave" placeholder="Contraseña" autocomplete="off" required>
                    </div>
					<?php echo (isset($alert)) ? $alert : '' ; ?>
                    <div class="button">
                        <input type="submit" value="Login">
                    </div>
                </div>
                <center>
                    <input type="checkbox" id="toggle" onclick="changeMode();">
                </center>
            </form>
        </div>

    </div>
	<script src="assets/js/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
	<script src="assets/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <script>
        var rootProp = document.documentElement.style;
        var mode = true;

        function changeMode() {
            if (mode) {
                darkMode();
            } else {
                lightMode();
            }
            mode = !mode;
        }

        function lightMode() {
            rootProp.setProperty("--background1", "rgba(230, 230, 230)");
            rootProp.setProperty("--shadow1", "rgba(119, 119, 119, 0.5)");
            rootProp.setProperty("--shadow2", "rgba(255, 255, 255, 0.85)");
            rootProp.setProperty("--labelColor", "black");
        }

        function darkMode() {
            rootProp.setProperty("--background1", "rgb(9 25 33)");
            rootProp.setProperty("--shadow1", "rgb(0 0 0 / 45%)");
            rootProp.setProperty("--shadow2", "rgb(255 255 255 / 12%)");
            rootProp.setProperty("--labelColor", "rgb(255 255 255 / 59%)");
        }
    </script>
</body>

</html>
