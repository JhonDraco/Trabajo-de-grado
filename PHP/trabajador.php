<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 2) {
    header("Location: index.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
 <link rel="stylesheet" href="../css/administrador.css">
    <body>

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<nav class="menu">
    <a href="trabajador.php">➖Inicio</a>
    <a href="nomina.php">💵 Nomina de empleados</a>
    <a href="formulario_para_registrar_empleado.php">🧑‍💼 Registrar Empleado</a>
    <a href="listar_empleados.php">📋 Listar Empleados</a>
    <a href="">💸 Deducciones</a>
    <a href="">✔ Asignaciones</a>
    <a href="">📰 Reportes</a>
</nav>


<div class="contenido">
    <h3>Bienvenido al Panel de Control</h3>
    <p>Desde aquí puedes gestionar los empleados, usuarios y cargos del sistema RRHH.</p>
</div>
</body>
</html>