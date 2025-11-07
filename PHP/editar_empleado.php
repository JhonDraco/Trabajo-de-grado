<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

// Validar ID recibido
if (!isset($_GET['id'])) {
    echo "ID de empleado no recibido.";
    exit();
}

$id = $_GET['id'];

// Obtener datos del empleado
$consulta = "
    SELECT * FROM empleados 
    WHERE id = $id
";
$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

// Obtener lista de cargos
$consulta_cargos = "SELECT * FROM cargo";
$cargos = mysqli_query($conexion, $consulta_cargos);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $cargo_id = $_POST['cargo_id'];
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
            cargo_id='$cargo_id',
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
    <title>Editar Empleado</title>
    <style>
        body {
            background: linear-gradient(to bottom, #4a90e2, #a8d0ff);
            font-family: Arial;
            padding: 20px;
            margin: 0;
        }
        .formulario {
            width: 80%;
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.3);
        }
        label {
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            margin: 8px 0 15px 0;
            border-radius: 5px;
            border: 1px solid #999;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 5px;
            cursor: pointer;
        }
        a {
            background: #c0392b;
            color: white;
            padding: 10px 18px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="formulario">
    <h2>Editar Empleado</h2>

    <form method="POST">

        <label>Cédula:</label>
        <input type="text" name="cedula" value="<?php echo $empleado['cedula']; ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $empleado['nombre']; ?>">

        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?php echo $empleado['apellido']; ?>">

        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?php echo $empleado['direccion']; ?>">

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo $empleado['telefono']; ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $empleado['email']; ?>">

        <label>Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" value="<?php echo $empleado['fecha_ingreso']; ?>">

        <label>Cargo:</label>
        <select name="cargo_id">
            <?php while ($cargo = mysqli_fetch_assoc($cargos)) { ?>
                <option value="<?php echo $cargo['cargo_id']; ?>"
                    <?php if ($cargo['cargo_id'] == $empleado['cargo_id']) echo 'selected'; ?>>
                    <?php echo $cargo['nombre_cargo']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Salario Base:</label>
        <input type="text" name="salario_base" value="<?php echo $empleado['salario_base']; ?>">

        <label>Estado:</label>
        <select name="estado">
            <option value="Activo" <?php if ($empleado['estado'] == 'Activo') echo 'selected'; ?>>Activo</option>
            <option value="Inactivo" <?php if ($empleado['estado'] == 'Inactivo') echo 'selected'; ?>>Inactivo</option>
        </select>

        <button type="submit">Guardar Cambios</button>
        <a href="listar_empleados.php">Cancelar</a>
    </form>
</div>

</body>
</html>

<?php
mysqli_close($conexion);
?>
