<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAdministrador());
include("db.php");

$mensaje = "";
$error   = "";
$anio_actual = (int)date('Y');

/* ============================================================
   FERIADOS VENEZOLANOS FIJOS — Art. 184 LOTTT
   Carga automática por año
   ============================================================ */
function feriadosVenezolanos(int $anio): array {
    return [
        ['Año Nuevo',                  "$anio-01-01", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Carnaval (Lunes)',            lunesCarnestolendas($anio), 'nacional', 1, 'Feriado móvil'],
        ['Carnaval (Martes)',           martesCarnestolendas($anio), 'nacional', 1, 'Feriado móvil'],
        ['Miércoles Santo',            miercolesSanto($anio), 'nacional', 1, 'Semana Santa — feriado móvil'],
        ['Jueves Santo',               juevesSanto($anio), 'nacional', 1, 'Semana Santa — feriado móvil'],
        ['Viernes Santo',              viernesSanto($anio), 'nacional', 1, 'Semana Santa — feriado móvil'],
        ['Declaración Independencia',  "$anio-04-19", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Día del Trabajador',         "$anio-05-01", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Batalla de Carabobo',        "$anio-06-24", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Día de la Independencia',    "$anio-07-05", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Natalicio Simón Bolívar',    "$anio-07-24", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Día de la Resistencia',      "$anio-10-12", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Nochebuena',                 "$anio-12-24", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Navidad',                    "$anio-12-25", 'nacional', 1, 'Art. 184 LOTTT'],
        ['Fin de Año',                 "$anio-12-31", 'nacional', 1, 'Art. 184 LOTTT'],
    ];
}

// Cálculo de Semana Santa (algoritmo de Gauss)
function pascua(int $anio): \DateTime {
    $a = $anio % 19;
    $b = intdiv($anio, 100);
    $c = $anio % 100;
    $d = intdiv($b, 4);
    $e = $b % 4;
    $f = intdiv($b + 8, 25);
    $g = intdiv($b - $f + 1, 3);
    $h = (19 * $a + $b - $d - $g + 15) % 30;
    $i = intdiv($c, 4);
    $k = $c % 4;
    $l = (32 + 2*$e + 2*$i - $h - $k) % 7;
    $m = intdiv($a + 11*$h + 22*$l, 451);
    $mes = intdiv($h + $l - 7*$m + 114, 31);
    $dia = (($h + $l - 7*$m + 114) % 31) + 1;
    return new \DateTime("$anio-$mes-$dia");
}
function viernesSanto(int $anio): string {
    $p = pascua($anio); $p->modify('-2 days'); return $p->format('Y-m-d');
}
function juevesSanto(int $anio): string {
    $p = pascua($anio); $p->modify('-3 days'); return $p->format('Y-m-d');
}
function miercolesSanto(int $anio): string {
    $p = pascua($anio); $p->modify('-4 days'); return $p->format('Y-m-d');
}
function lunesCarnestolendas(int $anio): string {
    $p = pascua($anio); $p->modify('-48 days'); return $p->format('Y-m-d');
}
function martesCarnestolendas(int $anio): string {
    $p = pascua($anio); $p->modify('-47 days'); return $p->format('Y-m-d');
}

/* ============================================================
   CARGA MASIVA AUTOMÁTICA
   ============================================================ */
if (isset($_POST['cargar_automatico'])) {
    $anio_cargar = (int)$_POST['anio_cargar'];
    $insertados  = 0;
    $omitidos    = 0;

    foreach (feriadosVenezolanos($anio_cargar) as $f) {
        [$nombre, $fecha, $tipo, $obligatorio, $desc] = $f;
        // Verificar si ya existe
        $existe = mysqli_fetch_assoc(mysqli_query($conexion,
            "SELECT id_feriado FROM feriados WHERE fecha = '$fecha'"
        ));
        if ($existe) { $omitidos++; continue; }

        $nombre_esc = mysqli_real_escape_string($conexion, $nombre);
        $desc_esc   = mysqli_real_escape_string($conexion, $desc);
        mysqli_query($conexion,
            "INSERT INTO feriados (nombre, fecha, tipo, obligatorio, descripcion)
             VALUES ('$nombre_esc', '$fecha', '$tipo', $obligatorio, '$desc_esc')"
        );
        $insertados++;
    }
    registrar_auditoria($conexion, 'CREAR', 'Feriados',
        "Carga automática $anio_cargar: $insertados insertados, $omitidos ya existían");
    $mensaje = "✅ Carga completada para $anio_cargar: <strong>$insertados feriados insertados</strong>" .
               ($omitidos ? ", $omitidos ya existían." : ".");
}

/* ============================================================
   REGISTRAR FERIADO MANUAL
   ============================================================ */
if (isset($_POST['guardar_manual'])) {
    $nombre      = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $fecha       = $_POST['fecha'];
    $tipo        = $_POST['tipo'];
    $obligatorio = isset($_POST['obligatorio']) ? 1 : 0;
    $descripcion = mysqli_real_escape_string($conexion, trim($_POST['descripcion']));

    // Verificar duplicado
    $dup = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT id_feriado FROM feriados WHERE fecha = '$fecha'"
    ));
    if ($dup) {
        $error = "⚠️ Ya existe un feriado registrado para la fecha $fecha.";
    } else {
        mysqli_query($conexion,
            "INSERT INTO feriados (nombre, fecha, tipo, obligatorio, descripcion)
             VALUES ('$nombre', '$fecha', '$tipo', $obligatorio, '$descripcion')"
        );
        registrar_auditoria($conexion, 'CREAR', 'Feriados', "Registró feriado manual: $nombre ($fecha)");
        $mensaje = "✅ Feriado <strong>$nombre</strong> registrado correctamente.";
    }
}

