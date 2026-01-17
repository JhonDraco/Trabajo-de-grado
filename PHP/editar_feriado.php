<?php
session_start();
include("db.php");

if (!isset($_GET['id'])) {
    header("Location: feriados.php");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos del feriado
$consulta = mysqli_query($conexion, "SELECT * FROM feriados WHERE id_feriado = $id");
$feriado = mysqli_fetch_assoc($consulta);

if (!$feriado) {
    echo "Feriado no encontrado";
    exit();
}

// Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $fecha = $_POST['fecha'];
    $tipo = $_POST['tipo'];
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = $_POST['descripcion'];

    $update = "UPDATE feriados SET 
                nombre='$nombre',
                fecha='$fecha',
                tipo='$tipo',
                obligatorio=$obligatorio,
                descripcion='$descripcion'
               WHERE id_feriado=$id";

    mysqli_query($conexion, $update);

    header("Location: feriados.php?editado=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Editar Feriado</title>
<link rel="stylesheet" href="../css/feriados.css">
</head>
<body>

<h2>✏️ Editar Feriado</h2>

<form method="post">
    <label>Nombre:</label>
    <input type="text" name="nombre" value="<?= $feriado['nombre'] ?>" required>

    <label>Fecha:</label>
    <input type="date" name="fecha" value="<?= $feriado['fecha'] ?>" required>

    <label>Tipo:</label>
    <select name="tipo">
        <option value="nacional" <?= $feriado['tipo']=='nacional'?'selected':'' ?>>Nacional</option>
        <option value="regional" <?= $feriado['tipo']=='regional'?'selected':'' ?>>Regional</option>
        <option value="interno" <?= $feriado['tipo']=='interno'?'selected':'' ?>>Interno</option>
    </select>

    <label>
        <input type="checkbox" name="obligatorio" <?= $feriado['obligatorio'] ? 'checked' : '' ?>>
        Obligatorio
    </label>

    <label>Descripción:</label>
    <textarea name="descripcion"><?= $feriado['descripcion'] ?></textarea>

    <button type="submit">💾 Guardar Cambios</button>
    <a href="feriados.php">Cancelar</a>
</form>

</body>
</html>
