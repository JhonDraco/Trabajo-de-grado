<?php
include("db.php");

$id_nomina = intval($_GET['id'] ?? 0);

$sql = "
SELECT dn.*, e.nombre, e.apellido, e.cedula
FROM detalle_nomina dn
JOIN empleados e ON dn.empleado_id = e.id
WHERE dn.id_nomina = $id_nomina
";

$detalles = mysqli_query($conexion, $sql);
?>

<table style="width:100%; border-collapse:collapse;">
<tr>
    <th>Cédula</th>
    <th>Empleado</th>
    <th>Salario Base</th>
    <th>Asignaciones</th>
    <th>Deducciones</th>
    <th>Total a Pagar</th>
    <th>Ver Detalle PDF</th>
</tr>

<?php while ($d = mysqli_fetch_assoc($detalles)) { ?>
<tr>
    <td><?= $d['cedula'] ?></td>
    <td><?= $d['nombre'].' '.$d['apellido'] ?></td>
    <td><?= number_format($d['salario_base'],2) ?></td>
    <td><?= number_format($d['total_asignaciones'],2) ?></td>
    <td><?= number_format($d['total_deducciones'],2) ?></td>
    <td><strong><?= number_format($d['total_pagar'],2) ?></strong></td>
    <td>
        <a href="ver_detalle_individual.php?id_detalle=<?= $d['id_detalle'] ?>" target="_blank">
            📰 Ver
        </a>
    </td>
</tr>
<?php } ?>
</table>
