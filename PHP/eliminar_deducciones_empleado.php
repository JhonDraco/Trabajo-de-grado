<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeEliminarDeduccion());
include("db.php");

$id = (int)$_GET['id'];
mysqli_query($conexion, "UPDATE deduccion_empleado SET activa = 0 WHERE id_deduccion_emp = $id");

registrar_auditoria($conexion, 'ELIMINAR', 'Deducciones', "Desactivó deducción_empleado ID $id");

header("Location: deducciones.php");
exit();
?>