/* ============================================================
   ELIMINAR FERIADO
   ============================================================ */
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $f  = mysqli_fetch_assoc(mysqli_query($conexion,
        "SELECT nombre, fecha FROM feriados WHERE id_feriado = $id"));
    if ($f) {
        mysqli_query($conexion, "DELETE FROM feriados WHERE id_feriado = $id");
        registrar_auditoria($conexion, 'ELIMINAR', 'Feriados',
            "Eliminó feriado: {$f['nombre']} ({$f['fecha']})");
        $mensaje = "✅ Feriado eliminado.";
    }
}

/* ============================================================
   DETECCIÓN: Feriados dentro del período de nómina activa
   ============================================================ */
$ultima_nomina = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT id_nomina, fecha_inicio, fecha_fin, tipo, estado
     FROM nomina ORDER BY fecha_inicio DESC LIMIT 1"
));

$feriados_en_nomina = [];
if ($ultima_nomina) {
    $fi = $ultima_nomina['fecha_inicio'];
    $ff = $ultima_nomina['fecha_fin'];
    $feriados_en_nomina = mysqli_fetch_all(mysqli_query($conexion,
        "SELECT nombre, fecha, tipo, obligatorio
         FROM feriados
         WHERE fecha BETWEEN '$fi' AND '$ff'
         ORDER BY fecha ASC"
    ), MYSQLI_ASSOC);
}

/* ============================================================
   DATOS PARA LA VISTA
   ============================================================ */
$anio_filtro = (int)($_GET['anio'] ?? $anio_actual);
$feriados = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT * FROM feriados
     WHERE YEAR(fecha) = $anio_filtro
     ORDER BY fecha ASC"
), MYSQLI_ASSOC);

$total_feriados = count($feriados);
$feriados_nac   = count(array_filter($feriados, fn($f) => $f['tipo'] === 'nacional'));
$feriados_int   = count(array_filter($feriados, fn($f) => $f['tipo'] === 'interno'));

