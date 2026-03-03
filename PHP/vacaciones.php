
<?php
session_start();
include("db.php");


if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}



/* ======================================================
   CALCULAR SALDO (MODELO VENEZUELA)
====================================================== */
function calcularSaldo($conexion, $empleado_id) {

    $q = mysqli_query($conexion, "SELECT fecha_ingreso FROM empleados WHERE id=$empleado_id");
    $emp = mysqli_fetch_assoc($q);
    if (!$emp) return 0;

    $fecha_ingreso = new DateTime($emp['fecha_ingreso']);
    $hoy = new DateTime();
    $antiguedad = $fecha_ingreso->diff($hoy)->y;

    if ($antiguedad < 1) return 0;

    $dias_acumulados = 15 + $antiguedad;

    $q2 = mysqli_query($conexion, "
        SELECT SUM(dias_habiles) as total 
        FROM vacaciones 
        WHERE empleado_id=$empleado_id 
        AND estado='aprobado'
    ");

    $usados = mysqli_fetch_assoc($q2)['total'] ?? 0;

    return max(0, $dias_acumulados - $usados);
}

/* ======================================================
   CALCULAR DIAS HABILES
====================================================== */
function calcularDiasHabiles($conexion, $inicio, $fin) {

    $fin->modify('+1 day');
    $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);
    $dias = 0;

    foreach ($periodo as $fecha) {

        $diaSemana = $fecha->format('N');
        $f = $fecha->format('Y-m-d');

        if ($diaSemana >= 6) continue;

        $q = mysqli_query($conexion, "SELECT id_feriado FROM feriados WHERE fecha='$f'");
        if (mysqli_num_rows($q) > 0) continue;

        $dias++;
    }

    return $dias;
}

