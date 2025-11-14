<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo = ($_POST['tipo'] === 'porcentaje') ? 'porcentaje' : 'fijo';
    $valor = floatval($_POST['valor']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    mysqli_query($conexion, "INSERT INTO tipo_asignacion (nombre, tipo, valor, descripcion) VALUES ('$nombre','$tipo',$valor,'$desc')");
    header("Location: asignaciones.php");
    exit();
}

$res = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Asignaciones</title></head>
<body>
<h2>Gestionar Asignaciones</h2>

<form method="post">
    <label>Nombre: <input name="nombre" required></label><br>
    <label>Tipo:
        <select name="tipo">
            <option value="fijo">Fijo</option>
            <option value="porcentaje">Porcentaje</option>
        </select>
    </label><br>
    <label>Valor (monto o %): <input name="valor" step="0.01" value="0.00"></label><br>
    <label>Descripción:<br><textarea name="descripcion"></textarea></label><br>
    <button type="submit">Guardar</button>
</form>

<h3>Lista</h3>
<table border="1" cellpadding="6">
<tr><th>Nombre</th><th>Tipo</th><th>Valor</th><th>Acción</th></tr>
<?php while($d = mysqli_fetch_assoc($res)) { ?>
<tr>
  <td><?=htmlspecialchars($d['nombre'])?></td>
  <td><?= $d['tipo'] ?></td>
  <td><?= number_format($d['valor'],2) ?><?= $d['tipo']=='porcentaje' ? '%' : '' ?></td>
  <td><a href="eliminar_asignacion.php?id=<?=$d['id_asignacion']?>" onclick="return confirm('Eliminar?')">Eliminar</a></td>
</tr>
<?php } ?>
</table>
</body>
</html>
