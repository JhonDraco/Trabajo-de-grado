<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAdministrador());
include("db.php");

/* ============================================================
   CONSULTAS DE AGREGACIÓN — KPIs en tiempo real
   ============================================================ */

// 1. Empleados activos
$empleados_activos = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM empleados WHERE estado = 'activo'"
))['total'];

// 2. Costo nómina mes actual (suma de total_pagar de detalle_nomina en nóminas del mes)
$nomina_mes = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COALESCE(SUM(dn.total_pagar), 0) AS total
     FROM detalle_nomina dn
     INNER JOIN nomina n ON n.id_nomina = dn.id_nomina
     WHERE MONTH(n.fecha_inicio) = MONTH(CURDATE())
     AND YEAR(n.fecha_inicio)  = YEAR(CURDATE())"
))['total'];

// 3. Total deducciones mes actual
$deducciones_mes = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COALESCE(SUM(dn.total_deducciones), 0) AS total
     FROM detalle_nomina dn
     INNER JOIN nomina n ON n.id_nomina = dn.id_nomina
     WHERE MONTH(n.fecha_inicio) = MONTH(CURDATE())
     AND YEAR(n.fecha_inicio)  = YEAR(CURDATE())"
))['total'];

// 4. Vacaciones pendientes de aprobar
$vac_pendientes = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) AS total FROM vacaciones WHERE estado = 'pendiente'"
))['total'];

// 5. Empleados con vacaciones vencidas
//    (antigüedad >= 1 año y sin solicitud activa o aprobada este año)
$vac_vencidas = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT e.id, e.nombre, e.apellido, e.fecha_ingreso,
            TIMESTAMPDIFF(YEAR, e.fecha_ingreso, CURDATE()) AS anos
     FROM empleados e
     WHERE e.estado = 'activo'
       AND TIMESTAMPDIFF(YEAR, e.fecha_ingreso, CURDATE()) >= 1
       AND e.id NOT IN (
           SELECT empleado_id FROM vacaciones
           WHERE estado IN ('pendiente','aprobada')
           AND YEAR(fecha_inicio) = YEAR(CURDATE())
       )
     ORDER BY anos DESC
     LIMIT 8"
), MYSQLI_ASSOC);

// 6. Últimas 5 nóminas generadas
$ultimas_nominas = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT n.id_nomina, n.tipo, n.fecha_inicio, n.fecha_fin, n.estado,
            COUNT(dn.id_detalle) AS empleados,
            COALESCE(SUM(dn.total_pagar),0) AS total
     FROM nomina n
     LEFT JOIN detalle_nomina dn ON dn.id_nomina = n.id_nomina
     GROUP BY n.id_nomina
     ORDER BY n.fecha_inicio DESC
     LIMIT 5"
), MYSQLI_ASSOC);