/* ======================================================
   VALIDAR SOLAPAMIENTO
====================================================== */
function haySolapamiento($conexion, $empleado_id, $inicio, $fin) {

    $i = $inicio->format('Y-m-d');
    $f = $fin->format('Y-m-d');

    $q = mysqli_query($conexion, "
        SELECT id_vacacion FROM vacaciones
        WHERE empleado_id=$empleado_id
        AND estado IN ('pendiente','aprobado')
        AND (
            ('$i' BETWEEN fecha_inicio AND fecha_fin)
            OR ('$f' BETWEEN fecha_inicio AND fecha_fin)
            OR (fecha_inicio BETWEEN '$i' AND '$f')
        )
    ");

    return mysqli_num_rows($q) > 0;
}

/* ======================================================
   APROBAR / RECHAZAR
====================================================== */
if (isset($_GET['accion']) && isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $q = mysqli_query($conexion, "
        SELECT empleado_id, dias_habiles 
        FROM vacaciones 
        WHERE id_vacacion=$id
    ");

    $v = mysqli_fetch_assoc($q);

    if ($v) {

        if ($_GET['accion'] == "aprobar") {

            $saldo = calcularSaldo($conexion, $v['empleado_id']);

            if ($v['dias_habiles'] > $saldo) {
                header("Location: vacaciones.php?error=saldo_insuficiente");
                exit();
            }

            mysqli_query($conexion, "
                UPDATE vacaciones 
                SET estado='aprobado' 
                WHERE id_vacacion=$id
            ");
        }

        if ($_GET['accion'] == "rechazar") {
            mysqli_query($conexion, "
                UPDATE vacaciones 
                SET estado='rechazado' 
                WHERE id_vacacion=$id
            ");
        }
    }

    header("Location: vacaciones.php");
    exit();
}

/* ======================================================
   NUEVA SOLICITUD
====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);

    if (!$empleado_id || !$fecha_inicio || !$fecha_fin) {
        header("Location: vacaciones.php?error=campos");
        exit();
    }

    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $hoy = new DateTime();
    $hoy->setTime(0,0,0);

    if ($inicio > $fin) {
        header("Location: vacaciones.php?error=fechas");
        exit();
    }

    if ($inicio < $hoy) {
        header("Location: vacaciones.php?error=pasado");
        exit();
    }

    if (haySolapamiento($conexion, $empleado_id, $inicio, $fin)) {
        header("Location: vacaciones.php?error=solapamiento");
        exit();
    }

    $dias_habiles = calcularDiasHabiles($conexion, clone $inicio, clone $fin);
    $saldo = calcularSaldo($conexion, $empleado_id);

    if ($saldo <= 0) {
        header("Location: vacaciones.php?error=sin_derecho");
        exit();
    }

    if ($dias_habiles > $saldo) {
        header("Location: vacaciones.php?error=exceso");
        exit();
    }

    mysqli_query($conexion, "
        INSERT INTO vacaciones (
            empleado_id, fecha_inicio, fecha_fin,
            dias_solicitados, dias_habiles,
            observaciones, creada_por,
            estado
        ) VALUES (
            $empleado_id,
            '$fecha_inicio',
            '$fecha_fin',
            $dias_habiles,
            $dias_habiles,
            '$observaciones',
            '{$_SESSION['usuario']}',
            'pendiente'
        )
    ");

    header("Location: vacaciones.php?ok=1");
    exit();
}

/* ======================================================
   AJAX SALDO
====================================================== */
if (isset($_GET['saldo_empleado'])) {
    echo calcularSaldo($conexion, intval($_GET['saldo_empleado']));
    exit();
}

/* ======================================================
   DATOS
====================================================== */
$empleados = mysqli_query($conexion, "
    SELECT id,nombre,apellido 
    FROM empleados 
    WHERE estado='activo'
");

$vacaciones = mysqli_query($conexion, "
    SELECT v.*, e.nombre, e.apellido
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id=e.id
    ORDER BY v.id_vacacion DESC
");
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Vacaciones</title>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<h2>Gestión de Vacaciones</h2>

<form method="post">

<label>Empleado</label>
<select name="empleado_id" required onchange="obtenerSaldo(this.value)">
<option value="">Seleccionar...</option>
<?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
<option value="<?= $e['id'] ?>">
<?= $e['nombre']." ".$e['apellido'] ?>
</option>
<?php } ?>
</select>

<p><strong id="saldo">Saldo disponible: 0 días</strong></p>

<label>Fecha Inicio</label>
<input type="date" name="fecha_inicio" required>

<label>Fecha Fin</label>
<input type="date" name="fecha_fin" required>

<label>Observaciones</label>
<input type="text" name="observaciones">

<button type="submit">Solicitar Vacaciones</button>

</form>

<hr>

<h3>Historial</h3>

<table border="1" cellpadding="6">
<tr>
<th>Empleado</th>
<th>Inicio</th>
<th>Fin</th>
<th>Días</th>
<th>Estado</th>
<th>Acción</th>
</tr>

<?php while ($v = mysqli_fetch_assoc($vacaciones)) { ?>
<tr>
<td><?= $v['nombre']." ".$v['apellido'] ?></td>
<td><?= $v['fecha_inicio'] ?></td>
<td><?= $v['fecha_fin'] ?></td>
<td><?= $v['dias_habiles'] ?></td>
<td><?= $v['estado'] ?></td>
<td>
<?php if ($v['estado'] == 'pendiente') { ?>
<button onclick="confirmarAccion('aprobar', <?= $v['id_vacacion'] ?>)">Aprobar</button>
<button onclick="confirmarAccion('rechazar', <?= $v['id_vacacion'] ?>)">Rechazar</button>
<?php } else { echo "---"; } ?>
</td>
</tr>
<?php } ?>
</table>

<script>
function obtenerSaldo(id){
    if(!id){
        document.getElementById("saldo").innerHTML="Saldo disponible: 0 días";
        return;
    }
    fetch("vacaciones.php?saldo_empleado="+id)
    .then(res=>res.text())
    .then(data=>{
        document.getElementById("saldo").innerHTML="Saldo disponible: "+data+" días";
    });
}

function confirmarAccion(accion,id){
    Swal.fire({
        title:'¿Confirmar acción?',
        icon:'warning',
        showCancelButton:true
    }).then((result)=>{
        if(result.isConfirmed){
            window.location.href="vacaciones.php?accion="+accion+"&id="+id;
        }
    });
}
</script>

</body>
</html>