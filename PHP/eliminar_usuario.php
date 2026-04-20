<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeListarUsuarios());
include("db.php");

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header("Location: listar_usuario.php"); exit(); }

// Borrado lógico — nunca eliminar usuarios reales
mysqli_query($conexion, "UPDATE usuarios SET activo = 0 WHERE id_usuario = $id");
registrar_auditoria($conexion, 'DESACTIVAR', 'Usuarios', "Desactivó usuario ID $id");

header("Location: listar_usuario.php");
exit();
?>