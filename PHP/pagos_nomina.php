<?php
session_start();
include("db.php");
$res = mysqli_query($conexion, "SELECT n.*, 
  (SELECT SUM(total_pagar) FROM detalle_nomina d WHERE d.id_nomina = n.id_nomina) as total_nomina
  FROM nomina n ORDER BY n.fecha_creacion DESC");
?>
<!doctype html><html><head><meta charset="utf-8"><title>Pagos</title></head><body>
<h2>Nóminas</h2>
<table border="1" cellpadding="6">
<tr><th>ID</th><th>Fechas</th><th>Tipo</th><th>Total</th><th>Estado</th><th>Acción</th></tr>
<?php while($n = mysqli_fetch_assoc($res)) { ?>
<tr>
 <td><?=$n['id_nomina']?></td>
 <td><?=$n['fecha_inicio']?> - <?=$n['fecha_fin']?></td>
 <td><?=$n['tipo']?></td>
 <td><?=number_format($n['total_nomina'],2)?></td>
 <td><?=$n['estado']?></td>
 <td>
    <a href="ver_nomina.php?id=<?=$n['id_nomina']?>">Ver</a>
    <?php if($n['estado']!='pagada') { ?>
      <a href="marcar_pagada.php?id=<?=$n['id_nomina']?>" onclick="return confirm('Marcar como pagada?')">Marcar pagada</a>
    <?php } ?>
 </td>
</tr>
<?php } ?>
</table>
</body></html>
