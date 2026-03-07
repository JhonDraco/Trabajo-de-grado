<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

/* ===============================
   CREAR ASIGNACIÓN POR EMPLEADO
================================*/
if (isset($_POST['guardar'])) {

    $empleado_id = $_POST['empleado_id'];
    $nombre      = $_POST['nombre'];
    $tipo        = $_POST['tipo'];
    $monto       = $_POST['monto'];

    mysqli_query($conexion, "
        INSERT INTO asignacion_empleado
        (empleado_id, nombre, tipo, monto)
        VALUES ($empleado_id, '$nombre', '$tipo', $monto)
    ");

    header("Location: asignacion_empleado.php");
    exit();
}

/* ===============================
   ACTIVAR / DESACTIVAR
================================*/
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];

    mysqli_query($conexion, "
        UPDATE asignacion_empleado
        SET activa = IF(activa=1,0,1)
        WHERE id_asig_emp = $id
    ");

    header("Location: asignacion_empleado.php");
    exit();
}

/* ===============================
   CARGAR DATOS
================================*/
$empleados = mysqli_query($conexion, "
    SELECT id, nombre, apellido
    FROM empleados
    WHERE estado = 'activo'
");

$asignaciones = mysqli_query($conexion, "
    SELECT ae.*, e.nombre AS emp_nombre, e.apellido
    FROM asignacion_empleado ae
    JOIN empleados e ON ae.empleado_id = e.id
    ORDER BY ae.creada_en DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Asignaciones por Empleado</title>
<link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<h2>➕ Asignaciones por Empleado</h2>

<!-- ===== FORMULARIO ===== -->
<form method="POST" class="card">

    <label>Empleado</label>
    <select name="empleado_id" required>
        <option value="">Seleccione</option>
        <?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
            <option value="<?= $e['id'] ?>">
                <?= $e['nombre']." ".$e['apellido'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Nombre de la asignación</label>
    <input type="text" name="nombre" required>

    <label>Tipo</label>
    <select name="tipo" required>
        <option value="fijo">Monto fijo</option>
        <option value="porcentaje">Porcentaje</option>
    </select>

    <label>Monto / Porcentaje</label>
    <input type="number" step="0.01" name="monto" required>

    <button type="submit" name="guardar">
        Guardar Asignación
    </button>

</form>

<!-- ===== LISTADO ===== -->
<table border="1" cellpadding="6" cellspacing="0">
<thead>
<tr>
    <th>Empleado</th>
    <th>Asignación</th>
    <th>Tipo</th>
    <th>Monto</th>
    <th>Estado</th>
    <th>Acción</th>
</tr>
</thead>

<tbody>
<?php while ($a = mysqli_fetch_assoc($asignaciones)) { ?>
<tr>
    <td><?= $a['emp_nombre']." ".$a['apellido'] ?></td>
    <td><?= $a['nombre'] ?></td>
    <td><?= ucfirst($a['tipo']) ?></td>
    <td><?= number_format($a['monto'],2) ?></td>
    <td><?= $a['activa'] ? 'Activa' : 'Inactiva' ?></td>
    <td>
        <a href="?toggle=<?= $a['id_asig_emp'] ?>">
            <?= $a['activa'] ? 'Desactivar' : 'Activar' ?>
        </a>
    </td>
</tr>
<?php } ?>
</tbody>
</table>

</body>
</html>
