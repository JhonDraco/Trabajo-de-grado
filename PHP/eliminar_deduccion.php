<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeDeducciones());
include("db.php");

$id = (int)$_GET['id'];
mysqli_query($conexion, "UPDATE tipo_deduccion SET activo = 0 WHERE id_tipo = $id");

header("Location: deducciones.php");
exit();
?>