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
</head>

<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina </a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="listar_usuario.php">Usuarios</a>
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
        <a href="" class="top-button">Funcion de nuestro sistema</a>
        <a href="" class="top-button">Proposito</a>
        <a href="" class="top-button">Vision</a>
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
