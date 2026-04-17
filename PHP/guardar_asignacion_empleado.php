<?php

include("db.php");

include("seguridad.php");
verificarSesion();

$empleado = $_POST['empleado_id'];
$asignacion = $_POST['asignacion_id'];
$monto = $_POST['monto'];

mysqli_query($conexion,"



INSERT INTO asignacion_empleado
(empleado_id, id_asignacion, monto, activa)

VALUES

($empleado,$asignacion,$monto,1)

");
registrar_auditoria($conexion, 'CREAR', 'Asignaciones', "Asignó asignación ID $asignacion al empleado ID $empleado");

header("Location: asignaciones.php");

?>