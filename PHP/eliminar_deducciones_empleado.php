<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeDeducciones());
include("db.php");

$id = (int)$_GET['id'];
mysqli_query($conexion, "UPDATE deduccion_empleado SET activa = 0 WHERE id_deduccion_emp = $id");

header("Location: deducciones.php");
exit();
?>