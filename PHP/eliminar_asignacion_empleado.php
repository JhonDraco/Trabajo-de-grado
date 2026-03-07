<?php

include("db.php");

$id = $_GET['id'];

mysqli_query($conexion,"

UPDATE asignacion_empleado
SET activa = 0
WHERE id_asig_emp = $id

");

header("Location: asignaciones.php");

?>