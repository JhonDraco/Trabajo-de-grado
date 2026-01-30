<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo = $_POST['tipo'];
    $forma = $_POST['forma'];
    $porcentaje = floatval($_POST['porcentaje'] ?? 0);
    $monto_fijo = floatval($_POST['monto_fijo'] ?? 0);
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    mysqli_query($conexion, "
        INSERT INTO tipo_deduccion 
        (nombre, tipo, forma, porcentaje, monto_fijo, obligatorio, activo, descripcion)
        VALUES
        ('$nombre', '$tipo', '$forma', $porcentaje, $monto_fijo, $obligatorio, 1, '$descripcion')
    ");

    header("Location: deducciones.php?ok=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Deducción</title>
<link rel="stylesheet" href="../css/deducciones.css">
</head>
<body>

<h2>➖ Crear Nueva Deducción</h2>

<form method="post" class="card">

    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Tipo</label>
    <select name="tipo">
        <option value="legal">Legal</option>
        <option value="interna">Interna</option>
    </select>

    <label>Forma de cálculo</label>
    <select name="forma" id="forma" onchange="toggleForma()">
        <option value="porcentaje">Porcentaje</option>
        <option value="fijo">Monto fijo</option>
    </select>

    <div id="campo_porcentaje">
        <label>Porcentaje (%)</label>
        <input type="number" step="0.01" name="porcentaje" value="0">
    </div>

    <div id="campo_fijo" style="display:none;">
        <label>Monto fijo (Bs)</label>
        <input type="number" step="0.01" name="monto_fijo" value="0">
    </div>

    <label>
        <input type="checkbox" name="obligatorio" checked>
        Deducción obligatoria
    </label>

    <label>Descripción</label>
    <textarea name="descripcion"></textarea>

    <button type="submit">Guardar Deducción</button>

</form>

<script>
function toggleForma() {
    let forma = document.getElementById('forma').value;
    document.getElementById('campo_porcentaje').style.display =
        forma === 'porcentaje' ? 'block' : 'none';
    document.getElementById('campo_fijo').style.display =
        forma === 'fijo' ? 'block' : 'none';
}
</script>

</body>
</html>
