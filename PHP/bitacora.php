<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(esAdmin());

include("db.php");

// Filtros
$filtro_usuario = isset($_GET['usuario']) ? mysqli_real_escape_string($conexion, $_GET['usuario']) : '';
$filtro_modulo  = isset($_GET['modulo'])  ? mysqli_real_escape_string($conexion, $_GET['modulo'])  : '';
$filtro_accion  = isset($_GET['accion'])  ? mysqli_real_escape_string($conexion, $_GET['accion'])  : '';
$filtro_fecha   = isset($_GET['fecha'])   ? mysqli_real_escape_string($conexion, $_GET['fecha'])   : '';

$where = "WHERE 1=1";
if ($filtro_usuario) $where .= " AND usuario LIKE '%$filtro_usuario%'";
if ($filtro_modulo)  $where .= " AND modulo = '$filtro_modulo'";
if ($filtro_accion)  $where .= " AND accion = '$filtro_accion'";
if ($filtro_fecha)   $where .= " AND DATE(fecha) = '$filtro_fecha'";

$registros = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT * FROM auditoria $where ORDER BY fecha DESC LIMIT 300"
), MYSQLI_ASSOC);

$total = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT COUNT(*) as total FROM auditoria $where"
))['total'];

// Módulos y acciones únicos para los filtros
$modulos  = mysqli_fetch_all(mysqli_query($conexion, "SELECT DISTINCT modulo FROM auditoria ORDER BY modulo"), MYSQLI_ASSOC);
$acciones = mysqli_fetch_all(mysqli_query($conexion, "SELECT DISTINCT accion FROM auditoria ORDER BY accion"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Bitácora del Sistema</title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .filtros {
        display: flex; gap: 10px; flex-wrap: wrap;
        background: #f7f7f7; border: 1px solid #e2e2e2;
        border-radius: 10px; padding: 16px 20px;
        margin-bottom: 20px; align-items: flex-end;
    }
    .filtro-group { display: flex; flex-direction: column; gap: 4px; min-width: 140px; }
    .filtro-group label { font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; }
    .filtro-group input,
    .filtro-group select {
        padding: 7px 10px; border: 2px solid #e2e2e2;
        border-radius: 6px; font-size: 13px; outline: none;
    }
    .filtro-group input:focus,
    .filtro-group select:focus { border-color: #2b4a42; }
    .btn-filtro {
        padding: 8px 18px; background: #1f3a34; color: white;
        border: none; border-radius: 6px; font-size: 13px;
        font-weight: 600; cursor: pointer; align-self: flex-end;
    }
    .btn-limpiar {
        padding: 8px 14px; background: #f0f0f0; color: #555;
        border: none; border-radius: 6px; font-size: 13px;
        font-weight: 600; cursor: pointer; align-self: flex-end;
        text-decoration: none;
    }

    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { background: #1f3a34; color: white; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; }
    td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
    tr:hover td { background: #fafafa; }

    .badge-accion {
        display: inline-block; padding: 2px 9px;
        border-radius: 20px; font-size: 11px; font-weight: 700;
    }
    .ac-CREAR     { background:#d4edda; color:#155724; }
    .ac-EDITAR    { background:#d1ecf1; color:#0c5460; }
    .ac-ELIMINAR  { background:#f8d7da; color:#721c24; }
    .ac-DESACTIVAR{ background:#fff3cd; color:#856404; }
    .ac-TOGGLE    { background:#e2d9f3; color:#432874; }
    .ac-PAGAR     { background:#d4edda; color:#155724; }
    .ac-LOGIN     { background:#e8f4f8; color:#0c5460; }
    .ac-APROBAR   { background:#d4edda; color:#155724; }
    .ac-RECHAZAR  { background:#f8d7da; color:#721c24; }
    .ac-PROCESAR  { background:#fff3cd; color:#856404; }

    .modulo-tag {
        font-size: 11px; font-weight: 600; color: #2b4a42;
        background: #eaf3ef; padding: 2px 8px; border-radius: 4px;
    }
    .desc-cell { max-width: 320px; color: #444; line-height: 1.4; }
    .ip-cell   { color: #aaa; font-size: 11px; font-family: monospace; }
    .fecha-cell { font-size: 12px; color: #666; white-space: nowrap; }

    .resumen {
        display: flex; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .resumen-card {
        background: #f7f7f7; border: 1px solid #e2e2e2;
        border-radius: 8px; padding: 12px 18px; flex: 1; min-width: 120px;
    }
    .resumen-card .num { font-size: 24px; font-weight: 700; color: #1f3a34; }
    .resumen-card .lbl { font-size: 11px; color: #888; text-transform: uppercase; margin-top: 2px; }

    .sin-datos { text-align: center; color: #aaa; padding: 30px; font-size: 14px; }
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
    <a href="bitacora.php" class="active"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2><i class="ri-file-shield-2-line"></i> Bitácora del Sistema</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu"></div>

    <div class="contenido">

        <!-- RESUMEN -->
        <?php
        $total_hoy    = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM auditoria WHERE DATE(fecha) = CURDATE()"))['t'];
        $total_login  = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM auditoria WHERE accion = 'LOGIN'"))['t'];
        $total_editar = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM auditoria WHERE accion IN ('EDITAR','CREAR')"))['t'];
        $total_elim   = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT COUNT(*) as t FROM auditoria WHERE accion IN ('ELIMINAR','DESACTIVAR')"))['t'];
        ?>
        <div class="resumen">
            <div class="resumen-card">
                <div class="num"><?= $total ?></div>
                <div class="lbl">Registros filtrados</div>
            </div>
            <div class="resumen-card">
                <div class="num"><?= $total_hoy ?></div>
                <div class="lbl">Acciones hoy</div>
            </div>
            <div class="resumen-card">
                <div class="num"><?= $total_login ?></div>
                <div class="lbl">Inicios de sesión</div>
            </div>
            <div class="resumen-card">
                <div class="num"><?= $total_editar ?></div>
                <div class="lbl">Creaciones / Ediciones</div>
            </div>
            <div class="resumen-card">
                <div class="num"><?= $total_elim ?></div>
                <div class="lbl">Eliminaciones / Desactivaciones</div>
            </div>
        </div>

        <!-- FILTROS -->
        <form method="GET" class="filtros">
            <div class="filtro-group">
                <label>Usuario</label>
                <input type="text" name="usuario" value="<?= htmlspecialchars($filtro_usuario) ?>" placeholder="Buscar usuario...">
            </div>
            <div class="filtro-group">
                <label>Módulo</label>
                <select name="modulo">
                    <option value="">Todos</option>
                    <?php foreach ($modulos as $m): ?>
                        <option value="<?= $m['modulo'] ?>" <?= $filtro_modulo == $m['modulo'] ? 'selected' : '' ?>>
                            <?= $m['modulo'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-group">
                <label>Acción</label>
                <select name="accion">
                    <option value="">Todas</option>
                    <?php foreach ($acciones as $a): ?>
                        <option value="<?= $a['accion'] ?>" <?= $filtro_accion == $a['accion'] ? 'selected' : '' ?>>
                            <?= $a['accion'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtro-group">
                <label>Fecha</label>
                <input type="date" name="fecha" value="<?= htmlspecialchars($filtro_fecha) ?>">
            </div>
            <button type="submit" class="btn-filtro"><i class="ri-search-line"></i> Filtrar</button>
            <a href="bitacora.php" class="btn-limpiar"><i class="ri-close-line"></i> Limpiar</a>
        </form>

        <!-- TABLA -->
        <?php if (!empty($registros)): ?>
        <table>
            <tr>
                <th>#</th>
                <th>Fecha y hora</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Módulo</th>
                <th>Descripción</th>
                <th>IP</th>
            </tr>
            <?php foreach ($registros as $r): ?>
            <tr>
                <td style="color:#bbb; font-size:11px;"><?= $r['id'] ?></td>
                <td class="fecha-cell"><?= date('d/m/Y H:i:s', strtotime($r['fecha'])) ?></td>
                <td><strong><?= htmlspecialchars($r['usuario']) ?></strong></td>
                <td>
                    <span class="badge-accion ac-<?= $r['accion'] ?>">
                        <?= $r['accion'] ?>
                    </span>
                </td>
                <td><span class="modulo-tag"><?= htmlspecialchars($r['modulo']) ?></span></td>
                <td class="desc-cell"><?= htmlspecialchars($r['descripcion']) ?></td>
                <td class="ip-cell"><?= htmlspecialchars($r['ip']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php else: ?>
            <div class="sin-datos">
                <i class="ri-file-shield-2-line" style="font-size:32px; display:block; margin-bottom:8px;"></i>
                No hay registros<?= ($filtro_usuario || $filtro_modulo || $filtro_accion || $filtro_fecha) ? ' para los filtros aplicados' : ' en la bitácora aún' ?>.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php mysqli_close($conexion); ?>
</body>
</html>