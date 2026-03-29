<?php
session_start();

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID no recibido.";
    exit();
}

$id = $_GET['id'];

$consulta = "DELETE FROM nomina WHERE id_nomina = $id";

if (mysqli_query($conexion, $consulta)) {
    header("Location: ver_nomina.php");
    exit();
} else {
    echo "Error al eliminar: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
