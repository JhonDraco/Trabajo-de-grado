<?php
include("db.php");

if (!isset($_GET['id'])) {
    die("ID de nómina no especificado.");
}

$id_nomina = intval($_GET['id']);

// Obtener encabezado de la nómina
$sql_nomina = "SELECT * FROM nomina WHERE id_nomina = $id_nomina";
$res_nomina = mysqli_query($conexion, $sql_nomina);
$nomina = mysqli_fetch_assoc($res_nomina);

if (!$nomina) {
    die("Nómina no encontrada.");
}

// Obtener detalles por empleado
$sql_detalle = "
SELECT dn.*, e.nombre, e.apellido, e.cedula
FROM detalle_nomina dn
JOIN empleados e ON dn.empleado_id = e.id
WHERE dn.id_nomina = $id_nomina
";
$detalles = mysqli_query($conexion, $sql_detalle);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Detalle de Nómina</title>
<style>
table { border-collapse: collapse; width: 80%; margin: 20px auto; }
th, td { border: 1px solid #555; padding: 8px; text-align: center; }
h2 { text-align: center; }
</style>
</head>
<body>

<h2>📄 Detalle de Nómina #<?= $nomina['id_nomina'] ?></h2>
<p style="text-align:center;">
<strong>Periodo:</strong> <?= $nomina['fecha_inicio'] ?> → <?= $nomina['fecha_fin'] ?><br>
<strong>Tipo:</strong> <?= $nomina['tipo'] ?><br>
<strong>Creada por:</strong> <?= $nomina['creada_por'] ?>
</p>

<table>
<tr>
    <th>Cédula</th>
    <th>Empleado</th>
    <th>Salario Base</th>
    <th>Asignaciones</th>
    <th>Deducciones</th>
    <th>Total a Pagar</th>
    <th>Ver Detalle</th>
</tr>

<?php while ($d = mysqli_fetch_assoc($detalles)) { ?>
<tr>
    <td><?= $d['cedula'] ?></td>
    <td><?= $d['nombre'] . " " . $d['apellido'] ?></td>
    <td><?= number_format($d['salario_base'], 2) ?></td>
    <td><?= number_format($d['total_asignaciones'], 2) ?></td>
    <td><?= number_format($d['total_deducciones'], 2) ?></td>
    <td><strong><?= number_format($d['total_pagar'], 2) ?></strong></td>
    <td>
        <a href="ver_detalle_individual.php?id_detalle=<?= $d['id_detalle'] ?>">🔍 Ver</a>
    </td>
</tr>
<?php } ?>
</table>

</body>
</html>
