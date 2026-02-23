<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

function calcularDiasVacaciones($fecha_ingreso, $dias_anuales = 15) {
    $fechaIngreso = new DateTime($fecha_ingreso);
    $hoy = new DateTime();

    if ($fechaIngreso > $hoy) {
        return 0;
    }

    $intervalo = $fechaIngreso->diff($hoy);

    $mesesTrabajados = ($intervalo->y * 12) + $intervalo->m;

    $diasPorMes = $dias_anuales / 12;

    $diasAcumulados = $mesesTrabajados * $diasPorMes;

    return floor($diasAcumulados);
}


$anio_actual = date('Y');

/* =====================================
   FUNCIÓN CALCULAR DÍAS ACUMULADOS
===================================== */
function calcularDiasVacaciones($fecha_ingreso, $dias_anuales = 15) {

    $fechaIngreso = new DateTime($fecha_ingreso);
    $hoy = new DateTime();

    if ($fechaIngreso > $hoy) {
        return 0;
    }

    $intervalo = $fechaIngreso->diff($hoy);

    $mesesTrabajados = ($intervalo->y * 12) + $intervalo->m;

    $diasPorMes = $dias_anuales / 12;

    $diasAcumulados = $mesesTrabajados * $diasPorMes;

    return floor($diasAcumulados);
}

