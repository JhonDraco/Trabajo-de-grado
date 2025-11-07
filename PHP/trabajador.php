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

    <style>
        body {
            background: linear-gradient(to bottom, #4a90e2, #a8d0ff);
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh;
        }

        header {
            background: rgba(44, 62, 80, 0.9);
            padding: 15px;
            color: white;
            font-size: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .contenedor {
            background: rgba(255, 255, 255, 0.95);
            width: 80%;
            max-width: 700px;
            margin: 40px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 5px 15px rgba(0,0,0,0.3);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .info {
            font-size: 18px;
            margin: 10px 0;
        }

        .label {
            font-weight: bold;
            color: #2c3e50;
        }

        a {
            color: white;
            text-decoration: none;
            background: #c0392b;
            padding: 8px 12px;
            border-radius: 5px;
        }

    </style>
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
