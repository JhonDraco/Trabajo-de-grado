<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <link rel="stylesheet" href="../css/formulario_para_registrar_empleado.css">
</head>
<body>

<?php
include "db.php"; // Conexión a la base de datos
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $salario_base = $_POST['salario_base'];

    // Consulta preparada (8 columnas → 8 valores)
    $stmt = $conexion->prepare("INSERT INTO empleados 
        (cedula, nombre, apellido, direccion, telefono, email, fecha_ingreso, salario_base) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Verifica si la preparación fue exitosa
    if (!$stmt) {
        die("❌ Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular los parámetros
    // s = string, d = double (número decimal)
    $stmt->bind_param("sssssssd", 
        $cedula, 
        $nombre, 
        $apellido, 
        $direccion, 
        $telefono, 
        $email, 
        $fecha_ingreso, 
        $salario_base
    );

    // Ejecutar la consulta y verificar errores
    if ($stmt->execute()) {
        $mensaje = "✅ Empleado registrado con éxito.";
    } else {
        $mensaje = "❌ Error al registrar el empleado: " . $stmt->error;
    }

    // Cerrar el statement
    $stmt->close();
}
?>

<div class="formulario">
    <h2>Registrar Empleado</h2>

    <?php if (!empty($mensaje)) echo "<div class='mensaje'>$mensaje</div>"; ?>

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
        <a href="administrador.php">Volver</a>
    </form>
</div>

</body>
</html>
