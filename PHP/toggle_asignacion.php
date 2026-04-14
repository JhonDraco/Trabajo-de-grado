<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAsignaciones());
include("db.php");

$id = (int)$_GET['id'];

$res = mysqli_query($conexion, "SELECT activo FROM tipo_asignacion WHERE id_asignacion = $id");
$fila = mysqli_fetch_assoc($res);
$nuevo = $fila['activo'] ? 0 : 1;

mysqli_query($conexion, "UPDATE tipo_asignacion SET activo = $nuevo WHERE id_asignacion = $id");

header("Location: asignaciones.php");
exit();
?>