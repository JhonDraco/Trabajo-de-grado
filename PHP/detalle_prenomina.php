<?php
include("db.php");

$id = intval($_GET['id']);
$inicio = $_GET['inicio'];
$fin = $_GET['fin'];
$tipo = $_GET['tipo'];

switch ($tipo) {
    case 'semanal': $factor = 1/4; break;
    case 'quincenal': $factor = 1/2; break;
    default: $factor = 1;
}

/* ===== EMPLEADO ===== */
$emp = mysqli_fetch_assoc(mysqli_query($conexion,"
SELECT * FROM empleados WHERE id = $id
"));

$salario = round($emp['salario_base'] * $factor,2);

/* ===== ASIGNACIONES ===== */
$asig = mysqli_query($conexion,"
SELECT ta.nombre,
CASE
    WHEN ta.tipo='porcentaje' THEN ($salario * ta.porcentaje / 100)
    ELSE IFNULL(ae.monto, ta.porcentaje)
END monto
FROM tipo_asignacion ta
LEFT JOIN asignacion_empleado ae
ON ae.id_asignacion = ta.id_asignacion
AND ae.empleado_id = $id
AND ae.activa = 1
");

$total_asig = 0;

/* ===== DEDUCCIONES ===== */
$ded = mysqli_query($conexion,"
SELECT td.nombre,
CASE
    WHEN td.tipo='porcentaje' THEN ($salario * td.porcentaje / 100)
    ELSE IFNULL(de.monto, td.porcentaje)
END monto
FROM tipo_deduccion td
LEFT JOIN deduccion_empleado de
ON de.id_tipo = td.id_tipo
AND de.empleado_id = $id
AND de.activa = 1
");

$total_ded = 0;
?>

<h3><?= $emp['nombre']." ".$emp['apellido'] ?></h3>


<p><strong>Salario Base:</strong> <?= number_format($salario,2) ?> Bs</p>

<hr>

<h4>🟢 Asignaciones</h4>

<table class="tabla-detalle">
<tr>
<th>Concepto</th>
<th>Monto</th>
</tr>

<?php while($a=mysqli_fetch_assoc($asig)){ 
    $total_asig += $a['monto'];
?>

<tr>
<td><?= $a['nombre'] ?></td>
<td><?= number_format($a['monto'],2) ?> Bs</td>
</tr>

<?php } ?>

<tr class="total">
<td>Total Asignaciones</td>
<td><?= number_format($total_asig,2) ?> Bs</td>
</tr>

</table>

<hr>

<h4>🔴 Deducciones</h4>

<table class="tabla-detalle">

<tr>
<th>Concepto</th>
<th>Monto</th>
</tr>

<?php while($d=mysqli_fetch_assoc($ded)){ 
    $total_ded += $d['monto'];
?>

<tr>
<td><?= $d['nombre'] ?></td>
<td><?= number_format($d['monto'],2) ?> Bs</td>
</tr>

<?php } ?>

<tr class="total">
<td>Total Deducciones</td>
<td><?= number_format($total_ded,2) ?> Bs</td>
</tr>

</table>

<hr>

<h3>
💰 Neto a pagar:
<?= number_format(($salario+$total_asig)-$total_ded,2) ?> Bs
</h3>