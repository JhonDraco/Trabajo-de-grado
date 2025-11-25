<?php
include("db.php");
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
<title>Panel del Administrador - Nómina</title>
<link rel="stylesheet" href="../css/nomina.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="usuarios.php"><i class="ri-user-settings-line"></i> Usuarios</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
        <a href="crear_asignacion.php" class="top-button">Crear Asignación</a>
        <a href="crear_deduccion.php" class="top-button">Crear Deducción</a>
        <a href="generar_nomina.php" class="top-button">Generar Nómina</a>
        <a href="ver_nomina.php" class="top-button">Ver Nóminas</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Bienvenido al Panel de Nómina</h3>
        <div class="cards">
            <div class="card">
                <h4>Gestión de Nómina</h4>
                <p>Desde aquí puedes crear asignaciones, deducciones y generar o ver nóminas.</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
