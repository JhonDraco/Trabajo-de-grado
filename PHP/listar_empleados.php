<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

$consulta = "SELECT e.*, c.nombre_cargo 
             FROM empleados e 
             JOIN cargo c 
             ON e.cargo_id = c.cargo_id";

$resultado = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Empleados</title>
    <style>
        body {
            background: linear-gradient(to bottom, #4a90e2, #a8d0ff);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #fff;
        }

        table {
            width: 90%;
            margin: auto;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.3);
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .btn {
            padding: 6px 10px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }

        .editar {
            background: #3498db;
        }

        .eliminar {
            background: #e74c3c;
        }
    </style>
</head>
<body>

<h2>📋 Lista de Empleados Registrados</h2>

<table>
    <tr>
        <th>Cédula</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Cargo</th>
        <th>Teléfono</th>
        <th>Email</th>
        <th>Acciones</th>
    </tr>

    <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
        <tr>
            <td><?php echo $fila['cedula']; ?></td>
            <td><?php echo $fila['nombre']; ?></td>
            <td><?php echo $fila['apellido']; ?></td>
            <td><?php echo $fila['nombre_cargo']; ?></td>
            <td><?php echo $fila['telefono']; ?></td>
            <td><?php echo $fila['email']; ?></td>
            <td>
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
