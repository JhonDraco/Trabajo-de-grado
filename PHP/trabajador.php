<?php
session_start();

// Solo trabajadores pueden entrar
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 2) {
    header("Location: index.php");
    exit();
}

include("db.php");

// ID del empleado que viene desde la sesión
$empleado_id = $_SESSION['empleado_id'];

$consulta = "
    SELECT e.*, c.nombre_cargo
    FROM empleados e
    JOIN cargo c ON e.cargo_id = c.cargo_id
    WHERE e.id = $empleado_id
";

$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Trabajador</title>
    <link rel="stylesheet" href="../css/trabajador.css">
   
</head>
<body>

<header>
    <span>Bienvenido, <?php echo $_SESSION['usuario']; ?></span>
    <a href="cerrar_sesion.php">Cerrar sesión</a>
</header>

<div class="contenedor">
    <h2>Información del Empleado</h2>

    <p class="info"><span class="label">Nombre:</span> <?php echo $empleado['nombre']; ?></p>
    <p class="info"><span class="label">Apellido:</span> <?php echo $empleado['apellido']; ?></p>
    <p class="info"><span class="label">Cédula:</span> <?php echo $empleado['cedula']; ?></p>
    <p class="info"><span class="label">Cargo:</span> <?php echo $empleado['nombre_cargo']; ?></p>
    <p class="info"><span class="label">Teléfono:</span> <?php echo $empleado['telefono']; ?></p>
    <p class="info"><span class="label">Email:</span> <?php echo $empleado['email']; ?></p>
    <p class="info"><span class="label">Dirección:</span> <?php echo $empleado['direccion']; ?></p>
    <p class="info"><span class="label">Fecha de ingreso:</span> <?php echo $empleado['fecha_ingreso']; ?></p>

</div>

</body>
</html>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
