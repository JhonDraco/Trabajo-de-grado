


<?php
include "db.php"; // Conexión a la base de datos
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $salario_base = $_POST['salario_base'];

    $stmt = $conexion->prepare("INSERT INTO empleados 
        (cedula, nombre, apellido, direccion, telefono, email, fecha_ingreso, salario_base) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("❌ Error al preparar la consulta: " . $conexion->error);
    }
    $stmt->bind_param("sssssssd", $cedula, $nombre, $apellido, $direccion, $telefono, $email, $fecha_ingreso, $salario_base);

    if ($stmt->execute()) {
        $mensaje = "✅ Empleado registrado con éxito.";
    } else {
        $mensaje = "❌ Error al registrar el empleado: " . $stmt->error;
    }
    $stmt->close();
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
<link rel="stylesheet" href="../css/formulario_para_registrar_empleado.css">
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
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


    <form method="POST">
        <label for="cedula">Cédula:</label>
        <input type="text" name="cedula" id="cedula" required>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" required>

        <label for="direccion">Dirección:</label>
        <textarea name="direccion" id="direccion"></textarea>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono">

        <label for="email">Email:</label>
        <input type="email" name="email" id="email">

        <label for="fecha_ingreso">Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" required>    

        <label for="salario_base">Salario base:</label>
        <input type="number" step="0.01" name="salario_base" id="salario_base" required>

        <button type="submit">Enviar</button>
        <a href="listar_empleados.php">Volver</a>
    </form>
</div>

</div>
</body>
</html>


