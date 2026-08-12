<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeEliminarEmpleado());

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID no recibido.";
    exit();
}

$id = intval($_GET['id']);

$consulta = "DELETE FROM empleados WHERE id = $id";

if (mysqli_query($conexion, $consulta)) {
    registrar_auditoria($conexion, 'ELIMINAR', 'Empleados', "Eliminó empleado ID $id");
    header("Location: listar_empleados.php");
    exit();
} else {
    echo "Error al eliminar: " . mysqli_error($conexion);
}

mysqli_close($conexion);
?>
