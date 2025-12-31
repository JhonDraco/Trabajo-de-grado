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

<style>

    
/* ===== SIDEBAR ===== */
.sidebar{
    width: var(--sidebar-width);
    background: linear-gradient(180deg, var(--green-dark), var(--green-mid));
    padding: 30px 20px;
    color:white;
    box-shadow: var(--shadow);
    display:flex;
    flex-direction:column;
    border-radius: 0 16px 16px 0;
}

.sidebar h2{
    text-align:center;
    margin-bottom:30px;
    font-size:22px;
}

.sidebar a{
    display:flex;
    align-items:center;
    gap:10px;
    padding:12px 15px;
    color:white;
    text-decoration:none;
    border-radius:10px;
    font-weight:500;
    transition:0.25s;
    position:relative;
}

.sidebar a:hover{
    background:rgba(255,255,255,0.15);
    transform:translateX(5px);
}

.sidebar a.active{
    background:rgba(0,0,0,0.35);
}

.sidebar a.active::before{
    content:"";
    position:absolute;
    left:-10px;
    top:50%;
    transform:translateY(-50%);
    width:6px;
    height:28px;
    background:white;
    border-radius:4px;
}
.form-card {
    width: 550px;
    background: var(--white-soft);
    margin: 30px auto;
    padding: 25px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}

.form-card h2 {
    text-align: center;
    color: var(--green-dark);
    margin-bottom: 18px;
}

form label {
    font-weight: 600;
    color: var(--green-mid);
}

form input, form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    margin-bottom: 15px;
    border-radius: 8px;
}

button {
    width: 100%;
    padding: 12px;
    background: var(--green-mid);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
}

button:hover {
    background: var(--green-hover);
}

.volver-btn {
    display: block;
    text-align: center;
    margin-top: 10px;
    color: var(--green-mid);
    font-weight: 600;
    text-decoration: none;
}
</style>

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
