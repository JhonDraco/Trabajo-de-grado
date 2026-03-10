<?php
include("db.php");

$id = intval($_GET['id']);
$inicio = $_GET['inicio'];
$fin = $_GET['fin'];
$tipo = $_GET['tipo'];

/* ===== FACTOR SEGÚN TIPO DE NÓMINA ===== */
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

/* ======================================================
   ASIGNACIONES
======================================================*/
$total_asig = 0;

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

/* ======================================================
   DEDUCCIONES LEGALES
======================================================*/
$ded_legales = mysqli_query($conexion,"
SELECT nombre, porcentaje
FROM tipo_deduccion
WHERE activo = 1
");

/* ======================================================
   DEDUCCIONES INDIVIDUALES
======================================================*/
$ded_personales = mysqli_query($conexion,"
SELECT nombre, monto, cuotas, cuota_actual
FROM deduccion_empleado
WHERE empleado_id = $id
AND activa = 1
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
<td><strong>Total Asignaciones</strong></td>
<td><strong><?= number_format($total_asig,2) ?> Bs</strong></td>
</tr>

</table>

<hr>

<h4>🔴 Deducciones</h4>

<table class="tabla-detalle">

<tr>
<th>Concepto</th>
<th>Monto</th>
</tr>

<?php
/* ===== DEDUCCIONES LEGALES ===== */
while($d = mysqli_fetch_assoc($ded_legales)){

    $monto = round($salario * ($d['porcentaje']/100),2);

    $total_ded += $monto;
?>

<tr>
<td><?= $d['nombre'] ?></td>
<td><?= number_format($monto,2) ?> Bs</td>
</tr>

<?php } ?>


<?php
/* ===== DEDUCCIONES PERSONALES ===== */
while($d = mysqli_fetch_assoc($ded_personales)){

    $cuota = $d['monto'] / $d['cuotas'];
    $cuota = round($cuota,2);

    $total_ded += $cuota;
?>

<tr>
<td>
<?= $d['nombre'] ?>
<br>
<small>Cuota <?= $d['cuota_actual']+1 ?> de <?= $d['cuotas'] ?></small>
</td>

<td><?= number_format($cuota,2) ?> Bs</td>
</tr>

<?php } ?>

<tr class="total">
<td><strong>Total Deducciones</strong></td>
<td><strong><?= number_format($total_ded,2) ?> Bs</strong></td>
</tr>

</table>

<hr>

<h3>
💰 Neto a pagar:
<?= number_format(($salario+$total_asig)-$total_ded,2) ?> Bs
</h3>