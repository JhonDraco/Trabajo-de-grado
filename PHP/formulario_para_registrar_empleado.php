<?php
include "db.php"; 
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Empleado</title>

<!-- ICONOS REMIX ICON (correcto) -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

<!-- TU CSS CON EL DISEÑO VERDE -->
<link rel="stylesheet" href="../css/formulario_para_registrar_empleado.css">



    


</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
     <a href="listar_empleados.php"  class="active">
        <i class="ri-team-line"></i> Empleados
    </a>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email
    </a>
    
   
</aside>

<!-- MAIN -->
<div class="main">

    <header>
        <h2><i class="ri-user-add-line"></i> Registrar Empleado</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="listar_empleados.php" class="top-button"><i class="ri-file-list-2-line"></i> Lista de empleados</a>
        <a href="formulario_para_registrar_empleado.php" class="top-button"><i class="ri-user-add-line"></i> Registrar empleado</a>
    </div>

    <div class="form-card">
        <h2><i class="ri-id-card-line"></i> Datos del Empleado</h2>

        <?php if ($mensaje) { echo "<p style='text-align:center; color:green; font-weight:bold;'>$mensaje</p>"; } ?>

        <form method="POST">

            <label>Cédula</label>
            <input type="text" name="cedula" required>

            <label>Nombre</label>
            <input type="text" name="nombre" required>

            <label>Apellido</label>
            <input type="text" name="apellido" required>

            <label>Dirección</label>
            <textarea name="direccion"></textarea>

            <label>Teléfono</label>
            <input type="text" name="telefono">

            <label>Email</label>
            <input type="email" name="email">

            <label>Fecha de ingreso</label>
            <input type="date" name="fecha_ingreso" required>

            <label>Salario base</label>
            <input type="number" step="0.01" name="salario_base" required>

            <button type="submit"><i class="ri-send-plane-2-line"></i> Registrar</button>

            <a class="volver-btn" href="listar_empleados.php"><i class="ri-arrow-left-line"></i> Volver</a>

        </form>
    </div>

</div>

</body>
</html>
