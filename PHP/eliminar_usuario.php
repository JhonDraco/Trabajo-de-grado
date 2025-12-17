<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID no recibido.";
    exit();
}

$id = $_GET['id'];

$consulta = "DELETE FROM usuarios WHERE id_usuario = $id";

if (mysqli_query($conexion, $consulta)) {
    header("Location: listar_usuario.php");
    exit();
} else {
    echo "Error al eliminar: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
