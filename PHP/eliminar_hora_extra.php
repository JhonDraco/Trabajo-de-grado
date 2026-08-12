<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeEliminarHorasExtra());
include("db.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: horas_extras.php"); exit(); }

mysqli_query($conexion, "DELETE FROM horas_extras WHERE id_hora_extra = $id");
registrar_auditoria($conexion, 'ELIMINAR', 'Horas Extra', "Eliminó registro de horas extra ID $id");

header("Location: horas_extras.php");
exit();
?>
