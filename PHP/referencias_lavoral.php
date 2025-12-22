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
<title>Reportes</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<!-- CSS -->
<link rel="stylesheet" href="../css/reportes.css">

<!-- Iconos RemixIcon -->


</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Usuarios</a>
    <a href="reportes.php" class="active"><i class="ri-bar-chart-line"></i> Reportes</a>

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



    <h2><i class="ri-user-settings-line"></i> Referencia lavoral</h2>

    <form action="referencia_laboral_pdf.php" method="post" target="_blank">
        <label>Numero de Cedula</label>
        <input type="number" id="cedula" name="cedula" placeholder="Ingresar Numero de cedula" required>

        <label>Nombre </label>
        <input type="text" id="name" name="name" placeholder="Ingresar nombre y apellido" required>

        <label>Apellido</label>
        <input type="text" id="apellido" name="apellido" placeholder="Ingresa el usuario" required>

         <label>Email</label>
        <input type="text" id="email" name= "email" placeholder="Ingresar correo">

       
        <button type="submit"><i class="ri-save-3-line"></i> Generar pdf</button>

        <a href="administrador.php" class="cancel-btn"><i class="ri-arrow-left-line"></i> Cancelar</a>

    </form>

</div>
</div>


</body>
</html>
