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
<!-- SIDEBAR -->
<aside class="sidebar">
    
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php" class="active">
        <i class="ri-home-4-line"></i> Inicio
    </a>

    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href="liquidacion.php">
        <i class="ri-ball-pen-line"></i> Liquidacion
    </a>

    <a href="vacaciones.php">
        <i class="ri-sun-line"></i> Vacaciones
    </a>
    
    <a href="listar_empleados.php">
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

