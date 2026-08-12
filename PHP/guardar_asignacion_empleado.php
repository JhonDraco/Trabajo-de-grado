<?php

include("db.php");
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeCrearAsignacion());

$empleado = intval($_POST['empleado_id']);
$asignacion = intval($_POST['asignacion_id']);
$monto = floatval($_POST['monto']);

mysqli_query($conexion,"

INSERT INTO asignacion_empleado
(empleado_id, id_asignacion, monto, activa)

VALUES

($empleado,$asignacion,$monto,1)

");
registrar_auditoria($conexion, 'CREAR', 'Asignaciones', "Asignó asignación ID $asignacion al empleado ID $empleado");

header("Location: asignaciones.php");
exit();
?>
