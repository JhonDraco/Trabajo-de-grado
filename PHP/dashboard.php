<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeReportes());
include("db.php");

/* ============================================================
   DATOS PARA GRÁFICOS
   ============================================================ */

// 1. Costo nómina por mes (últimos 6 meses)
$nomina_meses = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT DATE_FORMAT(n.fecha_inicio, '%b %Y') AS mes,
            DATE_FORMAT(n.fecha_inicio, '%Y-%m') AS mes_orden,
            COALESCE(SUM(dn.total_pagar), 0) AS total,
            COALESCE(SUM(dn.total_asignaciones), 0) AS asignaciones,
            COALESCE(SUM(dn.total_deducciones), 0) AS deducciones
     FROM nomina n
     LEFT JOIN detalle_nomina dn ON dn.id_nomina = n.id_nomina
     WHERE n.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY mes_orden, mes
     ORDER BY mes_orden ASC"
), MYSQLI_ASSOC);

// 2. Distribución de empleados por estado
$dist_estado = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT estado, COUNT(*) AS total FROM empleados GROUP BY estado"
), MYSQLI_ASSOC);

// 3. Top 5 asignaciones más costosas
$top_asignaciones = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT ta.nombre, COALESCE(SUM(da.monto), 0) AS total
     FROM detalle_asignacion da
     INNER JOIN tipo_asignacion ta ON ta.id_asignacion = da.id_asignacion
     GROUP BY ta.nombre
     ORDER BY total DESC
     LIMIT 5"
), MYSQLI_ASSOC);

// 4. Top 5 deducciones más aplicadas
$top_deducciones = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT td.nombre, COALESCE(SUM(dd.monto), 0) AS total
     FROM detalle_deduccion dd
     INNER JOIN tipo_deduccion td ON td.id_tipo = dd.id_tipo
     GROUP BY td.nombre
     ORDER BY total DESC
     LIMIT 5"
), MYSQLI_ASSOC);

// 5. Vacaciones por estado
$vac_estados = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT estado, COUNT(*) AS total FROM vacaciones GROUP BY estado"
), MYSQLI_ASSOC);

// 6. Nóminas por tipo
$nom_tipo = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT tipo, COUNT(*) AS total,
            COALESCE(SUM(dn.total_pagar),0) AS monto
     FROM nomina n
     LEFT JOIN detalle_nomina dn ON dn.id_nomina = n.id_nomina
     GROUP BY tipo"
), MYSQLI_ASSOC);

// KPIs resumen
$kpi_empleados = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM empleados WHERE estado='activo'"))['total'];
$kpi_nomina_mes = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COALESCE(SUM(dn.total_pagar),0) AS total
     FROM detalle_nomina dn
     JOIN nomina n ON n.id_nomina = dn.id_nomina
     WHERE MONTH(n.fecha_inicio)=MONTH(CURDATE()) AND YEAR(n.fecha_inicio)=YEAR(CURDATE())"))['total'];
$kpi_deduc_mes = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COALESCE(SUM(dn.total_deducciones),0) AS total
     FROM detalle_nomina dn
     JOIN nomina n ON n.id_nomina = dn.id_nomina
     WHERE MONTH(n.fecha_inicio)=MONTH(CURDATE()) AND YEAR(n.fecha_inicio)=YEAR(CURDATE())"))['total'];
$kpi_vac = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM vacaciones WHERE estado='pendiente'"))['total'];

