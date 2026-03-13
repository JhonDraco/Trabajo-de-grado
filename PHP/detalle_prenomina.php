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
<div class="detalle-dashboard">

<div class="detalle-top">

<div class="empleado-info">
<i class="ri-user-3-line"></i>
<div>
<h3><?= $emp['nombre']." ".$emp['apellido'] ?></h3>
<span>Salario período</span>
</div>
</div>

<div class="salario-big">
<?= number_format($salario,2) ?> Bs
</div>

</div>


<div class="detalle-grid">

<!-- ASIGNACIONES -->

<div class="detalle-box asignaciones">

<h4>🟢 Asignaciones</h4>

<div class="detalle-list">

<?php while($a=mysqli_fetch_assoc($asig)){ 
$total_asig += $a['monto'];
?>

<div class="fila">

<span><?= $a['nombre'] ?></span>

<strong class="positivo">
<?= number_format($a['monto'],2) ?> Bs
</strong>

</div>

<?php } ?>

</div>

<div class="box-total positivo">

Total
<?= number_format($total_asig,2) ?> Bs

</div>

</div>


<!-- DEDUCCIONES -->

<div class="detalle-box deducciones">

<h4>🔴 Deducciones</h4>

<div class="detalle-list">

<?php
while($d = mysqli_fetch_assoc($ded_legales)){

$monto = round($salario * ($d['porcentaje']/100),2);
$total_ded += $monto;
?>

<div class="fila">

<span><?= $d['nombre'] ?></span>

<strong class="negativo">
<?= number_format($monto,2) ?> Bs
</strong>

</div>

<?php } ?>


<?php
while($d = mysqli_fetch_assoc($ded_personales)){

$cuota = round($d['monto'] / $d['cuotas'],2);
$total_ded += $cuota;
?>

<div class="fila">

<span>
<?= $d['nombre'] ?>
<small>(<?= $d['cuota_actual']+1 ?>/<?= $d['cuotas'] ?>)</small>
</span>

<strong class="negativo">
<?= number_format($cuota,2) ?> Bs
</strong>

</div>

<?php } ?>

</div>

<div class="box-total negativo">

Total
<?= number_format($total_ded,2) ?> Bs

</div>

</div>

</div>


<div class="neto-final">

<span>Neto a pagar</span>

<strong>

<?= number_format(($salario+$total_asig)-$total_ded,2) ?> Bs

</strong>

</div>

</div>