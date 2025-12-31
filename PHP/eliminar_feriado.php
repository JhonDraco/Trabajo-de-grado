<?php
session_start();
include("db.php");

if (!isset($_GET['id'])) {
    header("Location: feriados.php");
    exit();
}

$id = intval($_GET['id']);

mysqli_query($conexion, "DELETE FROM feriados WHERE id_feriado = $id");

header("Location: feriados.php?eliminado=1");
exit();
