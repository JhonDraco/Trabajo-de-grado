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
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="../css/administrador.css">

</head>
<body>

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<nav class="menu">
    <a href="administrador.php">➖Inicio</a>
    <a href="nomina.php">💵 Nomina de empleados</a>
    <a href="formulario_para_registrar_empleado.php">🧑‍💼 Registrar Empleado</a>
    <a href="listar_empleados.php">📋 Listar Empleados</a>
    <a href="usuarios.php">👥 Gestionar Usuarios</a>
    <a href="cargos.php">🧰 Gestionar Cargos</a>
</nav>

<div class="contenido">
    <h3>Bienvenido al Panel de Control</h3>
    <p>Desde aquí puedes gestionar los empleados, usuarios y cargos del sistema RRHH.</p>
</div>

</body>
</html>
