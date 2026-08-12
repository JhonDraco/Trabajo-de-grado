<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeEliminarNomina());

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID no recibido.";
    exit();
}

$id = intval($_GET['id']);

$consulta = "DELETE FROM nomina WHERE id_nomina = $id";

if (mysqli_query($conexion, $consulta)) {
    registrar_auditoria($conexion, 'ELIMINAR', 'Nómina', "Eliminó nómina ID $id");
    header("Location: ver_nomina.php");
    exit();
} else {
    echo "Error al eliminar: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