/* =====================================
   SALDOS DE VACACIONES (CON DÍAS ACUMULADOS)
===================================== */
$saldos = mysqli_query($conexion, "
    SELECT 
        e.id,
        e.cedula,
        e.nombre,
        e.apellido,
        e.fecha_ingreso,
        COALESCE(vs.dias_disfrutados, 0) as dias_disfrutados,
        COALESCE(vs.dias_acumulados, 0) as dias_acumulados -- Agregado para evitar el Warning
    FROM empleados e
    LEFT JOIN vacaciones_saldo vs 
        ON e.id = vs.empleado_id AND vs.anio = '$anio_actual'
    WHERE e.estado='activo'
    ORDER BY e.nombre
");

/* =====================================
   PROCESAR FORMULARIO
===================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $creada_por = $_SESSION['usuario'];

    // Obtener datos del empleado
    $qEmp = mysqli_query($conexion, "
        SELECT 
            e.fecha_ingreso,
            COALESCE(vs.dias_disfrutados,0) as dias_disfrutados
        FROM empleados e
        LEFT JOIN vacaciones_saldo vs 
            ON e.id = vs.empleado_id AND vs.anio = $anio_actual
        WHERE e.id = $empleado_id
    ");

    if (mysqli_num_rows($qEmp) == 0) {
        header("Location: vacaciones.php?error=empleado");
        exit();
    }

    $emp = mysqli_fetch_assoc($qEmp);

    $dias_acumulados = calcularDiasVacaciones($emp['fecha_ingreso'], 15);
    $dias_disfrutados = $emp['dias_disfrutados'];
    $dias_disponibles = $dias_acumulados - $dias_disfrutados;

    if ($dias_disponibles < 0) $dias_disponibles = 0;

    /* ===============================
       CALCULAR DÍAS SOLICITADOS
    =============================== */
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

    /* ===============================
       INSERTAR VACACIONES
    =============================== */
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

  /* ===============================
    ACTUALIZAR O CREAR SALDO
=============================== */
$nuevo_disfrutado = $dias_disfrutados + $dias_habiles;

$sqlCheck = "SELECT id FROM vacaciones_saldo WHERE empleado_id=$empleado_id AND anio=$anio_actual";
$checkSaldo = mysqli_query($conexion, $sqlCheck);

// VALIDACIÓN: Si la consulta falló, mostramos el error de SQL
if (!$checkSaldo) {
    die("Error en SQL: " . mysqli_error($conexion) . " | Consulta: " . $sqlCheck);
}

if (mysqli_num_rows($checkSaldo) > 0) {
    // UPDATE
    mysqli_query($conexion, "
        UPDATE vacaciones_saldo
        SET dias_disfrutados = $nuevo_disfrutado,
            dias_pendientes = ($dias_acumulados - $nuevo_disfrutado)
        WHERE empleado_id=$empleado_id AND anio=$anio_actual
    ");
} else {
    // INSERT
    $dias_pendientes = $dias_acumulados - $nuevo_disfrutado;
    mysqli_query($conexion, "
        INSERT INTO vacaciones_saldo 
        (empleado_id, anio, dias_acumulados, dias_disfrutados, dias_pendientes)
        VALUES (
            $empleado_id, $anio_actual, $dias_acumulados, $nuevo_disfrutado, $dias_pendientes
        )
    ");
}

    header("Location: vacaciones.php?ok=1");
    exit();
}

/* =====================================
   DATOS PARA VISTA
===================================== */
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
<title>Módulo de Vacaciones</title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f6f9;
    margin:0;
}

.container{
    width:95%;
    margin:auto;
    margin-top:20px;
}

h2{
    margin-bottom:10px;
}

.card{
    background:#fff;
    padding:20px;
    border-radius:8px;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
    margin-bottom:20px;
}

input, select, textarea{
    width:100%;
    padding:8px;
    margin-top:5px;
    margin-bottom:10px;
    border:1px solid #ccc;
    border-radius:4px;
}

button{
    background:#28a745;
    color:#fff;
    border:none;
    padding:10px 15px;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#218838;
}

table{
    width:100%;
    border-collapse:collapse;
}

table th, table td{
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

table th{
    background:#007bff;
    color:white;
}

.alert{
    padding:10px;
    border-radius:5px;
    margin-bottom:10px;
}

.success{
    background:#d4edda;
    color:#155724;
}

.error{
    background:#f8d7da;
    color:#721c24;
}
</style>
</head>

<body>

<div class="container">

<h2>Módulo de Vacaciones</h2>

<?php if(isset($_GET['ok'])): ?>
<div class="alert success">Vacaciones registradas correctamente.</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='exceso'): ?>
<div class="alert error">El empleado no tiene días suficientes disponibles.</div>
<?php endif; ?>

<?php if(isset($_GET['error']) && $_GET['error']=='saldo'): ?>
<div class="alert error">No existe saldo de vacaciones para este empleado.</div>
<?php endif; ?>

<!-- ================= FORMULARIO ================= -->

<div class="card">
<h3>Registrar Vacaciones</h3>

<form method="POST">

<label>Empleado</label>
<select name="empleado_id" required>
<option value="">Seleccione</option>
<?php while($emp = mysqli_fetch_assoc($empleados)): ?>
<option value="<?php echo $emp['id']; ?>">
<?php echo $emp['nombre']." ".$emp['apellido']; ?>
</option>
<?php endwhile; ?>
</select>

<label>Fecha Inicio</label>
<input type="date" name="fecha_inicio" required>

<label>Fecha Fin</label>
<input type="date" name="fecha_fin" required>

<label>Observaciones</label>
<textarea name="observaciones"></textarea>

<button type="submit">Registrar Vacaciones</button>

</form>
</div>

<!-- ================= SALDOS ================= -->

<div class="card">
<h3>Saldos de Vacaciones <?php echo date('Y'); ?></h3>

<table>
<tr>
<th>Cédula</th>
<th>Empleado</th>
<th>Días Acumulados</th>
<th>Días Disfrutados</th>
<th>Días Pendientes</th>
</tr>

<?php while($s = mysqli_fetch_assoc($saldos)): 
    // Calculamos los días que le corresponden por ley según su fecha de ingreso
    $acumulados_reales = calcularDiasVacaciones($s['fecha_ingreso']);
    
    // Los disfrutados vienen de la base de datos (ya tienen COALESCE en el SQL)
    $disfrutados = $s['dias_disfrutados'];
    
    // La diferencia es lo que le queda
    $pendientes = $acumulados_reales - $disfrutados;
?>
<tr>
<<<<<<< Updated upstream
    <td><?php echo $s['cedula']; ?></td>
    <td><?php echo $s['nombre']." ".$s['apellido']; ?></td>
    <td><?php echo $acumulados_reales; ?></td>
    <td><?php echo $disfrutados; ?></td>
    <td><?php echo ($pendientes < 0) ? 0 : $pendientes; ?></td>
=======
    <td><?= $s['nombre']." ".$s['apellido'] ?></td>
    <td><?= $s['dias_acumulados'] ?></td>
    <td><?= $s['dias_disfrutados'] ?></td>
    <td><strong><?= $saldo ?></strong></td>
>>>>>>> Stashed changes
</tr>
<?php endwhile; ?>

</table>
</div>

<!-- ================= HISTORIAL ================= -->

<div class="card">
<h3>Historial de Vacaciones</h3>

<table>
<tr>
<th>Empleado</th>
<th>Fecha Inicio</th>
<th>Fecha Fin</th>
<th>Días Solicitados</th>
<th>Días Hábiles</th>
<th>Días Feriados</th>
<th>Observaciones</th>
</tr>

<?php while($v = mysqli_fetch_assoc($vacaciones)): ?>
<tr>
<td><?php echo $v['nombre']." ".$v['apellido']; ?></td>
<td><?php echo date('d/m/Y', strtotime($v['fecha_inicio'])); ?></td>
<td><?php echo date('d/m/Y', strtotime($v['fecha_fin'])); ?></td>
<td><?php echo $v['dias_solicitados']; ?></td>
<td><?php echo $v['dias_habiles']; ?></td>
<td><?php echo $v['dias_feriados']; ?></td>
<td><?php echo $v['observaciones']; ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

</body>
</html>
