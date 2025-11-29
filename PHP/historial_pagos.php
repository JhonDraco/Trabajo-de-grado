<?php
include("db.php");
$sql = "SELECT p.*, n.fecha_inicio, n.fecha_fin 
        FROM pagos p 
        JOIN nomina n ON p.id_nomina = n.id_nomina
        ORDER BY fecha_pago DESC";

$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Historial de Pagos</title>
</head>
<body>

<h2>📜 Historial de Pagos</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID Pago</th>
    <th>ID Nómina</th>
    <th>Periodo</th>
    <th>Fecha de Pago</th>
    <th>Total Pagado</th>
    <th>Método</th>
    <th>Notas</th>
</tr>

<?php while($p = mysqli_fetch_assoc($res)) { ?>
<tr>
    <td><?= $p['id_pago'] ?></td>
    <td><?= $p['id_nomina'] ?></td>
    <td><?= $p['fecha_inicio'] ?> / <?= $p['fecha_fin'] ?></td>
    <td><?= $p['fecha_pago'] ?></td>
    <td>Bs <?= number_format($p['total_pagado'],2) ?></td>
    <td><?= $p['metodo'] ?></td>
    <td><?= $p['notas'] ?></td>
</tr>
<?php } ?>

</table>

</body>
</html>
