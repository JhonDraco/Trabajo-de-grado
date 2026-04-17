<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeDeducciones());
include("db.php");

$id = (int)$_GET['id'];

// Lee el estado actual y lo invierte
$res = mysqli_query($conexion, "SELECT activo FROM tipo_deduccion WHERE id_tipo = $id");
$fila = mysqli_fetch_assoc($res);
$nuevo = $fila['activo'] ? 0 : 1;

mysqli_query($conexion, "UPDATE tipo_deduccion SET activo = $nuevo WHERE id_tipo = $id");

registrar_auditoria($conexion, 'TOGGLE', 'Deducciones', "Toggle activo para deducción ID $id: $nuevo");
header("Location: deducciones.php");
exit();
?>