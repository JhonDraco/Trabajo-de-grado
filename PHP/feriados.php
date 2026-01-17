<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

/* ====== GUARDAR FERIADO ====== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = $_POST['descripcion'];

    $sql = "INSERT INTO feriados (nombre, fecha, tipo, obligatorio, descripcion)
            VALUES ('$nombre', '$fecha', '$tipo', $obligatorio, '$descripcion')";
    mysqli_query($conexion, $sql);

    header("Location: feriados.php");
    exit();
}

/* ====== LISTAR FERIADOS ====== */
$feriados = mysqli_query($conexion, "SELECT * FROM feriados ORDER BY fecha ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Feriados</title>
</head>
<body>

<h2>📅 Gestión de Feriados</h2>

<!-- FORMULARIO -->
<h3>➕ Registrar Feriado</h3>
<form method="post">
    <label>Nombre del feriado:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Fecha:</label><br>
    <input type="date" name="fecha" required><br><br>

    <label>Tipo:</label><br>
    <select name="tipo">
        <option value="nacional">Nacional</option>
        <option value="regional">Regional</option>
        <option value="interno">Interno</option>
    </select><br><br>

    <label>
        <input type="checkbox" name="obligatorio" checked>
        Feriado obligatorio (afecta nómina)
    </label><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion"></textarea><br><br>

    <button type="submit">Guardar Feriado</button>
</form>

<hr>

<!-- LISTADO -->
<h3>📋 Lista de Feriados</h3>

<table border="1" cellpadding="6">
<tr>
    <th>Fecha</th>
    <th>Nombre</th>
    <th>Tipo</th>
    <th>Obligatorio</th>
    <th>Editar / Borrar</th>
</tr>

<?php while ($f = mysqli_fetch_assoc($feriados)) { ?>
<tr>
    <td><?= $f['fecha'] ?></td>
    <td><?= $f['nombre'] ?></td>
    <td><?= ucfirst($f['tipo']) ?></td>
    <td><?= $f['obligatorio'] ? 'Sí' : 'No' ?></td>
    
</tr>
<?php } ?>

</table>

</body>
</html>