// 7. Últimas acciones de bitácora (si existe la tabla)
$hay_auditoria = mysqli_query($conexion, "SHOW TABLES LIKE 'auditoria'");
$ultimas_acciones = [];
if (mysqli_num_rows($hay_auditoria) > 0) {
    $ultimas_acciones = mysqli_fetch_all(mysqli_query($conexion,
        "SELECT usuario, accion, modulo, descripcion, fecha
         FROM auditoria ORDER BY fecha DESC LIMIT 6"
    ), MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Administración — KAO SHOP</title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    /* ---- KPI CARDS ---- */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: white;
        border: 1px solid #e2e2e2;
        border-radius: 12px;
        padding: 18px 20px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        display: flex;
        align-items: center;
        gap: 14px;
        text-decoration: none;
        transition: box-shadow 0.2s, transform 0.15s;
    }
    .kpi-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.1); transform: translateY(-2px); }
    .kpi-icon {
        width: 48px; height: 48px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; flex-shrink: 0;
    }
    .kpi-icon.verde   { background: #eaf3ef; color: #1f3a34; }
    .kpi-icon.azul    { background: #e8f0fe; color: #1a56db; }
    .kpi-icon.naranja { background: #fff4e5; color: #d97706; }
    .kpi-icon.rojo    { background: #fef2f2; color: #dc2626; }
    .kpi-label { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; }
    .kpi-valor { font-size: 26px; font-weight: 800; color: #1f3a34; line-height: 1.1; margin-top: 2px; }
    .kpi-sub   { font-size: 11px; color: #aaa; margin-top: 3px; }

    /* ---- GRID 2 COLUMNAS ---- */
    .dash-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .dash-grid.tres { grid-template-columns: 1fr 1fr 1fr; }

    /* ---- SECCIONES ---- */
    .dash-section {
        background: white;
        border: 1px solid #e2e2e2;
        border-radius: 12px;
        padding: 20px 22px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.05);
    }
    .dash-section h3 {
        font-size: 13px; font-weight: 700;
        color: #1f3a34; text-transform: uppercase;
        letter-spacing: 0.06em; margin-bottom: 14px;
        padding-bottom: 10px; border-bottom: 2px solid #f0f0f0;
        display: flex; align-items: center; gap: 8px;
    }

    /* ---- TABLA INTERNA ---- */
    .dash-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .dash-table th { font-size: 10px; font-weight: 700; color: #aaa; text-transform: uppercase; padding: 0 8px 8px; text-align: left; }
    .dash-table td { padding: 8px; border-bottom: 1px solid #f5f5f5; vertical-align: middle; }
    .dash-table tr:last-child td { border-bottom: none; }
    .dash-table tr:hover td { background: #fafafa; }

    /* ---- BADGES ---- */
    .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge.pagada   { background:#d4edda; color:#155724; }
    .badge.generada { background:#d1ecf1; color:#0c5460; }
    .badge.pendiente{ background:#fff3cd; color:#856404; }
    .badge.mensual  { background:#e8f0fe; color:#1a56db; }
    .badge.semanal  { background:#f3e8ff; color:#6b21a8; }

    /* ---- VACACIONES VENCIDAS ---- */
    .vac-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0; border-bottom: 1px solid #f5f5f5; font-size: 13px;
    }
    .vac-item:last-child { border-bottom: none; }
    .vac-nombre { font-weight: 600; color: #1f3a34; }
    .vac-anos   { font-size: 11px; color: #888; margin-top: 2px; }
    .vac-badge  { background: #fef2f2; color: #dc2626; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

    /* ---- BITÁCORA ---- */
    .log-item {
        display: flex; gap: 10px; align-items: flex-start;
        padding: 8px 0; border-bottom: 1px solid #f5f5f5; font-size: 12px;
    }
    .log-item:last-child { border-bottom: none; }
    .log-accion { flex-shrink: 0; }
    .log-desc   { color: #555; line-height: 1.4; flex: 1; }
    .log-time   { color: #bbb; font-size: 11px; white-space: nowrap; }
    .ac-sm { display: inline-block; padding: 2px 7px; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .ac-CREAR    { background:#d4edda; color:#155724; }
    .ac-EDITAR   { background:#d1ecf1; color:#0c5460; }
    .ac-ELIMINAR { background:#f8d7da; color:#721c24; }
    .ac-LOGIN    { background:#e8f4f8; color:#0c5460; }
    .ac-PAGAR    { background:#d4edda; color:#155724; }
    .ac-TOGGLE   { background:#e2d9f3; color:#432874; }
    .ac-DESACTIVAR{background:#fff3cd; color:#856404; }

    /* ---- BIENVENIDA ---- */
    .welcome-bar {
        background: linear-gradient(135deg, #1f3a34, #2b4a42);
        color: white; border-radius: 12px;
        padding: 20px 24px;
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 22px;
    }
    .welcome-bar h2 { font-size: 18px; font-weight: 700; }
    .welcome-bar p  { font-size: 13px; opacity: 0.7; margin-top: 4px; }
    .welcome-bar .fecha { font-size: 12px; opacity: 0.6; text-align: right; }
    .btn-dashboard {
        background: #f6c90e; color: #1f3a34;
        padding: 8px 16px; border-radius: 8px;
        font-weight: 700; font-size: 13px;
        text-decoration: none; display: flex;
        align-items: center; gap: 6px;
        white-space: nowrap;
    }

    /* ---- ALERTA ---- */
    .alerta-vac {
        background: #fff8e1; border: 1px solid #ffe082;
        border-radius: 8px; padding: 10px 14px;
        font-size: 12px; color: #7a5c00;
        margin-bottom: 10px;
        display: flex; align-items: center; gap: 8px;
    }
</style>
</head>
<body>

<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php" class="active"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="dashboard.php"><i class="ri-pie-chart-2-line"></i> Dashboard</a>
    <?php if (esAdmin()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2>Panel de Administración — RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="dashboard.php" class="top-button"><i class="ri-pie-chart-2-line"></i> Dashboard Gerencial</a>
        <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
        <a href="registrar_empleado_usuario.php" class="top-button"><i class="ri-user-add-line"></i> Nuevo Empleado</a>
        <?php if (esAdmin()): ?>
        <a href="bitacora.php" class="top-button"><i class="ri-file-shield-2-line"></i> Bitácora</a>
        <?php endif; ?>
    </div>

    <div class="contenido">

        <!-- BIENVENIDA -->
        <div class="welcome-bar">
            <div>
                <h2>Bienvenido, <?php echo $_SESSION['usuario']; ?> 👋</h2>
                <p>Sistema de Gestión de Recursos Humanos — KAO SHOP</p>
            </div>
            <div style="display:flex; flex-direction:column; align-items:flex-end; gap:10px;">
                <span class="fecha"><?php echo date('l, d \d\e F \d\e Y'); ?></span>
                <a href="dashboard.php" class="btn-dashboard">
                    <i class="ri-pie-chart-2-line"></i> Ver Dashboard Gerencial
                </a>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpi-grid">

            <a href="listar_empleados.php" class="kpi-card">
                <div class="kpi-icon verde"><i class="ri-team-line"></i></div>
                <div>
                    <div class="kpi-label">Empleados activos</div>
                    <div class="kpi-valor"><?= $empleados_activos ?></div>
                    <div class="kpi-sub">En plantilla</div>
                </div>
            </a>

            <a href="ver_nomina.php" class="kpi-card">
                <div class="kpi-icon azul"><i class="ri-money-dollar-circle-line"></i></div>
                <div>
                    <div class="kpi-label">Costo nómina <?= date('M Y') ?></div>
                    <div class="kpi-valor">Bs. <?= number_format($nomina_mes, 0, ',', '.') ?></div>
                    <div class="kpi-sub">Total neto pagado</div>
                </div>
            </a>

            <a href="deducciones.php" class="kpi-card">
                <div class="kpi-icon naranja"><i class="ri-subtract-line"></i></div>
                <div>
                    <div class="kpi-label">Deducciones <?= date('M Y') ?></div>
                    <div class="kpi-valor">Bs. <?= number_format($deducciones_mes, 0, ',', '.') ?></div>
                    <div class="kpi-sub">Total descontado</div>
                </div>
            </a>

            <a href="vacaciones.php" class="kpi-card">
                <div class="kpi-icon rojo"><i class="ri-sun-line"></i></div>
                <div>
                    <div class="kpi-label">Vacaciones pendientes</div>
                    <div class="kpi-valor"><?= $vac_pendientes ?></div>
                    <div class="kpi-sub">Por aprobar</div>
                </div>
            </a>

        </div>

        <!-- FILA 1: Nóminas + Vacaciones vencidas -->
        <div class="dash-grid">

            <!-- Últimas nóminas -->
            <div class="dash-section">
                <h3><i class="ri-file-list-3-line"></i> Últimas nóminas generadas</h3>
                <?php if (!empty($ultimas_nominas)): ?>
                <table class="dash-table">
                    <tr>
                        <th>#</th>
                        <th>Período</th>
                        <th>Tipo</th>
                        <th>Empl.</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                    <?php foreach ($ultimas_nominas as $n): ?>
                    <tr>
                        <td style="color:#bbb; font-size:11px;"><?= $n['id_nomina'] ?></td>
                        <td style="font-size:11px;"><?= date('d/m', strtotime($n['fecha_inicio'])) ?> — <?= date('d/m/Y', strtotime($n['fecha_fin'])) ?></td>
                        <td><span class="badge <?= $n['tipo'] ?>"><?= ucfirst($n['tipo']) ?></span></td>
                        <td style="text-align:center;"><?= $n['empleados'] ?></td>
                        <td style="font-weight:700; color:#1f3a34;">Bs. <?= number_format($n['total'], 0, ',', '.') ?></td>
                        <td><span class="badge <?= $n['estado'] ?>"><?= ucfirst($n['estado']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <p style="color:#aaa; font-size:13px;">No hay nóminas registradas.</p>
                <?php endif; ?>
            </div>

            <!-- Vacaciones vencidas -->
            <div class="dash-section">
                <h3><i class="ri-alarm-warning-line"></i> Empleados con vacaciones vencidas</h3>
                <?php if (!empty($vac_vencidas)): ?>
                    <?php if (count($vac_vencidas) >= 3): ?>
                    <div class="alerta-vac">
                        <i class="ri-alarm-warning-line"></i>
                        <?= count($vac_vencidas) ?> empleados no han solicitado vacaciones este año.
                    </div>
                    <?php endif; ?>
                    <?php foreach ($vac_vencidas as $v): ?>
                    <div class="vac-item">
                        <div>
                            <div class="vac-nombre"><?= $v['nombre'].' '.$v['apellido'] ?></div>
                            <div class="vac-anos">Desde <?= date('d/m/Y', strtotime($v['fecha_ingreso'])) ?></div>
                        </div>
                        <span class="vac-badge"><?= $v['anos'] ?> año<?= $v['anos'] > 1 ? 's' : '' ?> sin vacac.</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:#28a745; font-size:13px; display:flex; align-items:center; gap:6px;">
                        <i class="ri-checkbox-circle-line"></i> Todos los empleados tienen sus vacaciones al día.
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- FILA 2: Bitácora reciente -->
        <?php if (!empty($ultimas_acciones)): ?>
        <div class="dash-section" style="margin-bottom:20px;">
            <h3><i class="ri-file-shield-2-line"></i> Actividad reciente del sistema
                <a href="bitacora.php" style="margin-left:auto; font-size:11px; color:#2b4a42; font-weight:600; text-decoration:none;">
                    Ver todo →
                </a>
            </h3>
            <?php foreach ($ultimas_acciones as $a): ?>
            <div class="log-item">
                <div class="log-accion">
                    <span class="ac-sm ac-<?= $a['accion'] ?>"><?= $a['accion'] ?></span>
                </div>
                <div class="log-desc">
                    <strong><?= htmlspecialchars($a['usuario']) ?></strong> —
                    <?= htmlspecialchars($a['descripcion']) ?>
                    <span style="color:#bbb; font-size:11px;"> · <?= $a['modulo'] ?></span>
                </div>
                <div class="log-time"><?= date('d/m H:i', strtotime($a['fecha'])) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- fin contenido -->
</div><!-- fin main -->

<?php mysqli_close($conexion); ?>
</body>
</html>