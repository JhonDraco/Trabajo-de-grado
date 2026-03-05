<?php
include("db.php");

$id_empleado = $_GET['id'];

/* ==============================
   BUSCAR DETALLE NOMINA
============================== */

$sql = $conexion->query("
SELECT 
d.*, 
e.nombre,
e.apellido
FROM detalle_nomina d
INNER JOIN empleados e ON e.id = d.empleado_id
WHERE d.empleado_id = '$id_empleado'
ORDER BY d.id_detalle DESC
LIMIT 1
");

$datos = $sql->fetch_assoc();

$nombre = $datos['nombre']." ".$datos['apellido'];
$salario = $datos['salario_base'];
$id_detalle = $datos['id_detalle'];

/* ==============================
   ASIGNACIONES
============================== */

$asignaciones = [];

$sql_asig = $conexion->query("
SELECT 
da.monto,
ta.nombre
FROM detalle_asignacion da
INNER JOIN tipo_asignacion ta 
ON ta.id_asignacion = da.id_asignacion
WHERE da.id_detalle = '$id_detalle'
");

$total_asig = 0;

while($row = $sql_asig->fetch_assoc()){

    $asignaciones[] = $row;
    $total_asig += $row['monto'];
}

/* ==============================
   DEDUCCIONES
============================== */

$deducciones = [];

$sql_ded = $conexion->query("
SELECT 
dd.monto,
td.nombre
FROM detalle_deduccion dd
LEFT JOIN tipo_deduccion td
ON td.id_tipo = dd.id_tipo
WHERE dd.id_detalle = '$id_detalle'
");

$total_ded = 0;

while($row = $sql_ded->fetch_assoc()){

    $deducciones[] = $row;
    $total_ded += $row['monto'];
}

/* ==============================
   TOTAL A PAGAR
============================== */

$total_pagar = ($salario + $total_asig) - $total_ded;

?>

<h2><?php echo $nombre; ?></h2>

<hr>

<p><strong>Salario Base:</strong> <?php echo number_format($salario,2); ?> Bs</p>

<hr>

<h3>Asignaciones</h3>

<table class="tabla-detalle">

<tr>
<th>Concepto</th>
<th>Monto</th>
</tr>

<?php foreach($asignaciones as $a){ ?>

<tr>
<td><?php echo $a['nombre']; ?></td>
<td><?php echo number_format($a['monto'],2); ?> Bs</td>
</tr>

<?php } ?>

<tr>
<td><strong>Total Asignaciones</strong></td>
<td><strong><?php echo number_format($total_asig,2); ?> Bs</strong></td>
</tr>

</table>

<hr>

<h3>Deducciones</h3>

<table class="tabla-detalle">

<tr>
<th>Concepto</th>
<th>Monto</th>
</tr>

<?php foreach($deducciones as $d){ ?>

<tr>
<td><?php echo $d['nombre'] ?? "Deducción"; ?></td>
<td><?php echo number_format($d['monto'],2); ?> Bs</td>
</tr>

<?php } ?>

<tr>
<td><strong>Total Deducciones</strong></td>
<td><strong><?php echo number_format($total_ded,2); ?> Bs</strong></td>
</tr>

</table>

<hr>

<h3>Total a Pagar</h3>

<h2 style="color:green;">
<?php echo number_format($total_pagar,2); ?> Bs
</h2>