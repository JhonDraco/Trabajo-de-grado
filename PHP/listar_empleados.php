<?php
include("db.php"); // incluye la conexión

// Consulta SQL
$consulta = "SELECT id, cedula, nombre, apellido, email, telefono, estado FROM empleados";
$resultado = mysqli_query($conexion, $consulta);
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
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
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php" class="active"><i class="ri-team-line"></i> Empleados</a>
    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Usuarios</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="contactar.php" >
      <i class="ri-mail-line"></i> Agendar entrevistas 
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
        <a href="listar_empleados.php" class="top-button">Lista de Empleados</a>
        <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Empleados Registrados</h3>

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
                        <a class="btn editar" href="editar_empleado.php?id=<?php echo $fila['id']; ?>">
                            <i class="ri-edit-2-line"></i> Editar
                        </a>
                        <a class="btn eliminar" href="eliminar_empleado.php?id=<?php echo $fila['id']; ?>" onclick="return confirm('¿Eliminar empleado?');">
                            <i class="ri-delete-bin-6-line"></i> Eliminar
                        </a>
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
