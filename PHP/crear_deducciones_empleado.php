<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

/* ===========================
   GUARDAR DEDUCCIÓN
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $nombre      = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo        = $_POST['tipo'];
    $monto       = floatval($_POST['monto']);
    $cuotas      = intval($_POST['cuotas']);

    if ($cuotas <= 0) {
        $cuotas = 1;
    }

    mysqli_query($conexion, "
        INSERT INTO deduccion_empleado (
            empleado_id,
            nombre,
            tipo,
            monto,
            cuotas,
            cuota_actual,
            activa
        ) VALUES (
            $empleado_id,
            '$nombre',
            '$tipo',
            $monto,
            $cuotas,
            0,
            1
        )
    ");

    header("Location: deducciones.php?ok=1");
    exit();
}

/* ===========================
   EMPLEADOS
=========================== */
$empleados = mysqli_query($conexion, "
    SELECT id, nombre, apellido
    FROM empleados
    WHERE estado='activo'
    ORDER BY nombre
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asignar Deducción a Empleado</title>
<link rel="stylesheet" href="../css/deducciones.css">
</head>
<body>

<h2>➖ Asignar Deducción a Empleado</h2>

<form method="POST">

    <label>Empleado</label>
    <select name="empleado_id" required>
        <option value="">Seleccione</option>
        <?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
            <option value="<?= $e['id'] ?>">
                <?= $e['nombre'] . " " . $e['apellido'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Nombre de la Deducción</label>
    <input type="text" name="nombre" required placeholder="Ej: Préstamo, Adelanto, Multa">

    <label>Tipo</label>
    <select name="tipo">
        <option value="fijo">Monto fijo</option>
        <option value="porcentaje">Porcentaje</option>
    </select>

    <label>Monto</label>
    <input type="number" name="monto" step="0.01" required>

    <label>Cuotas</label>
    <input type="number" name="cuotas" value="1" min="1">

    <button type="submit">Guardar Deducción</button>

</form>

</body>
</html>
