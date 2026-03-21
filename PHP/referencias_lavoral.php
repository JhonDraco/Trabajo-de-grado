<?php
include("seguridad.php");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reportes</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<!-- CSS -->
<link rel="stylesheet" href="../css/reportes.css">

<!-- Iconos RemixIcon -->


</head>
<body>

<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <a href="reportes.php" class="active">
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
        <a href="Reportes.php" class="top-button"><i class="ri-file-text-line"></i> Cartas de trabajo</a>
        <a href="referencias_lavoral.php" class="top-button"><i class="ri-file-paper-2-line"></i> Referencia laboral</a>
    </div>
    
    <!-- FORMULARIO -->
<div class="form-card">



    <h2><i class="ri-user-settings-line"></i> Referencia laboral</h2>

    <form action="referencia_laboral_pdf.php" method="post" target="_blank">
        <label>Numero de Cedula</label>
        <input type="number" id="cedula" name="cedula" placeholder="Ingresar Numero de cedula" required>

        <label>Nombre </label>
        <input type="text" id="name" name="name" placeholder="Ingresar nombre y apellido" required>

        <label>Apellido</label>
        <input type="text" id="apellido" name="apellido" placeholder="Ingresa el usuario" required>

       
        <button type="submit"><i class="ri-save-3-line"></i> Generar pdf</button>

        <a href="administrador.php" class="cancel-btn"><i class="ri-arrow-left-line"></i> Cancelar</a>

    </form>

</div>
</div>


</body>
</html>
