<?php

include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeVerHorasExtra());

include("db.php");

/* ==========================================================
   Recargos según Art. 118 LOTTT (simplificado para el sistema):
   - Diurna: +50% sobre el valor hora normal
   - Nocturna: +100% sobre el valor hora normal
   Valor hora normal = salario_base / 30 / 8
   Tope legal informativo: 2h/día, 10h/semana (no se bloquea,
   solo se advierte en pantalla).
========================================================== */
define('RECARGO_DIURNO', 1.5);
define('RECARGO_NOCTURNO', 2.0);
define('TOPE_HORAS_DIA', 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    bloquearSiNo(puedeRegistrarHorasExtra());

    $empleado_id = intval($_POST['empleado_id']);
    $fecha       = $_POST['fecha'];
    $horas       = floatval($_POST['horas']);
    $tipo        = ($_POST['tipo'] === 'nocturna') ? 'nocturna' : 'diurna';
    $motivo      = mysqli_real_escape_string($conexion, $_POST['motivo']);

    $q = mysqli_query($conexion, "SELECT salario_base FROM empleados WHERE id = $empleado_id");
    $emp = mysqli_fetch_assoc($q);

    if ($emp && $horas > 0) {
        $valor_hora_normal = $emp['salario_base'] / 30 / 8;
        $recargo = ($tipo === 'nocturna') ? RECARGO_NOCTURNO : RECARGO_DIURNO;
        $valor_hora = round($valor_hora_normal * $recargo, 2);
        $monto = round($valor_hora * $horas, 2);

        mysqli_query($conexion, "
            INSERT INTO horas_extras
                (empleado_id, fecha, horas, tipo, valor_hora, monto, motivo, registrado_por)
            VALUES (
                $empleado_id, '$fecha', $horas, '$tipo', $valor_hora, $monto,
                '$motivo', '{$_SESSION['usuario']}'
            )
        ");

        registrar_auditoria($conexion, 'CREAR', 'Horas Extra',
            "Registró $horas h ($tipo) para empleado ID $empleado_id — Bs. $monto");
    }

    header("Location: horas_extras.php");
    exit();
}

$empleados = mysqli_query($conexion, "SELECT id, nombre, apellido, salario_base FROM empleados WHERE estado='activo' ORDER BY nombre");

$registros = mysqli_query($conexion, "
    SELECT h.*, e.nombre, e.apellido
    FROM horas_extras h
    JOIN empleados e ON e.id = h.empleado_id
    ORDER BY h.fecha DESC, h.creada_en DESC
    LIMIT 200
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Horas Extra</title>
<link rel="stylesheet" href="../css/asignaciones.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <?php if (puedeGenerarNomina()): ?>
    <a href="generar_nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <?php elseif (puedeVerNomina()): ?>
    <a href="ver_nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <?php endif; ?>
    <?php if (puedeVerLiquidacion()): ?>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <?php endif; ?>
    <?php if (puedeVerVacaciones()): ?>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <?php endif; ?>
    <a href="horas_extras.php" class="active"><i class="ri-time-line"></i> Horas Extra</a>
    <?php if (puedeListarEmpleados()): ?>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <?php endif; ?>
    <?php if (puedeVerUsuarios()): ?>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <?php endif; ?>
    <?php if (puedeReportes()): ?>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <?php endif; ?>
    <?php if (puedeVerBitacora()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
<header>
    <h2>Registro de Horas Extra</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="contenido">

<?php if (puedeRegistrarHorasExtra()): ?>
<h3><i class="ri-time-line"></i> Nuevo registro</h3>

<div class="form-container-compact">
<form method="post" class="form-grid" id="form-horas-extra">

    <div class="form-group-compact">
        <label>Empleado</label>
        <select name="empleado_id" id="empleado_id" required>
            <option value="">Seleccione</option>
            <?php while ($e = mysqli_fetch_assoc($empleados)) { ?>
            <option value="<?= $e['id'] ?>" data-salario="<?= $e['salario_base'] ?>">
                <?= htmlspecialchars($e['nombre']." ".$e['apellido']) ?>
            </option>
            <?php } ?>
        </select>
    </div>

    <div class="form-group-compact">
        <label>Fecha</label>
        <input type="date" name="fecha" id="fecha" required max="<?= date('Y-m-d') ?>">
    </div>

    <div class="form-group-compact">
        <label>Horas</label>
        <input type="number" step="0.5" min="0.5" name="horas" id="horas" required>
        <small id="aviso-tope" style="color:#dc3545; display:none; font-weight:600;">
            ⚠ Supera el tope legal de <?= TOPE_HORAS_DIA ?>h/día (Art. 118 LOTTT) — se permite igual, pero verifícalo.
        </small>
    </div>

    <div class="form-group-compact">
        <label>Tipo</label>
        <select name="tipo" id="tipo">
            <option value="diurna">Diurna (+50%)</option>
            <option value="nocturna">Nocturna (+100%)</option>
        </select>
    </div>

    <div class="form-group-compact">
        <label>Motivo</label>
        <input type="text" name="motivo" placeholder="Ej: Cierre de inventario">
    </div>

    <button type="submit" class="btn-guardar-compact">
        <i class="ri-save-3-line"></i> Guardar
    </button>

</form>
<p style="margin-top:10px;font-weight:600;color:#1f3a34;">
    <span id="preview-monto">Monto estimado: Bs. 0.00</span>
</p>
</div>
<?php endif; ?>

<h3><i class="ri-list-ordered"></i> Registros recientes</h3>
<table border="1" cellpadding="5">
<tr>
    <th>Empleado</th>
    <th>Fecha</th>
    <th>Horas</th>
    <th>Tipo</th>
    <th>Valor Hora</th>
    <th>Monto</th>
    <th>Motivo</th>
    <th>Registrado por</th>
    <th>Acción</th>
</tr>
<?php while ($r = mysqli_fetch_assoc($registros)) {
    $excede = $r['horas'] > TOPE_HORAS_DIA;
?>
<tr>
    <td><?= htmlspecialchars($r['nombre']." ".$r['apellido']) ?></td>
    <td><?= $r['fecha'] ?></td>
    <td style="<?= $excede ? 'color:#dc3545;font-weight:700;' : '' ?>">
        <?= $r['horas'] ?><?= $excede ? ' ⚠' : '' ?>
    </td>
    <td><?= ucfirst($r['tipo']) ?></td>
    <td><?= number_format($r['valor_hora'],2) ?></td>
    <td><?= number_format($r['monto'],2) ?></td>
    <td><?= htmlspecialchars($r['motivo']) ?></td>
    <td><?= htmlspecialchars($r['registrado_por']) ?></td>
    <td>
        <?php if (puedeEliminarHorasExtra()): ?>
        <a href="eliminar_hora_extra.php?id=<?= $r['id_hora_extra'] ?>"
           onclick="return confirm('¿Eliminar este registro de horas extra?')"
           style="background:#dc3545;color:white;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;">
           Eliminar
        </a>
        <?php endif; ?>
    </td>
</tr>
<?php } ?>
</table>

</div>
</div>

<script>
const selEmpleado = document.getElementById('empleado_id');
const inputHoras  = document.getElementById('horas');
const selTipo     = document.getElementById('tipo');
const avisoTope   = document.getElementById('aviso-tope');
const previewMonto= document.getElementById('preview-monto');

function actualizarPreview(){
    if (!selEmpleado) return;
    const opt = selEmpleado.options[selEmpleado.selectedIndex];
    const salario = parseFloat(opt?.dataset.salario || 0);
    const horas = parseFloat(inputHoras.value || 0);
    const recargo = selTipo.value === 'nocturna' ? 2.0 : 1.5;

    avisoTope.style.display = horas > <?= TOPE_HORAS_DIA ?> ? 'block' : 'none';

    if (salario > 0 && horas > 0) {
        const valorHoraNormal = salario / 30 / 8;
        const monto = valorHoraNormal * recargo * horas;
        previewMonto.textContent = 'Monto estimado: Bs. ' + monto.toFixed(2);
    } else {
        previewMonto.textContent = 'Monto estimado: Bs. 0.00';
    }
}

if (selEmpleado) {
    selEmpleado.addEventListener('change', actualizarPreview);
    inputHoras.addEventListener('input', actualizarPreview);
    selTipo.addEventListener('change', actualizarPreview);
}
</script>

</body>
</html>
