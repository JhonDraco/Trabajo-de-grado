<?php
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
<link rel="stylesheet" href="../css/administrador.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php" class="active">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
    <a href="feriados.php">
        <i class="ri-bar-chart-line"></i> Feriados
    </a>
    <a href="vacaciones.php">  <i class="ri-bar-chart-line"></i> Vacaciones</a>
</aside>


<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU HORIZONTAL -->
    <div class="top-menu">
        <a href="" class="top-button">Función de nuestro sistema</a>
        <a href="" class="top-button">Propósito</a>
        <a href="" class="top-button">Visión</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Bienvenido al Panel de Control</h3>

        <div class="cards">
            <div class="card">
                <h4>Empleados</h4>
                <p>Desde aquí puedes gestionar los empleados, usuarios y cargos del sistema RRHH.</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>
