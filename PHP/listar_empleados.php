<?php
include("db.php"); // incluye la conexión
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeListarEmpleados());

// Consulta SQL
$buscar = "";

if (isset($_GET['buscar']) && $_GET['buscar'] != "") {
    $buscar = mysqli_real_escape_string($conexion, $_GET['buscar']);

    $consulta = "SELECT id, cedula, nombre, apellido, email, telefono, estado 
                 FROM empleados
                 WHERE nombre LIKE '%$buscar%'
                 OR apellido LIKE '%$buscar%'
                 OR cedula LIKE '%$buscar%'";
} else {
    $consulta = "SELECT id, cedula, nombre, apellido, email, telefono, estado 
                 FROM empleados";
}

$resultado = mysqli_query($conexion, $consulta);
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lista de Empleados - Panel RRHH</title>

<!-- CSS del panel verde -->
<link rel="stylesheet" href="../css/listar_empleados.css">

<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <?php if (puedeGenerarNomina()): ?>
    <a href="generar_nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>
    <?php elseif (puedeVerNomina()): ?>
    <a href="ver_nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>
    <?php endif; ?>

    <?php if (puedeVerLiquidacion()): ?>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <?php endif; ?>

    <?php if (puedeVerVacaciones()): ?>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <?php endif; ?>
    <?php if (puedeVerHorasExtra()): ?>
    <a href="horas_extras.php"><i class="ri-time-line"></i> Horas Extra</a>
    <?php endif; ?>

    <a href="listar_empleados.php" class="active">
        <i class="ri-team-line"></i> Empleados
    </a>

    <?php if (puedeVerUsuarios()): ?>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <?php endif; ?>

    <?php if (puedeReportes()): ?>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
    <?php endif; ?>

    <?php if (puedeVerBitacora()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>

    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email
    </a>
</aside>

<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
        <?php if (puedeCrearEmpleado()): ?>
        <a href="formulario_para_registrar_empleado.php" class="top-button">
            <i class="ri-user-add-line"></i> Registrar Empleado
        </a>
        <?php endif; ?>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Empleados Registrados</h3>
     <form method="GET" class="buscador">
     <input
        type="text"
        name="buscar"
        placeholder="Buscar por nombre, apellido o cédula"
        value="<?php echo isset($_GET['buscar']) ? $_GET['buscar'] : ''; ?>">
      <button type="submit">
        <i class="ri-search-line"></i>
        Buscar
      </button>
    </form>


        <table>
            <tr>
                <th>ID</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Statu</th>
                <th>Acciones</th>
            </tr>

            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                <tr>
                    <td><?php echo $fila['id']; ?></td>
                    <td><?php echo $fila['cedula']; ?></td>
                    <td><?php echo $fila['nombre']; ?></td>
                    <td><?php echo $fila['apellido']; ?></td>
                    <td><?php echo $fila['email']; ?></td>
                    <td><?php echo $fila['telefono']; ?></td>
                    <td><?php echo $fila['estado']; ?></td>
                    <td class="acciones">
                        <a class="btn" href="salarios_archivos.php?id=<?php echo $fila['id']; ?>"
                        style="background:#1f3a34; color:white;">
                            <i class="ri-folder-user-line"></i> Datos
                        </a>

                        <?php if (puedeEditarEmpleado()): ?>
                        <a class="btn" href="editar_empleado.php?id=<?php echo $fila['id']; ?>"
                        style="background:#1a56db; color:white;">
                            <i class="ri-edit-line"></i> Editar
                        </a>
                        <?php endif; ?>

                        <?php if (puedeEliminarEmpleado()): ?>
                        <a class="btn eliminar" href="eliminar_empleado.php?id=<?php echo $fila['id']; ?>"
                        onclick="return confirm('¿Eliminar empleado?');">
                            <i class="ri-delete-bin-6-line"></i> Eliminar
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>

    </div>
</div>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>

</body>
</html>
