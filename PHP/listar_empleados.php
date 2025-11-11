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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
  <link rel="stylesheet" href="../css/listar_empleados.css"> 
</head>
<body>

<h2>📋 Lista de Empleados Registrados</h2>

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

</body>
</html>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
