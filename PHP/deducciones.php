<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

/* =========================================
   CREAR DEDUCCIÓN GENERAL
========================================= */
if (isset($_POST['crear_deduccion_general'])) {

    $nombre      = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $porcentaje  = floatval($_POST['porcentaje']);
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);

    mysqli_query($conexion, "
        INSERT INTO tipo_deduccion (nombre, porcentaje, obligatorio, descripcion)
        VALUES ('$nombre', $porcentaje, $obligatorio, '$descripcion')
    ");
}

/* =========================================
   CREAR DEDUCCIÓN POR EMPLEADO
========================================= */
if (isset($_POST['crear_deduccion_empleado'])) {

    $empleado_id = intval($_POST['empleado_id']);
    $nombre      = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo        = $_POST['tipo'];
    $monto       = floatval($_POST['monto']);
    $cuotas      = intval($_POST['cuotas']);

    if ($cuotas <= 0) $cuotas = 1;

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
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Deducciones</title>
<link rel="stylesheet" href="../css/deducciones.css">
</head>

<body>

<h2>➖ Módulo de Deducciones</h2>

<!-- ===============================
     DEDUCCIONES GENERALES
================================ -->

<h3>📌 Crear Deducción General</h3>

<form method="POST">
    <input type="hidden" name="crear_deduccion_general" value="1">

    <label>Nombre</label>
    <input type="text" name="nombre" required>

    <label>Porcentaje (%)</label>
    <input type="number" step="0.01" name="porcentaje" required>

    <label>
        <input type="checkbox" name="obligatorio" checked>
        Obligatoria
    </label>

    <label>Descripción</label>
    <textarea name="descripcion"></textarea>

    <button type="submit">Guardar</button>
</form>

<br>

<h3>📄 Deducciones Generales</h3>

<table border="1" cellpadding="5">
<tr>
    <th>Nombre</th>
    <th>%</th>
    <th>Obligatoria</th>
    <th>Descripción</th>
</tr>

<?php
$generales = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
while ($d = mysqli_fetch_assoc($generales)) {
?>
<tr>
    <td><?= $d['nombre'] ?></td>
    <td><?= $d['porcentaje'] ?>%</td>
    <td><?= $d['obligatorio'] ? 'Sí' : 'No' ?></td>
    <td><?= $d['descripcion'] ?></td>
</tr>
<?php } ?>
</table>

<hr>

<!-- ===============================
     DEDUCCIONES POR EMPLEADO
================================ -->

<h3>👤 Asignar Deducción a Empleado</h3>

<form method="POST">
    <input type="hidden" name="crear_deduccion_empleado" value="1">

    <label>Empleado</label>
    <select name="empleado_id" required>
        <option value="">Seleccione</option>
        <?php
        $emps = mysqli_query($conexion, "
            SELECT id, nombre, apellido
            FROM empleados
            WHERE estado='activo'
            ORDER BY nombre
        ");
        while ($e = mysqli_fetch_assoc($emps)) {
            echo "<option value='{$e['id']}'>{$e['nombre']} {$e['apellido']}</option>";
        }
        ?>
    </select>

    <label>Concepto</label>
    <input type="text" name="nombre" required placeholder="Ej: Préstamo">

    <label>Tipo</label>
    <select name="tipo">
        <option value="fijo">Monto fijo</option>
        <option value="porcentaje">Porcentaje</option>
    </select>

    <label>Monto</label>
    <input type="number" step="0.01" name="monto" required>

    <label>Cuotas</label>
    <input type="number" name="cuotas" value="1" min="1">

    <button type="submit">Asignar</button>
</form>

<br>

<h3>📋 Deducciones por Empleado</h3>

<table border="1" cellpadding="5">
<tr>
    <th>Empleado</th>
    <th>Deducción</th>
    <th>Monto</th>
    <th>Cuotas</th>
    <th>Estado</th>
</tr>

<?php
$listado = mysqli_query($conexion, "
    SELECT d.*, e.nombre AS emp_nombre, e.apellido
    FROM deduccion_empleado d
    INNER JOIN empleados e ON d.empleado_id = e.id
    ORDER BY d.activa DESC
");

while ($d = mysqli_fetch_assoc($listado)) {
?>
<tr>
    <td><?= $d['emp_nombre'] ?> <?= $d['apellido'] ?></td>
    <td><?= $d['nombre'] ?></td>
    <td><?= number_format($d['monto'],2) ?></td>
    <td><?= $d['cuota_actual'] ?> / <?= $d['cuotas'] ?></td>
    <td><?= $d['activa'] ? 'Activa' : 'Finalizada' ?></td>
</tr>
<?php } ?>
</table>

</body>
</html>
