<?php
session_start();

// Verifica que haya sesión y que sea administrador
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrador</title>
</head>
<body>
    <h1>Bienvenido Administrador <?php echo $_SESSION['usuario']; ?></h1>
    <a href="cerrar_sesion.php">Cerrar sesión</a>
</body>
</html>
