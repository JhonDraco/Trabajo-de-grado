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

<!-- CSS -->
<link rel="stylesheet" href="../css/listar_usuarios.css">

<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php" class="active"><i class="ri-user-settings-line"></i> Usuarios</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a></a>
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
        <a href="usuarios.php" class="top-button"><i class="ri-user-add-line"></i> Registrar usuario</a>
        <a href="" class="top-button"><i class="ri-information-line"></i> Información de usuarios</a>
    </div>

</div>

</body>
</html>
