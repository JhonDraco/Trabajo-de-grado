<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeGestionarFeriados());

include("db.php");

if (!isset($_GET['id'])) {
    header("Location: feriados.php");
    exit();
}

$id = intval($_GET['id']);

mysqli_query($conexion, "DELETE FROM feriados WHERE id_feriado = $id");
registrar_auditoria($conexion, 'ELIMINAR', 'Feriados', "Eliminó feriado ID $id");

header("Location: feriados.php?eliminado=1");
exit();
