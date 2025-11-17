<?php
session_start();
// aquí debes mantener tu protección de admin
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// Agregar deducción
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $porc = floatval($_POST['porcentaje']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $oblig = isset($_POST['obligatorio']) ? 1 : 0;
    mysqli_query($conexion, "INSERT INTO tipo_deduccion (nombre, porcentaje, obligatorio, descripcion) VALUES ('$nombre', $porc, $oblig, '$desc')");
    header("Location: deducciones.php");
    exit();
}

$res = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Deducciones</title></head>
<body>
<h2>Gestionar Deducciones</h2>

<h3>Añadir deducción</h3>
<form method="post">
    <label>Nombre: <input name="nombre" required></label><br>
    <label>Porcentaje: <input name="porcentaje" required step="0.01" value="0.00"></label><br>
    <label>Obligatoria: <input type="checkbox" name="obligatorio" checked></label><br>
    <label>Descripción:<br><textarea name="descripcion"></textarea></label><br>
    <button type="submit">Guardar</button>
</form>

<h3>Lista</h3>
<table border="1" cellpadding="6">
<tr><th>Nombre</th><th>%</th><th>Oblig.</th><th>Acción</th></tr>
<?php while($d = mysqli_fetch_assoc($res)) { ?>
<tr>
  <td><?=htmlspecialchars($d['nombre'])?></td>
  <td><?=number_format($d['porcentaje'],2)?>%</td>
  <td><?= $d['obligatorio'] ? 'Sí' : 'No' ?></td>
  <td><a href="eliminar_deduccion.php?id=<?=$d['id_tipo']?>" onclick="return confirm('Eliminar?')">Eliminar</a></td>
</tr>
<?php } ?>
</table>
</body>
</html>
