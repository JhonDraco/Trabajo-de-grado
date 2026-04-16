<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAsignaciones());
include("db.php");

$id = (int)$_GET['id'];
mysqli_query($conexion, "UPDATE tipo_asignacion SET activo = 0 WHERE id_asignacion = $id");

header("Location: asignaciones.php");
exit();
?>