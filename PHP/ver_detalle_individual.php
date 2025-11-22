<?php
include("db.php");

if (!isset($_GET['id_detalle'])) {
    die("ID de detalle no especificado.");
}

$id_detalle = intval($_GET['id_detalle']);

// Asignaciones
$sql_asig = "
SELECT ta.nombre, ta.tipo, da.monto 
FROM detalle_asignacion da
JOIN tipo_asignacion ta ON da.id_asignacion = ta.id_asignacion
WHERE da.id_detalle = $id_detalle
";
$asignaciones = mysqli_query($conexion, $sql_asig);

// Deducciones
$sql_ded = "
SELECT td.nombre, td.porcentaje, dd.monto 
FROM detalle_deduccion dd
JOIN tipo_deduccion td ON dd.id_tipo = td.id_tipo
WHERE dd.id_detalle = $id_detalle
";
$deducciones = mysqli_query($conexion, $sql_ded);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle Individual</title>
<style>
table { border-collapse: collapse; width: 60%; margin: 20px auto; }
th, td { border: 1px solid #555; padding: 8px; text-align: center; }
h2 { text-align: center; }
</style>
</head>
<body>

<h2>🧾 Asignaciones Aplicadas</h2>
<table>
<tr>
    <th>Nombre</th>
    <th>Tipo</th>
    <th>Monto</th>
</tr>

<?php while ($a = mysqli_fetch_assoc($asignaciones)) { ?>
<tr>
    <td><?= $a['nombre'] ?></td>
    <td><?= $a['tipo'] ?></td>
    <td><?= number_format($a['monto'], 2) ?></td>
</tr>
<?php } ?>
</table>


<h2>💸 Deducciones Aplicadas</h2>
<table>
<tr>
    <th>Nombre</th>
    <th>%</th>
    <th>Monto</th>
</tr>

<?php while ($d = mysqli_fetch_assoc($deducciones)) { ?>
<tr>
    <td><?= $d['nombre'] ?></td>
    <td><?= $d['porcentaje'] ?>%</td>
    <td><?= number_format($d['monto'], 2) ?></td>
</tr>
<?php } ?>
</table>

</body>
</html>
