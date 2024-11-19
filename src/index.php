<?php
require "../conexion.php";

// Consultas con PDO
try {
    // Preparar y ejecutar la consulta para obtener el total de usuarios
    $stmt = $conexion->prepare("SELECT * FROM usuario");
    $stmt->execute();
    $total['usuarios'] = $stmt->rowCount();
    
    // Preparar y ejecutar la consulta para obtener el total de clientes
    $stmt = $conexion->prepare("SELECT * FROM cliente");
    $stmt->execute();
    $total['clientes'] = $stmt->rowCount();
    
    // Preparar y ejecutar la consulta para obtener el total de productos
    $stmt = $conexion->prepare("SELECT * FROM producto");
    $stmt->execute();
    $total['productos'] = $stmt->rowCount();
    
    // Preparar y ejecutar la consulta para obtener el total de ventas con fecha superior a la actual
    $stmt = $conexion->prepare("SELECT * FROM ventas WHERE fecha > CURDATE()");
    $stmt->execute();
    $total['ventas'] = $stmt->rowCount();
    
} catch (PDOException $e) {
    echo "Error en la consulta: " . $e->getMessage();
    exit();
}

session_start();
include_once "includes/header.php";
?>

<!-- Content Row -->
<div class="row">
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
            <div class="card-header card-header-warning card-header-icon">
                <div class="card-icon">
                    <i class="fas fa-user fa-2x"></i>
                </div>
                <a href="usuarios.php" class="card-category text-warning font-weight-bold">
                    Usuarios
                </a>
                <h3 class="card-title"><?php echo $total['usuarios']; ?></h3>
            </div>
            <div class="card-footer bg-warning text-white">
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
            <div class="card-header card-header-success card-header-icon">
                <div class="card-icon">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <a href="clientes.php" class="card-category text-success font-weight-bold">
                    Clientes
                </a>
                <h3 class="card-title"><?php echo $total['clientes']; ?></h3>
            </div>
            <div class="card-footer bg-secondary text-white">
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
            <div class="card-header card-header-danger card-header-icon">
                <div class="card-icon">
                    <i class="fab fa-product-hunt fa-2x"></i>
                </div>
                <a href="productos.php" class="card-category text-danger font-weight-bold">
                    Productos
                </a>
                <h3 class="card-title"><?php echo $total['productos']; ?></h3>
            </div>
            <div class="card-footer bg-primary">
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
            <div class="card-header card-header-info card-header-icon">
                <div class="card-icon">
                    <i class="fas fa-cash-register fa-2x"></i>
                </div>
                <a href="ventas.php" class="card-category text-info font-weight-bold">
                    Ventas
                </a>
                <h3 class="card-title"><?php echo $total['ventas']; ?></h3>
            </div>
            <div class="card-footer bg-danger text-white">
            </div>
        </div>
    </div>
    <div class="col-lg-6">
		<div class="card">
            <div class="card-header card-header-primary">
                <h3 class="title-2 m-b-40">Productos con stock mínimo</h3>
            </div>
            <div class="card-body">
			<canvas id="stockMinimo"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
	<div class="card">
            <div class="card-header card-header-primary">
                <h3 class="title-2 m-b-40">Productos más vendidos</h3>
            </div>
            <div class="card-body">
			<canvas id="ProductosVendidos"></canvas>
            </div>
        </div>
    </div>
</div>

<?php include_once "includes/footer.php"; ?>