// Años disponibles para filtro
$anios_bd = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT DISTINCT YEAR(fecha) as anio FROM feriados ORDER BY anio DESC"
), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gestión de Feriados — KAO SHOP</title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
    .kpi-card { background:white; border:1px solid #e2e2e2; border-radius:12px; padding:16px 18px;
                box-shadow:0 4px 14px rgba(0,0,0,0.05); display:flex; align-items:center; gap:12px; }
    .kpi-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center;
                justify-content:center; font-size:20px; flex-shrink:0; }
    .kpi-icon.v { background:#eaf3ef; color:#1f3a34; }
    .kpi-icon.a { background:#e8f0fe; color:#1a56db; }
    .kpi-icon.n { background:#fff4e5; color:#d97706; }
    .kpi-icon.r { background:#fef2f2; color:#dc2626; }
    .kpi-label { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.06em; font-weight:600; }
    .kpi-valor { font-size:22px; font-weight:800; color:#1f3a34; line-height:1.1; }

    .grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    .seccion { background:white; border:1px solid #e2e2e2; border-radius:12px;
               padding:20px 22px; box-shadow:0 4px 14px rgba(0,0,0,0.05); margin-bottom:18px; }
    .seccion h3 { font-size:13px; font-weight:700; color:#1f3a34; text-transform:uppercase;
                  letter-spacing:.06em; margin-bottom:14px; padding-bottom:10px;
                  border-bottom:2px solid #f0f0f0; display:flex; align-items:center; gap:8px; }

    .liq-label { font-size:11px; font-weight:600; color:#777; text-transform:uppercase;
                 letter-spacing:.05em; display:block; margin-bottom:4px; margin-top:10px; }
    .liq-input { width:100%; padding:8px 12px; border:2px solid #e2e2e2; border-radius:6px;
                 font-size:13px; outline:none; transition:.2s; box-sizing:border-box; }
    .liq-input:focus { border-color:#2b4a42; }
    .liq-check { display:flex; align-items:center; gap:8px; margin-top:10px;
                 font-size:13px; font-weight:600; color:#1f3a34; cursor:pointer; }
    .liq-check input { width:16px; height:16px; accent-color:#1f3a34; }
    .liq-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:10px; }

    .btn-verde { background:#1f3a34; color:white; border:none; border-radius:7px;
                 padding:9px 18px; font-size:13px; font-weight:700; cursor:pointer;
                 display:inline-flex; align-items:center; gap:6px; margin-top:14px;
                 transition:.2s; text-decoration:none; }
    .btn-verde:hover { background:#2b4a42; }
    .btn-azul  { background:#1a56db; color:white; border:none; border-radius:7px;
                 padding:9px 18px; font-size:13px; font-weight:700; cursor:pointer;
                 display:inline-flex; align-items:center; gap:6px; margin-top:14px; transition:.2s; }
    .btn-azul:hover { background:#1448c0; }
    .btn-rojo  { background:#dc2626; color:white; border:none; border-radius:6px;
                 padding:4px 12px; font-size:11px; font-weight:600; cursor:pointer;
                 text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
    .btn-rojo:hover { background:#b91c1c; }

    table { width:100%; border-collapse:collapse; font-size:13px; }
    th { background:#1f3a34; color:white; padding:10px 12px; text-align:left;
         font-size:11px; text-transform:uppercase; letter-spacing:.05em; }
    td { padding:9px 12px; border-bottom:1px solid #f0f0f0; vertical-align:middle; }
    tr:hover td { background:#fafafa; }
    tr:last-child td { border-bottom:none; }

    .badge { display:inline-block; padding:2px 9px; border-radius:20px;
             font-size:11px; font-weight:600; }
    .badge.nacional  { background:#dae8fc; color:#1a56db; }
    .badge.regional  { background:#d5e8d4; color:#155724; }
    .badge.interno   { background:#fff4e5; color:#d97706; }
    .badge.si        { background:#d4edda; color:#155724; }
    .badge.no        { background:#f8d7da; color:#721c24; }

    .alerta-nomina { background:#fff8e1; border:1px solid #ffe082; border-radius:10px;
                     padding:14px 18px; margin-bottom:18px; }
    .alerta-nomina .titulo { font-size:13px; font-weight:700; color:#7a5c00;
                              display:flex; align-items:center; gap:8px; margin-bottom:10px; }
    .alerta-feriado-item { display:flex; justify-content:space-between; align-items:center;
                           padding:7px 0; border-bottom:1px solid #f0e8c0; font-size:13px; }
    .alerta-feriado-item:last-child { border-bottom:none; }
    .alerta-ok { background:#d4edda; border:1px solid #c3e6cb; border-radius:10px;
                 padding:12px 16px; margin-bottom:18px; font-size:13px; color:#155724;
                 font-weight:600; display:flex; align-items:center; gap:8px; }

    .carga-auto { background:#eaf3ef; border:1px solid #b8dfd0; border-radius:10px;
                  padding:16px 18px; }
    .carga-auto h4 { font-size:13px; font-weight:700; color:#1f3a34; margin-bottom:10px;
                     display:flex; align-items:center; gap:8px; }

    .filtro-anio { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px; }
    .filtro-anio a { padding:5px 14px; border-radius:20px; font-size:12px; font-weight:600;
                     text-decoration:none; border:2px solid #e2e2e2; color:#666; transition:.15s; }
    .filtro-anio a.activo { background:#1f3a34; color:white; border-color:#1f3a34; }
    .filtro-anio a:hover:not(.activo) { border-color:#2b4a42; color:#1f3a34; }

    .msg-ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb;
               border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }
    .msg-err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;
               border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }

    .sin-datos { color:#aaa; font-size:13px; padding:12px 0;
                 display:flex; align-items:center; gap:6px; }
    .nota-legal { background:#e8f4f8; border:1px solid #b8ddf0; border-radius:8px;
                  padding:10px 14px; font-size:12px; color:#0c5460; margin-top:10px;
                  display:flex; gap:8px; }
</style>
</head>
<body>

<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2><i class="ri-calendar-event-line"></i> Gestión de Feriados</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="vacaciones.php" class="top-button"><i class="ri-sun-line"></i> Vacaciones</a>
        <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
        <a href="administrador.php" class="top-button"><i class="ri-home-4-line"></i> Panel</a>
    </div>

    <div class="contenido">

        <?php if ($mensaje): ?><div class="msg-ok"><?= $mensaje ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="msg-err"><?= $error ?></div><?php endif; ?>

        <!-- ── ALERTA: Feriados en período de nómina ─────── -->
        <?php if ($ultima_nomina): ?>
            <?php if (!empty($feriados_en_nomina)): ?>
            <div class="alerta-nomina">
                <div class="titulo">
                    <i class="ri-alarm-warning-line"></i>
                    ⚠️ <?= count($feriados_en_nomina) ?> feriado<?= count($feriados_en_nomina) > 1 ? 's' : '' ?>
                    detectado<?= count($feriados_en_nomina) > 1 ? 's' : '' ?> en la última nómina
                    (<?= $ultima_nomina['fecha_inicio'] ?> al <?= $ultima_nomina['fecha_fin'] ?> —
                    <?= ucfirst($ultima_nomina['tipo']) ?>)
                </div>
                <?php foreach ($feriados_en_nomina as $fn): ?>
                <div class="alerta-feriado-item">
                    <div>
                        <strong><?= htmlspecialchars($fn['nombre']) ?></strong>
                        <span style="color:#888; font-size:11px; margin-left:8px;">
                            <?= date('d/m/Y', strtotime($fn['fecha'])) ?> —
                            <?= ucfirst($fn['tipo']) ?>
                        </span>
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <span class="badge <?= $fn['obligatorio'] ? 'si' : 'no' ?>">
                            <?= $fn['obligatorio'] ? 'Obligatorio' : 'No obligatorio' ?>
                        </span>
                        <?php if ($fn['obligatorio']): ?>
                        <span style="font-size:11px; color:#7a5c00; font-weight:600;">
                            → Recargo del 50% si se trabajó (Art. 120 LOTTT)
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alerta-ok">
                <i class="ri-checkbox-circle-line"></i>
                Sin feriados en el período de la última nómina
                (<?= $ultima_nomina['fecha_inicio'] ?> al <?= $ultima_nomina['fecha_fin'] ?>).
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- ── KPIs ───────────────────────────────────────── -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon v"><i class="ri-calendar-event-line"></i></div>
                <div>
                    <div class="kpi-label">Feriados <?= $anio_filtro ?></div>
                    <div class="kpi-valor"><?= $total_feriados ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon a"><i class="ri-flag-line"></i></div>
                <div>
                    <div class="kpi-label">Nacionales</div>
                    <div class="kpi-valor"><?= $feriados_nac ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon n"><i class="ri-building-line"></i></div>
                <div>
                    <div class="kpi-label">Internos empresa</div>
                    <div class="kpi-valor"><?= $feriados_int ?></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon r"><i class="ri-alarm-warning-line"></i></div>
                <div>
                    <div class="kpi-label">En última nómina</div>
                    <div class="kpi-valor"><?= count($feriados_en_nomina) ?></div>
                </div>
            </div>
        </div>

        <!-- ── FORMULARIOS ────────────────────────────────── -->
        <div class="grid-2">

            <!-- Carga automática -->
            <div class="seccion">
                <h3><i class="ri-magic-line"></i> Carga Automática — Feriados Venezuela</h3>
                <div class="carga-auto">
                    <h4><i class="ri-government-line"></i> Feriados según Art. 184 LOTTT</h4>
                    <p style="font-size:12px; color:#555; margin-bottom:10px;">
                        Inserta automáticamente los 15 feriados nacionales del año seleccionado,
                        incluyendo los móviles de Semana Santa y Carnaval calculados con el
                        algoritmo de Gauss. No duplica fechas ya existentes.
                    </p>
                    <form method="POST">
                        <input type="hidden" name="cargar_automatico" value="1">
                        <label class="liq-label">Año a cargar</label>
                        <select name="anio_cargar" class="liq-input" style="width:auto;">
                            <?php for ($y = $anio_actual - 1; $y <= $anio_actual + 2; $y++): ?>
                            <option value="<?= $y ?>" <?= $y == $anio_actual ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit" class="btn-azul">
                            <i class="ri-download-cloud-line"></i> Cargar feriados del año
                        </button>
                    </form>
                </div>
                <div class="nota-legal" style="margin-top:14px;">
                    <i class="ri-information-line"></i>
                    <span>El <strong>Art. 120 LOTTT</strong> establece que si un trabajador labora en día feriado obligatorio,
                    tiene derecho a un recargo del <strong>50%</strong> sobre el salario normal de ese día, adicional al pago ordinario.</span>
                </div>
            </div>

            <!-- Registro manual -->
            <div class="seccion">
                <h3><i class="ri-add-circle-line"></i> Registrar Feriado Manual</h3>
                <form method="POST">
                    <input type="hidden" name="guardar_manual" value="1">
                    <label class="liq-label">Nombre del feriado</label>
                    <input type="text" name="nombre" class="liq-input" placeholder="Ej: Aniversario empresa" required>
                    <div class="liq-grid-2">
                        <div>
                            <label class="liq-label">Fecha</label>
                            <input type="date" name="fecha" class="liq-input" required>
                        </div>
                        <div>
                            <label class="liq-label">Tipo</label>
                            <select name="tipo" class="liq-input">
                                <option value="nacional">Nacional</option>
                                <option value="regional">Regional</option>
                                <option value="interno" selected>Interno empresa</option>
                            </select>
                        </div>
                    </div>
                    <label class="liq-label">Descripción</label>
                    <input type="text" name="descripcion" class="liq-input" placeholder="Opcional...">
                    <label class="liq-check">
                        <input type="checkbox" name="obligatorio" checked>
                        Feriado obligatorio (afecta cálculo de nómina)
                    </label>
                    <button type="submit" class="btn-verde">
                        <i class="ri-save-3-line"></i> Guardar feriado
                    </button>
                </form>
            </div>

        </div>

        <!-- ── LISTADO ─────────────────────────────────────── -->
        <div class="seccion">
            <h3><i class="ri-list-check"></i> Feriados registrados

                <span style="margin-left:auto; font-weight:400; font-size:11px; color:#888;">
                    Filtrar por año:
                </span>
            </h3>

            <div class="filtro-anio">
                <?php foreach ($anios_bd as $a): ?>
                <a href="?anio=<?= $a['anio'] ?>"
                   class="<?= $a['anio'] == $anio_filtro ? 'activo' : '' ?>">
                    <?= $a['anio'] ?>
                </a>
                <?php endforeach; ?>
                <a href="?anio=<?= $anio_actual ?>" style="color:#1f3a34; font-weight:700;">
                    Año actual
                </a>
            </div>

            <?php if (!empty($feriados)): ?>
            <table>
                <tr>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Obligatorio</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
                <?php foreach ($feriados as $f): ?>
                <tr>
                    <td style="font-weight:600; white-space:nowrap;">
                        <?= date('d/m/Y', strtotime($f['fecha'])) ?>
                        <div style="font-size:10px; color:#aaa;">
                            <?= date('l', strtotime($f['fecha'])) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($f['nombre']) ?></td>
                    <td><span class="badge <?= $f['tipo'] ?>"><?= ucfirst($f['tipo']) ?></span></td>
                    <td><span class="badge <?= $f['obligatorio'] ? 'si' : 'no' ?>">
                        <?= $f['obligatorio'] ? 'Sí' : 'No' ?>
                    </span></td>
                    <td style="font-size:12px; color:#666;"><?= htmlspecialchars($f['descripcion'] ?? '') ?></td>
                    <td style="white-space:nowrap;">
                        <a href="editar_feriado.php?id=<?= $f['id_feriado'] ?>" class="btn-verde"
                           style="padding:4px 10px; font-size:11px; margin-top:0;">
                            <i class="ri-edit-2-line"></i> Editar
                        </a>
                        <a href="?eliminar=<?= $f['id_feriado'] ?>&anio=<?= $anio_filtro ?>"
                           class="btn-rojo"
                           onclick="return confirm('¿Eliminar el feriado <?= htmlspecialchars($f['nombre']) ?>?')">
                            <i class="ri-delete-bin-6-line"></i> Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p class="sin-datos">
                <i class="ri-calendar-close-line"></i>
                No hay feriados registrados para <?= $anio_filtro ?>.
                Usa la carga automática para insertar los feriados nacionales.
            </p>
            <?php endif; ?>
        </div>

    </div><!-- fin contenido -->
</div><!-- fin main -->

<?php mysqli_close($conexion); ?>
</body>
</html>