
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

    // Si no ha cumplido 1 año, no tiene derecho
    if ($antiguedad < 1) return 0;

    // 15 días el primer año
    // +1 día por cada año adicional
    $dias_acumulados = 15 + ($antiguedad - 1);

    // Tope máximo legal: 30 días
    $dias_acumulados = min(30, $dias_acumulados);

    // Días ya disfrutados
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
                SET estado='aprobado',
                  aprobado_por='{$_SESSION['usuario']}',
                    fecha_aprobacion=NOW(),
                  motivo_rechazo=NULL
                WHERE id_vacacion=$id
            ");
        }

        if ($_GET['accion'] == "rechazar") {

                $motivo = mysqli_real_escape_string($conexion, $_GET['motivo'] ?? '');

                mysqli_query($conexion, "
                    UPDATE vacaciones 
                    SET estado='rechazado',
                        motivo_rechazo='$motivo',
                        aprobado_por='{$_SESSION['usuario']}',
                        fecha_aprobacion=NOW()
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

/* filtro de estado */
$filtro_estado = $_GET['estado'] ?? '';

$where = "";
if(in_array($filtro_estado,['pendiente','aprobado','rechazado'])){
    $where = "WHERE v.estado='$filtro_estado'";
}
/* filtro de estado */

$vacaciones = mysqli_query($conexion, "
    SELECT v.*, e.nombre, e.apellido
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id=e.id
    $where
    ORDER BY v.id_vacacion DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Vacaciones - RRHH</title>
<link rel="stylesheet" href="../css/vacaciones.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>

    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php" class="active"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">

<header>
    <h2>Gestión de Vacaciones</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="contenido">

<h3><i class="ri-sun-line"></i> Nueva Solicitud de Vacaciones</h3>

<div class="form-container-compact">
<form method="post" class="form-grid">

<div class="form-group-compact">
<label>Empleado</label>
<select name="empleado_id" required onchange="obtenerSaldo(this.value)">
<option value="">Seleccionar...</option>
<?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
<option value="<?= $e['id'] ?>">
<?= $e['nombre']." ".$e['apellido'] ?>
</option>
<?php } ?>
</select>
</div>

<div class="form-group-compact">
<label>Fecha Inicio</label>
<input type="date" name="fecha_inicio" required>
</div>

<div class="form-group-compact">
<label>Fecha Fin</label>
<input type="date" name="fecha_fin" required>
</div>

<div class="form-group-compact">
<label>Observaciones</label>
<input type="text" name="observaciones" placeholder="Opcional...">
</div>

<button type="submit" class="btn-guardar-compact">
<i class="ri-save-3-line"></i> Solicitar
</button>

</form>

<p style="margin-top:10px;font-weight:600;color:#1f3a34;">
<strong id="saldo">Saldo disponible: 0 días</strong>
</p>

</div>

<h3><i class="ri-history-line"></i> Historial de Vacaciones</h3>

<div style="margin-bottom:15px;">
<a href="vacaciones.php" class="top-button">Todas</a>
<a href="vacaciones.php?estado=pendiente" class="top-button">Pendientes</a>
<a href="vacaciones.php?estado=aprobado" class="top-button">Aprobadas</a>
<a href="vacaciones.php?estado=rechazado" class="top-button">Rechazadas</a>

</div>

<table>
<tr>
<th>Empleado</th>
<th>Inicio</th>
<th>Fin</th>
<th>Días</th>
<th>Estado</th>
<th>Acción</th>
<th>Aprobado/Revisado por</th>
<th>Fecha</th>

</tr>

<?php while ($v = mysqli_fetch_assoc($vacaciones)) { 

$saldo_actual = calcularSaldo($conexion, $v['empleado_id']);
$puede_aprobar = $v['dias_habiles'] <= $saldo_actual;
?>

<tr>
<td><?= $v['nombre']." ".$v['apellido'] ?></td>
<td><?= $v['fecha_inicio'] ?></td>
<td><?= $v['fecha_fin'] ?></td>
<td><?= $v['dias_habiles'] ?></td>
<td><?= ucfirst($v['estado']) ?></td>
<td><?= $v['aprobado_por'] ?? '-' ?></td>
<td><?= $v['fecha_aprobacion'] ?? '-' ?></td>
<td>
    

<?php if ($v['estado'] == 'pendiente') { ?>

<?php if ($puede_aprobar) { ?>
<button class="top-button" onclick="confirmarAccion('aprobar', <?= $v['id_vacacion'] ?>)">
Aprobar
</button>
<?php } else { ?>
<button class="top-button" style="background:red;" disabled>
Sin saldo
</button>
<?php } ?>

<button class="top-button" onclick="confirmarAccion('rechazar', <?= $v['id_vacacion'] ?>)">
Rechazar
</button>


<?php } else { echo "---"; } ?>
<?php if($v['estado']=='aprobado'){ ?>
<a href="hola.php?id=<?= $v['id_vacacion'] ?>" 
   class="btn btn-success" target="_blank">
   📄 Constancia
</a>
<?php } ?>

</td>
</tr>

<?php } ?>

</table>

</div>
</div>

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

    if(accion === 'rechazar'){
        Swal.fire({
            title:'Motivo del rechazo',
            input:'textarea',
            inputPlaceholder:'Escriba el motivo...',
            showCancelButton:true,
            confirmButtonText:'Rechazar'
        }).then((result)=>{
            if(result.isConfirmed && result.value){
                window.location.href="vacaciones.php?accion="+accion+"&id="+id+"&motivo="+encodeURIComponent(result.value);
            }
        });
    } else {
        Swal.fire({
            title:'¿Confirmar aprobación?',
            icon:'warning',
            showCancelButton:true
        }).then((result)=>{
            if(result.isConfirmed){
                window.location.href="vacaciones.php?accion="+accion+"&id="+id;
            }
        });
    }
}
</script>

</body>
</html>