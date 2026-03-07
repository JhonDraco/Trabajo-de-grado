<?php

include("db.php");

$empleado = $_POST['empleado_id'];
$asignacion = $_POST['asignacion_id'];
$monto = $_POST['monto'];

mysqli_query($conexion,"

INSERT INTO asignacion_empleado
(empleado_id, id_asignacion, monto, activa)

VALUES

($empleado,$asignacion,$monto,1)

");

header("Location: asignaciones.php");

?>