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

<!-- CSS -->
<link rel="stylesheet" href="../css/nomina.css">

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
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php" class="active">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
   <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados    </a>
  
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

    <!-- HEADER -->
    <header>
        <h2><i class="ri-money-dollar-circle-line"></i> Panel de Nómina</h2>

        <div>
            <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
        <a href="asignaciones.php" class="top-button">
            <i class="ri-add-circle-line"></i> Asignaciónes
        </a>

        <a href="deducciones.php" class="top-button">
            <i class="ri-subtract-line"></i>  Deducciónes
        </a>

        <a href="generar_nomina.php" class="top-button">
            <i class="ri-calculator-line"></i> Generar Nómina
        </a>

        <a href="ver_nomina.php" class="top-button">
            <i class="ri-file-list-line"></i> Ver Nóminas
        </a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
       
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Bienvenido al Panel de Nómina</h3>

        <div class="cards">
            <div class="card">
                <h4><i class="ri-file-paper-line"></i> Gestión de Nómina</h4>
                <p>Desde aquí puedes crear asignaciones, deducciones y generar o consultar las nóminas procesadas.</p>
            </div>
        </div>

    </div>
</div>

</body>
</html>
