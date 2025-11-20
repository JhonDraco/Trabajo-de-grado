<?php
include("db.php");
?>
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
<link rel="stylesheet" href="../css/nomina.css">
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina </a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
    <a href="reportes.php">Reportes</a>
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
        <a href="crear_asignacion.php"class="top-button"> Crear Asignación</a>
        <a href="crear_deduccion.php" class="top-button"> Crear Deducción</a>
        <a href="generar_nomina.php"class="top-button"> Generar Nómina</a>
        <a href="ver_nomina.php" class="top-button"> Ver Nóminas</a>
    </div>
    <!-- CONTENIDO -->

</div>

</body>
</html>
