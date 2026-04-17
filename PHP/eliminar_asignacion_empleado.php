<?php

include("db.php");
include("seguridad.php");
verificarSesion();

$id = $_GET['id'];

mysqli_query($conexion,"

UPDATE asignacion_empleado
SET activa = 0
WHERE id_asig_emp = $id

");
registrar_auditoria($conexion, 'ELIMINAR', 'Asignaciones', "Desactivó asignación_empleado ID $id");
header("Location: asignaciones.php");

?>