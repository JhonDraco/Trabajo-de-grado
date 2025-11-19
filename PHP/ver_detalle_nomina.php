<?php
include("db.php");

// Validar ID
if(!isset($_GET['id'])){
    die("ID de nómina no especificado.");
}

$id_nomina = intval($_GET['id']);

// ===============================
// 1. OBTENER DATOS DE LA NÓMINA
// ===============================

$sql_nomina = "SELECT * FROM nomina WHERE id_nomina = $id_nomina LIMIT 1";
$res_nomina = mysqli_query($conexion, $sql_nomina);

if(mysqli_num_rows($res_nomina) == 0){
    die("Nómina no encontrada.");
}

$nomina = mysqli_fetch_assoc($res_nomina);

// ===============================
// 2. OBTENER DETALLES DE LA NÓMINA
// ===============================

$sql_detalles = "
SELECT dn.*, e.nombre, e.apellido 
FROM detalle_nomina dn
INNER JOIN empleados e ON e.id = dn.empleado_id
WHERE dn.nomina_id = $id_nomina
ORDER BY e.apellido ASC
";

$res_detalles = mysqli_query($conexion, $sql_detalles);

// ===============================
// 3. SUMAR ASIGNACIONES Y DEDUCCIONES POR EMPLEADO
// ===============================

$sql_asignaciones = "
SELECT da.*, a.nombre_asignacion 
FROM detalle_asignacion da
INNER JOIN asignaciones a ON a.id_asignacion = da.asignacion_id
WHERE da.nomina_id = $id_nomina
";

$res_asignaciones = mysqli_query($conexion, $sql_asignaciones);

$sql_deducciones = "
SELECT dd.*, d.nombre_deduccion 
FROM detalle_deduccion dd
INNER JOIN deducciones d ON d.id_deduccion = dd.deduccion_id
WHERE dd.nomina_id = $id_nomina
";

$res_deducciones = mysqli_query($conexion, $sql_deducciones);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Nómina</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #444; padding: 8px; text-align: left; }
        th { background: #ddd; }
        h2, h3 { margin-top: 20px; }
    </style>
</head>
<body>

<h2>📄 Detalle de Nómina #<?= $nomina['id_nomina'] ?></h2>

<p><strong>Fecha:</strong> <?= $nomina['fecha_inicio'] ?> → <?= $nomina['fecha_fin'] ?></p>
<p><strong>Tipo:</strong> <?= $nomina['tipo'] ?></p>
<p><strong>Estado:</strong> <?= $nomina['estado'] ?></p>
<p><strong>Creada por:</strong> <?= $nomina['creada_por'] ?></p>

<hr>

<h3>👨‍💼 Empleados incluidos en esta nómina</h3>

<table>
    <tr>
        <th>Empleado</th>
        <th>Sueldo Base</th>
        <th>Asignaciones</th>
        <th>Deducciones</th>
        <th>Total Neto</th>
    </tr>

<?php
$totales = [];

while ($detalle = mysqli_fetch_assoc($res_detalles)) {

    $id_empleado = $detalle['empleado_id'];

    // Obtener asignaciones del empleado
    $sqlA = "SELECT SUM(monto) AS totalA FROM detalle_asignacion 
            WHERE nomina_id = $id_nomina AND empleado_id = $id_empleado";
    $resA = mysqli_query($conexion, $sqlA);
    $asig = mysqli_fetch_assoc($resA)['totalA'] ?? 0;

    // Obtener deducciones del empleado
    $sqlD = "SELECT SUM(monto) AS totalD FROM detalle_deduccion 
            WHERE nomina_id = $id_nomina AND empleado_id = $id_empleado";
    $resD = mysqli_query($conexion, $sqlD);
    $dedu = mysqli_fetch_assoc($resD)['totalD'] ?? 0;

    $total_neto = ($detalle['pago_base'] + $asig) - $dedu;

    echo "
    <tr>
        <td>{$detalle['nombre']} {$detalle['apellido']}</td>
        <td>".number_format($detalle['pago_base'], 2)."</td>
        <td>".number_format($asig, 2)."</td>
        <td>".number_format($dedu, 2)."</td>
        <td><strong>".number_format($total_neto, 2)."</strong></td>
    </tr>
    ";
}
?>
</table>

<hr>

<h3>📌 Asignaciones aplicadas</h3>
<table>
<tr>
    <th>Empleado</th>
    <th>Asignación</th>
    <th>Monto</th>
</tr>

<?php while ($a = mysqli_fetch_assoc($res_asignaciones)) { ?>
<tr>
    <td><?= $a['empleado_id'] ?></td>
    <td><?= $a['nombre_asignacion'] ?></td>
    <td><?= number_format($a['monto'], 2) ?></td>
</tr>
<?php } ?>
</table>

<h3>📌 Deducciones aplicadas</h3>
<table>
<tr>
    <th>Empleado</th>
    <th>Deducción</th>
    <th>Monto</th>
</tr>

<?php while ($d = mysqli_fetch_assoc($res_deducciones)) { ?>
<tr>
    <td><?= $d['empleado_id'] ?></td>
    <td><?= $d['nombre_deduccion'] ?></td>
    <td><?= number_format($d['monto'], 2) ?></td>
</tr>
<?php } ?>
</table>

<br>
<a href="ver_nomina.php">← Volver</a>

</body>
</html>
