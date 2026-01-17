<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

$anio_actual = date('Y');

/* ===========================
   SALDOS DE VACACIONES
=========================== */
$saldos = mysqli_query($conexion, "
    SELECT 
        e.id,
        e.cedula,
        e.nombre,
        e.apellido,
        vs.anio,
        vs.dias_acumulados,
        vs.dias_disfrutados,
        vs.dias_pendientes
    FROM vacaciones_saldo vs
    INNER JOIN empleados e ON vs.empleado_id = e.id
    WHERE vs.anio = $anio_actual
    ORDER BY e.nombre
");

/* ===========================
   PROCESAR FORMULARIO
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $creada_por = $_SESSION['usuario'];

    // Obtener saldo
    $qSaldo = mysqli_query($conexion, "
        SELECT dias_pendientes 
        FROM vacaciones_saldo 
        WHERE empleado_id=$empleado_id AND anio=$anio_actual
    ");

    if (mysqli_num_rows($qSaldo) == 0) {
        header("Location: vacaciones.php?error=saldo");
        exit();
    }

    $saldo = mysqli_fetch_assoc($qSaldo);
    $dias_disponibles = $saldo['dias_pendientes'];

    // Calcular días
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

    // Validar saldo suficiente
    if ($dias_habiles > $dias_disponibles) {
        header("Location: vacaciones.php?error=exceso");
        exit();
    }

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

<?php if (isset($_GET['error']) && $_GET['error']=='saldo') { ?>
<p style="color:red;">❌ El empleado no tiene saldo inicializado</p>
<?php } ?>

<?php if (isset($_GET['error']) && $_GET['error']=='exceso') { ?>
<p style="color:red;">❌ No tiene suficientes días disponibles</p>
<?php } ?>

<!-- SALDOS -->
<h3>📊 Saldo de Vacaciones <?= $anio_actual ?></h3>
<table border="1" cellpadding="8">
<tr>
    <th>Empleado</th>
    <th>Acumulados</th>
    <th>Disfrutados</th>
    <th>Pendientes</th>
</tr>

<?php while ($s = mysqli_fetch_assoc($saldos)) { ?>
<tr>
    <td><?= $s['nombre']." ".$s['apellido'] ?></td>
    <td><?= $s['dias_acumulados'] ?></td>
    <td><?= $s['dias_disfrutados'] ?></td>
    <td><strong><?= $s['dias_pendientes'] ?></strong></td>
</tr>
<?php } ?>
</table>

<hr>

<!-- FORMULARIO -->
<h3>📝 Solicitar Vacaciones</h3>
<form method="post">
    <label>Empleado</label>
    <select name="empleado_id" required>
        <option value="">Seleccione</option>
        <?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
            <option value="<?= $e['id'] ?>">
                <?= $e['nombre']." ".$e['apellido'] ?>
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
<h3>📋 Solicitudes</h3>
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
    <td><?= $v['nombre']." ".$v['apellido'] ?></td>
    <td><?= $v['fecha_inicio']." al ".$v['fecha_fin'] ?></td>
    <td><?= $v['dias_habiles'] ?></td>
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
