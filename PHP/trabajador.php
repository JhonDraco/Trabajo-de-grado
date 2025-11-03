<?php
session_start();

// Verifica que haya sesión y que sea trabajador
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 2) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Trabajador</title>
</head>
<body>
    <h1>Bienvenido Trabajador <?php echo $_SESSION['usuario']; ?></h1>
    <a href="cerrar_sesion.php">Cerrar sesión</a>
</body>
</html>
