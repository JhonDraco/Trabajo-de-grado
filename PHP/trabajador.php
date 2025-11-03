<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

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
