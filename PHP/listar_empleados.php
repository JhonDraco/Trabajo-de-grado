<?php
include("db.php"); // incluye la conexión

// Consulta SQL
$consulta = "SELECT id, cedula, nombre, apellido, email, telefono FROM empleados";

// Ejecutar consulta
$resultado = mysqli_query($conexion, $consulta);

// Verificar errores
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
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/listar_empleados.css">
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="listar_usuario.php">Usuarios</a>
    <a href="reportes.php">Reportes</a>
</aside>

<div class="main">

    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
    <a href="listar_empleados.php" class="top-button">lista de empleados</a>    
    <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
    </div>

    <h2>Lista de Empleados Registrados</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Teléfono</th>
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
                <td class="acciones">
                    <a class="btn editar" href="editar_empleado.php?id=<?php echo $fila['id']; ?>">Editar</a>
                    <a class="btn eliminar" href="eliminar_empleado.php?id=<?php echo $fila['id']; ?>" onclick="return confirm('¿Eliminar empleado?');">Eliminar</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
