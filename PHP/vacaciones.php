<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

/* ===========================
   PROCESAR FORMULARIO
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $creada_por = $_SESSION['usuario'];

    // Calcular días totales
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $fin->modify('+1 day');

    $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);

    $dias_totales = 0;
    $dias_feriados = 0;
    $feriados_ids = [];

    foreach ($periodo as $fecha) {
        $dias_totales++;

        $f = $fecha->format('Y-m-d');
        $q = mysqli_query($conexion, "SELECT id_feriado FROM feriados WHERE fecha='$f'");
        if (mysqli_num_rows($q) > 0) {
            $dias_feriados++;
            $row = mysqli_fetch_assoc($q);
            $feriados_ids[] = $row['id_feriado'];
        }
    }

    $dias_habiles = $dias_totales - $dias_feriados;

    // Insertar vacaciones
    mysqli_query($conexion, "
        INSERT INTO vacaciones (
            empleado_id, fecha_inicio, fecha_fin,
            dias_solicitados, dias_habiles, dias_feriados,
            observaciones, creada_por
        ) VALUES (
            $empleado_id, '$fecha_inicio', '$fecha_fin',
            $dias_totales, $dias_habiles, $dias_feriados,
            '$observaciones', '$creada_por'
        )
    ");

    $id_vacacion = mysqli_insert_id($conexion);

    // Guardar feriados asociados
    foreach ($feriados_ids as $id_feriado) {
        mysqli_query($conexion, "
            INSERT INTO vacaciones_feriados (id_vacacion, id_feriado)
            VALUES ($id_vacacion, $id_feriado)
        ");
    }

    header("Location: vacaciones.php?ok=1");
    exit();
}

/* ===========================
   DATOS PARA VISTA
=========================== */
$empleados = mysqli_query($conexion, "
    SELECT id, nombre, apellido 
    FROM empleados 
    WHERE estado='activo'
");

$vacaciones = mysqli_query($conexion, "
    SELECT v.*, e.nombre, e.apellido
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id = e.id
    ORDER BY v.creada_en DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Vacaciones</title>
<link rel="stylesheet" href="../css/vacaciones.css">
</head>
<body>

<h2>🏖️ Gestión de Vacaciones</h2>

<?php if (isset($_GET['ok'])) { ?>
<p style="color:green;">Solicitud registrada correctamente</p>
<?php } ?>

<!-- FORMULARIO -->
<form method="post">
    <label>Empleado</label>
    <select name="empleado_id" required>
        <option value="">Seleccione</option>
        <?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
            <option value="<?= $e['id'] ?>">
                <?= $e['nombre'] . " " . $e['apellido'] ?>
            </option>
        <?php } ?>
    </select>

    <label>Fecha Inicio</label>
    <input type="date" name="fecha_inicio" required>

    <label>Fecha Fin</label>
    <input type="date" name="fecha_fin" required>

    <label>Observaciones</label>
    <textarea name="observaciones"></textarea>

    <button type="submit">Solicitar Vacaciones</button>
</form>

<hr>

<!-- LISTADO -->
<h3>📋 Solicitudes de Vacaciones</h3>

<table border="1" cellpadding="8">
<tr>
    <th>Empleado</th>
    <th>Periodo</th>
    <th>Días</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php while ($v = mysqli_fetch_assoc($vacaciones)) { ?>
<tr>
    <td><?= $v['nombre'] . " " . $v['apellido'] ?></td>
    <td><?= $v['fecha_inicio'] ?> al <?= $v['fecha_fin'] ?></td>
    <td><?= $v['dias_habiles'] ?> hábiles</td>
    <td><?= ucfirst($v['estado']) ?></td>
    <td>
        <a href="aprobar_vacaciones.php?id=<?= $v['id_vacacion'] ?>">Aprobar</a> |
        <a href="rechazar_vacaciones.php?id=<?= $v['id_vacacion'] ?>">Rechazar</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>
