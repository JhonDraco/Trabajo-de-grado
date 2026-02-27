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
<link rel="stylesheet" href="../css/contactar.css">
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
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php"  class="active">
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
      
    </div>
<div class="contenido">

    <div class="card" style="max-width:480px;margin:auto;">
        <h3 style="text-align:center;">
            <i class="ri-mail-send-line"></i> Agendar Entrevista
        </h3>

        <!-- MENSAJE DE ÉXITO -->
        <?php if (isset($_GET['enviado'])) { ?>
            <div class="mensaje-exito">
                <i class="ri-checkbox-circle-line"></i>
                Correo enviado con éxito
            </div>
        <?php } ?>

        <form action="contactar_por_correos.php" method="post" class="form-contacto">

            <label>
                <i class="ri-user-line"></i> Nombre del destinatario
            </label>
            <input type="text" name="destinatario" required placeholder="Ej: Juan Pérez">

            <label>
                <i class="ri-user-star-line"></i> Nombre del emisor
            </label>
            <input type="text" name="emisor" required placeholder="Ej: RRHH Empresa">

            <label>
                <i class="ri-mail-line"></i> Correo electrónico
            </label>
            <input type="email" name="email" required placeholder="ejemplo@gmail.com">

            <button type="submit">
                <i class="ri-send-plane-2-line"></i> Enviar invitación
            </button>

        </form>
    </div>

</div>



</body>
</html>
