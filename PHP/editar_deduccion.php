<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

if (!isset($_GET['id'])) {
    die("ID no especificado");
}

$id = intval($_GET['id']);

$deduccion = mysqli_fetch_assoc(
    mysqli_query($conexion, "SELECT * FROM tipo_deduccion WHERE id_tipo=$id")
);

if (!$deduccion) {
    die("Deducción no encontrada");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo = $_POST['tipo'];
    $forma = $_POST['forma'];
    $porcentaje = floatval($_POST['porcentaje'] ?? 0);
    $monto_fijo = floatval($_POST['monto_fijo'] ?? 0);
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    mysqli_query($conexion, "
        UPDATE tipo_deduccion SET
            nombre='$nombre',
            tipo='$tipo',
            forma='$forma',
            porcentaje=$porcentaje,
            monto_fijo=$monto_fijo,
            obligatorio=$obligatorio,
            descripcion='$descripcion'
        WHERE id_tipo=$id
    ");

    header("Location: deducciones.php?edit=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Deducción</title>
<link rel="stylesheet" href="../css/deducciones.css">
</head>
<body>

<h2>✏ Editar Deducción</h2>

<form method="post" class="card">

    <label>Nombre</label>
    <input type="text" name="nombre" value="<?= $deduccion['nombre'] ?>" required>

    <label>Tipo</label>
    <select name="tipo">
        <option value="legal" <?= $deduccion['tipo']=='legal'?'selected':'' ?>>Legal</option>
        <option value="interna" <?= $deduccion['tipo']=='interna'?'selected':'' ?>>Interna</option>
    </select>

    <label>Forma de cálculo</label>
    <select name="forma" id="forma" onchange="toggleForma()">
        <option value="porcentaje" <?= $deduccion['forma']=='porcentaje'?'selected':'' ?>>Porcentaje</option>
        <option value="fijo" <?= $deduccion['forma']=='fijo'?'selected':'' ?>>Monto fijo</option>
    </select>

    <div id="campo_porcentaje">
        <label>Porcentaje (%)</label>
        <input type="number" step="0.01" name="porcentaje" value="<?= $deduccion['porcentaje'] ?>">
    </div>

    <div id="campo_fijo">
        <label>Monto fijo (Bs)</label>
        <input type="number" step="0.01" name="monto_fijo" value="<?= $deduccion['monto_fijo'] ?>">
    </div>

    <label>
        <input type="checkbox" name="obligatorio" <?= $deduccion['obligatorio']?'checked':'' ?>>
        Deducción obligatoria
    </label>

    <label>Descripción</label>
    <textarea name="descripcion"><?= $deduccion['descripcion'] ?></textarea>

    <button type="submit">Actualizar</button>

</form>

<script>
function toggleForma() {
    let forma = document.getElementById('forma').value;
    document.getElementById('campo_porcentaje').style.display =
        forma === 'porcentaje' ? 'block' : 'none';
    document.getElementById('campo_fijo').style.display =
        forma === 'fijo' ? 'block' : 'none';
}
toggleForma();
</script>

</body>
</html>
