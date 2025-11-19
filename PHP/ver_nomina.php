<?php
include("db.php");

// Ordenar por la fecha en que se creó la nómina
$consulta = "SELECT * FROM nomina ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ver Nóminas</title>
</head>
<body>

<h2>📄 Nóminas Generadas</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID Nómina</th>
    <th>Período</th>
    <th>Tipo</th>
    <th>Estado</th>
    <th>Creada por</th>
    <th>Fecha creación</th>
    <th>Acciones</th>
</tr>

<?php while ($n = mysqli_fetch_array($nominas)) { ?>
<tr>
    <td><?= $n['id_nomina'] ?></td>

    <!-- Mostrar fecha inicio y fin -->
    <td><?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>

    <td><?= $n['tipo'] ?></td>
    <td><?= $n['estado'] ?></td>
    <td><?= $n['creada_por'] ?></td>
    <td><?= $n['fecha_creacion'] ?></td>

    <td>
        <a href="ver_detalle_nomina.php?id=<?= $n['id_nomina'] ?>">Ver Detalle</a> | 
        <a href="eliminar_nomina.php?id=<?= $n['id_nomina'] ?>" onclick="return confirm('¿Eliminar esta nómina?')">Eliminar</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