// Preparar JSON para JS
$json_meses        = json_encode(array_column($nomina_meses, 'mes'));
$json_totales      = json_encode(array_column($nomina_meses, 'total'));
$json_asig_m       = json_encode(array_column($nomina_meses, 'asignaciones'));
$json_deduc_m      = json_encode(array_column($nomina_meses, 'deducciones'));
$json_estado_lbl   = json_encode(array_column($dist_estado, 'estado'));
$json_estado_data  = json_encode(array_column($dist_estado, 'total'));
$json_tasig_lbl    = json_encode(array_column($top_asignaciones, 'nombre'));
$json_tasig_data   = json_encode(array_column($top_asignaciones, 'total'));
$json_tdeduc_lbl   = json_encode(array_column($top_deducciones, 'nombre'));
$json_tdeduc_data  = json_encode(array_column($top_deducciones, 'total'));
$json_vac_lbl      = json_encode(array_column($vac_estados, 'estado'));
$json_vac_data     = json_encode(array_column($vac_estados, 'total'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Gerencial — KAO SHOP</title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:22px; }
    .kpi-card { background:white; border:1px solid #e2e2e2; border-radius:12px; padding:16px 18px; box-shadow:0 4px 14px rgba(0,0,0,0.05); display:flex; align-items:center; gap:12px; }
    .kpi-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .kpi-icon.v { background:#eaf3ef; color:#1f3a34; }
    .kpi-icon.a { background:#e8f0fe; color:#1a56db; }
    .kpi-icon.n { background:#fff4e5; color:#d97706; }
    .kpi-icon.r { background:#fef2f2; color:#dc2626; }
    .kpi-label { font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.06em; font-weight:600; }
    .kpi-valor { font-size:22px; font-weight:800; color:#1f3a34; line-height:1.1; }

    .chart-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    .chart-grid.uno { grid-template-columns:1fr; }
    .chart-box { background:white; border:1px solid #e2e2e2; border-radius:12px; padding:18px 20px; box-shadow:0 4px 14px rgba(0,0,0,0.05); }
    .chart-box h3 { font-size:12px; font-weight:700; color:#1f3a34; text-transform:uppercase; letter-spacing:.06em; margin-bottom:14px; padding-bottom:10px; border-bottom:2px solid #f0f0f0; display:flex; align-items:center; gap:6px; }
    .chart-wrap { position:relative; height:240px; }
    .chart-wrap.tall { height:300px; }

    .welcome-bar { background:linear-gradient(135deg,#1f3a34,#2b4a42); color:white; border-radius:12px; padding:16px 22px; display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
    .welcome-bar h2 { font-size:16px; font-weight:700; }
    .welcome-bar p  { font-size:12px; opacity:.7; margin-top:3px; }
    .btn-volver { background:#f6c90e; color:#1f3a34; padding:7px 14px; border-radius:7px; font-weight:700; font-size:12px; text-decoration:none; display:flex; align-items:center; gap:5px; }
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
    <a href="dashboard.php" class="active"><i class="ri-pie-chart-2-line"></i> Dashboard</a>
    <?php if (esAdmin()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2><i class="ri-pie-chart-2-line"></i> Dashboard Gerencial</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="administrador.php" class="top-button"><i class="ri-arrow-left-line"></i> Volver al Panel</a>
    </div>

    <div class="contenido">

        <!-- BIENVENIDA -->
        <div class="welcome-bar">
            <div>
                <h2>Dashboard Gerencial — KAO SHOP</h2>
                <p>Análisis de datos en tiempo real · <?= date('d \d\e F \d\e Y') ?></p>
            </div>
            <a href="administrador.php" class="btn-volver"><i class="ri-home-4-line"></i> Panel principal</a>
        </div>

        <!-- KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon v"><i class="ri-team-line"></i></div>
                <div><div class="kpi-label">Empleados activos</div><div class="kpi-valor"><?= $kpi_empleados ?></div></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon a"><i class="ri-money-dollar-circle-line"></i></div>
                <div><div class="kpi-label">Nómina <?= date('M Y') ?></div><div class="kpi-valor">Bs. <?= number_format($kpi_nomina_mes, 0, ',', '.') ?></div></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon n"><i class="ri-subtract-line"></i></div>
                <div><div class="kpi-label">Deducciones <?= date('M Y') ?></div><div class="kpi-valor">Bs. <?= number_format($kpi_deduc_mes, 0, ',', '.') ?></div></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon r"><i class="ri-sun-line"></i></div>
                <div><div class="kpi-label">Vacac. pendientes</div><div class="kpi-valor"><?= $kpi_vac ?></div></div>
            </div>
        </div>

        <!-- GRÁFICO 1: Evolución nómina (ancho completo) -->
        <div class="chart-grid uno">
            <div class="chart-box">
                <h3><i class="ri-line-chart-line"></i> Evolución del costo de nómina — últimos 6 meses</h3>
                <div class="chart-wrap tall">
                    <canvas id="chartNomina"></canvas>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS 2 y 3 -->
        <div class="chart-grid">
            <div class="chart-box">
                <h3><i class="ri-donut-chart-line"></i> Distribución de empleados por estado</h3>
                <div class="chart-wrap">
                    <canvas id="chartEstado"></canvas>
                </div>
            </div>
            <div class="chart-box">
                <h3><i class="ri-sun-line"></i> Solicitudes de vacaciones por estado</h3>
                <div class="chart-wrap">
                    <canvas id="chartVacaciones"></canvas>
                </div>
            </div>
        </div>

        <!-- GRÁFICOS 4 y 5 -->
        <div class="chart-grid">
            <div class="chart-box">
                <h3><i class="ri-add-circle-line"></i> Top 5 asignaciones por monto</h3>
                <div class="chart-wrap">
                    <canvas id="chartAsignaciones"></canvas>
                </div>
            </div>
            <div class="chart-box">
                <h3><i class="ri-subtract-line"></i> Top 5 deducciones por monto</h3>
                <div class="chart-wrap">
                    <canvas id="chartDeducciones"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
const VERDE  = '#1f3a34';
const VERDE2 = '#2b4a42';
const VERDE3 = '#3f6f61';
const AZUL   = '#1a56db';
const NARANJA= '#d97706';
const ROJO   = '#dc2626';
const AMARILLO='#f6c90e';

Chart.defaults.font.family = 'Inter, Arial, sans-serif';
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#666';

// 1. Evolución nómina — líneas
new Chart(document.getElementById('chartNomina'), {
    type: 'bar',
    data: {
        labels: <?= $json_meses ?>,
        datasets: [
            {
                label: 'Neto pagado',
                data: <?= $json_totales ?>,
                backgroundColor: 'rgba(31,58,52,0.85)',
                borderRadius: 6,
                order: 2
            },
            {
                label: 'Asignaciones',
                data: <?= $json_asig_m ?>,
                type: 'line',
                borderColor: AMARILLO,
                backgroundColor: 'rgba(246,201,14,0.1)',
                borderWidth: 2,
                pointBackgroundColor: AMARILLO,
                tension: 0.3,
                fill: true,
                order: 1
            },
            {
                label: 'Deducciones',
                data: <?= $json_deduc_m ?>,
                type: 'line',
                borderColor: ROJO,
                backgroundColor: 'rgba(220,38,38,0.05)',
                borderWidth: 2,
                pointBackgroundColor: ROJO,
                tension: 0.3,
                fill: false,
                order: 1
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { ticks: { callback: v => 'Bs. ' + v.toLocaleString('es-VE') }, grid: { color: '#f0f0f0' } },
            x: { grid: { display: false } }
        }
    }
});

// 2. Empleados por estado — dona
new Chart(document.getElementById('chartEstado'), {
    type: 'doughnut',
    data: {
        labels: <?= $json_estado_lbl ?>,
        datasets: [{
            data: <?= $json_estado_data ?>,
            backgroundColor: [VERDE, NARANJA, ROJO, AZUL],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' },
            tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' empleados' } }
        },
        cutout: '60%'
    }
});

// 3. Vacaciones por estado — dona
new Chart(document.getElementById('chartVacaciones'), {
    type: 'doughnut',
    data: {
        labels: <?= $json_vac_lbl ?>,
        datasets: [{
            data: <?= $json_vac_data ?>,
            backgroundColor: ['#d97706', '#1f3a34', '#dc2626', '#1a56db'],
            borderWidth: 2, borderColor: '#fff'
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        cutout: '60%'
    }
});

// 4. Top asignaciones — barras horizontales
new Chart(document.getElementById('chartAsignaciones'), {
    type: 'bar',
    data: {
        labels: <?= $json_tasig_lbl ?>,
        datasets: [{
            label: 'Total Bs.',
            data: <?= $json_tasig_data ?>,
            backgroundColor: [VERDE, VERDE2, VERDE3, '#4a8a78', '#5aaa94'],
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { callback: v => 'Bs. ' + v.toLocaleString('es-VE') }, grid: { color: '#f0f0f0' } },
            y: { grid: { display: false } }
        }
    }
});

// 5. Top deducciones — barras horizontales
new Chart(document.getElementById('chartDeducciones'), {
    type: 'bar',
    data: {
        labels: <?= $json_tdeduc_lbl ?>,
        datasets: [{
            label: 'Total Bs.',
            data: <?= $json_tdeduc_data ?>,
            backgroundColor: [ROJO, '#e05555', '#e87777', '#ef9999', '#f5bbbb'],
            borderRadius: 6
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { callback: v => 'Bs. ' + v.toLocaleString('es-VE') }, grid: { color: '#f0f0f0' } },
            y: { grid: { display: false } }
        }
    }
});
</script>

<?php mysqli_close($conexion); ?>
</body>
</html>