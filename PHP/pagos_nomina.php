<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// obtener nominas abiertas o cerradas (no pagadas)
$consulta = "SELECT * FROM nomina WHERE estado != 'pagada' ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagos de Nómina</title>
</head>
<body>

<h2>💵 Pagos de Nómina</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID Nómina</th>
    <th>Periodo</th>
    <th>Tipo</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php while ($n = mysqli_fetch_assoc($nominas)) { ?>
<tr>
    <td><?= $n['id_nomina'] ?></td>
    <td><?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>
    <td><?= ucfirst($n['tipo']) ?></td>
    <td><?= $n['estado'] ?></td>
    <td>
        <a href="pagar_nomina.php?id=<?= $n['id_nomina'] ?>">💰 Registrar Pago</a>
    </td>
</tr>
<?php } ?>

</table>

<br>
<a href="historial_pagos.php">📜 Ver Historial de Pagos</a>

</body>
</html>
