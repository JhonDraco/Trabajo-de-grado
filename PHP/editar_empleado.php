<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID de empleado no recibido.";
    exit();
}

$id = $_GET['id'];

$consulta = "SELECT * FROM empleados WHERE id = $id";
$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $salario_base = $_POST['salario_base'];
    $estado = $_POST['estado'];

    $update = "
        UPDATE empleados SET
            cedula='$cedula',
            nombre='$nombre',
            apellido='$apellido',
            direccion='$direccion',
            telefono='$telefono',
            email='$email',
            fecha_ingreso='$fecha_ingreso',
            salario_base='$salario_base',
            estado='$estado'
        WHERE id=$id
    ";

    if (mysqli_query($conexion, $update)) {
        header("Location: listar_empleados.php");
        exit();
    } else {
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Empleado</title>
<link rel="stylesheet" href="../css/editar_empleado.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php" class="active"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Usuarios</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
</aside>

<div class="main">

<header>
    <h2>Editar Empleado</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="top-menu">
    <a href="listar_empleados.php" class="top-button">Lista de Empleados</a>
    <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
</div>

<div class="formulario">
    <form method="POST">

        <label><i class="ri-id-card-line"></i> Cédula:</label>
        <input type="text" name="cedula" value="<?php echo $empleado['cedula']; ?>">

        <label><i class="ri-user-line"></i> Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $empleado['nombre']; ?>">

        <label><i class="ri-user-2-line"></i> Apellido:</label>
        <input type="text" name="apellido" value="<?php echo $empleado['apellido']; ?>">

        <label><i class="ri-map-pin-line"></i> Dirección:</label>
        <input type="text" name="direccion" value="<?php echo $empleado['direccion']; ?>">

        <label><i class="ri-phone-line"></i> Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo $empleado['telefono']; ?>">

        <label><i class="ri-mail-line"></i> Email:</label>
        <input type="text" name="email" value="<?php echo $empleado['email']; ?>">

        <label><i class="ri-calendar-line"></i> Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" value="<?php echo $empleado['fecha_ingreso']; ?>">

        <label><i class="ri-money-dollar-circle-line"></i> Salario Base:</label>
        <input type="text" name="salario_base" value="<?php echo $empleado['salario_base']; ?>">

        <label><i class="ri-checkbox-circle-line"></i> Estado:</label>
        <select name="estado">
            <option value="Activo" <?php if ($empleado['estado'] == 'Activo') echo 'selected'; ?>>Activo</option>
            <option value="Inactivo" <?php if ($empleado['estado'] == 'Inactivo') echo 'selected'; ?>>Inactivo</option>
        </select>

        <div class="acciones">
            <button type="submit"><i class="ri-save-line"></i> Guardar Cambios</button>
            <a href="listar_empleados.php"><i class="ri-close-circle-line"></i> Cancelar</a>
        </div>

    </form>
</div>


</div>
</body>
</html>

<?php
mysqli_close($conexion);
?>
