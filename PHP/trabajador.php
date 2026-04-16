<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeEmpleado());

include("db.php");

$empleado_id = $_SESSION['empleado_id'];
// DIAGNÓSTICO TEMPORAL - borra esta línea después

/* =============================================
   1. DATOS PERSONALES DEL EMPLEADO
   ============================================= */
$sql_emp = "SELECT nombre, apellido, cedula, email, telefono, 
                   fecha_ingreso, salario_base, estado, direccion
            FROM empleados 
            WHERE id = $empleado_id";
$res_emp = mysqli_query($conexion, $sql_emp);
$emp = mysqli_fetch_assoc($res_emp);

// Calcular antigüedad
$ingreso   = new DateTime($emp['fecha_ingreso']);
$hoy       = new DateTime();
$diff      = $ingreso->diff($hoy);
$antiguedad = $diff->y . ' año(s) y ' . $diff->m . ' mes(es)';

/* =============================================
   2. ÚLTIMO RECIBO DE NÓMINA
   ============================================= */
$sql_nom = "SELECT dn.id_detalle, dn.salario_base, dn.total_asignaciones,
                   dn.total_deducciones, dn.total_pagar,
                   n.fecha_inicio, n.fecha_fin, n.estado AS estado_nomina
            FROM detalle_nomina dn
            INNER JOIN nomina n ON n.id_nomina = dn.id_nomina
            WHERE dn.empleado_id = $empleado_id
            ORDER BY n.fecha_inicio DESC
            LIMIT 1";
$res_nom = mysqli_query($conexion, $sql_nom);
$recibo  = mysqli_fetch_assoc($res_nom);

/* =============================================
   3. SALDO DE VACACIONES (mismo cálculo que vacaciones.php)
   ============================================= */
$q_ing   = mysqli_query($conexion, "SELECT fecha_ingreso FROM empleados WHERE id=$empleado_id");
$e_ing   = mysqli_fetch_assoc($q_ing);
$antiguedad_años = (new DateTime($e_ing['fecha_ingreso']))->diff(new DateTime())->y;

$dias_acumulados = 0;
if ($antiguedad_años >= 1) {
    $dias_acumulados = min(30, 15 + ($antiguedad_años - 1));
}

$q_usados = mysqli_query($conexion, "SELECT SUM(dias_habiles) as total 
                                     FROM vacaciones 
                                     WHERE empleado_id=$empleado_id 
                                     AND estado='aprobada'");
$usados   = mysqli_fetch_assoc($q_usados)['total'] ?? 0;
$saldo_vacaciones = max(0, $dias_acumulados - $usados);

/* =============================================
   4. SOLICITUDES DE VACACIONES DEL EMPLEADO
   ============================================= */
$sql_vac = "SELECT fecha_inicio, fecha_fin, dias_habiles, estado, observaciones
            FROM vacaciones
            WHERE empleado_id = $empleado_id
            ORDER BY creada_en DESC
            LIMIT 5";
$res_vac = mysqli_fetch_all(mysqli_query($conexion, $sql_vac), MYSQLI_ASSOC);

/* =============================================
   5. HISTORIAL DE ÚLTIMAS NÓMINAS COBRADAS
   ============================================= */
$sql_hist = "SELECT dn.total_pagar, n.fecha_inicio, n.fecha_fin, n.estado
             FROM detalle_nomina dn
             INNER JOIN nomina n ON n.id_nomina = dn.id_nomina
             WHERE dn.empleado_id = $empleado_id
             ORDER BY n.fecha_inicio DESC
             LIMIT 5";
$res_hist = mysqli_fetch_all(mysqli_query($conexion, $sql_hist), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mi Panel - <?php echo $emp['nombre'] . ' ' . $emp['apellido']; ?></title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .panel-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 24px;
    }
    .panel-grid.tres {
        grid-template-columns: 1fr 1fr 1fr;
    }
    .card-info {
        background: var(--white-soft);
        border: 1px solid var(--card-border);
        border-radius: var(--radius);
        padding: 20px 24px;
        box-shadow: var(--shadow);
    }
    .card-info h4 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        margin-bottom: 6px;
    }
    .card-info .valor {
        font-size: 26px;
        font-weight: 700;
        color: var(--green-dark);
    }
    .card-info .sub {
        font-size: 13px;
        color: #aaa;
        margin-top: 4px;
    }
    .card-info .icono {
        font-size: 28px;
        color: var(--green-mid);
        margin-bottom: 10px;
    }
    .seccion {
        background: var(--white-soft);
        border: 1px solid var(--card-border);
        border-radius: var(--radius);
        padding: 24px;
        box-shadow: var(--shadow);
        margin-bottom: 24px;
    }
    .seccion h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--green-dark);
        margin-bottom: 16px;
        border-bottom: 2px solid #e0e0e0;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dato-fila {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }
    .dato-fila:last-child { border-bottom: none; }
    .dato-fila span:first-child { color: #888; }
    .dato-fila span:last-child { font-weight: 600; color: var(--green-dark); }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: var(--green-dark); color: white; padding: 10px 12px; text-align: left; }
    td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; }
    tr:last-child td { border-bottom: none; }
    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge.aprobada { background: #d4edda; color: #155724; }
    .badge.pendiente { background: #fff3cd; color: #856404; }
    .badge.rechazada { background: #f8d7da; color: #721c24; }
    .badge.pagada    { background: #d4edda; color: #155724; }
    .badge.generada  { background: #d1ecf1; color: #0c5460; }
    .btn-recibo {
        display: inline-block;
        background: var(--green-mid);
        color: white;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        text-decoration: none;
        transition: 0.2s;
    }
    .btn-recibo:hover { background: var(--green-hover); }
    .sin-datos { color: #aaa; font-size: 14px; padding: 10px 0; }
    .barra-vac {
        background: #e0e0e0;
        border-radius: 10px;
        height: 10px;
        margin-top: 8px;
        overflow: hidden;
    }
    .barra-vac-fill {
        background: var(--green-mid);
        height: 100%;
        border-radius: 10px;
    }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="../img/logo.png" alt="Logo" class="logo">
        <h3 class="system-title">KAO SHOP</h3>
    </div>

    <a href="trabajador.php" class="active">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="cerrar_sesion.php">
        <i class="ri-logout-box-r-line"></i> Cerrar sesión
    </a>
</aside>

<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Mi Panel Personal</h2>
        <div>
            <span>👤 <?php echo $emp['nombre'] . ' ' . $emp['apellido']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="contenido">

        <!-- TARJETAS RESUMEN -->
        <div class="panel-grid tres">

            <div class="card-info">
                <div class="icono"><i class="ri-wallet-3-line"></i></div>
                <h4>Salario base</h4>
                <div class="valor">Bs. <?php echo number_format($emp['salario_base'], 2); ?></div>
                <div class="sub">Mensual</div>
            </div>

            <div class="card-info">
                <div class="icono"><i class="ri-sun-line"></i></div>
                <h4>Días de vacaciones disponibles</h4>
                <div class="valor"><?php echo $saldo_vacaciones; ?> días</div>
                <div class="sub">De <?php echo $dias_acumulados; ?> acumulados / <?php echo $usados; ?> disfrutados</div>
                <div class="barra-vac">
                    <div class="barra-vac-fill" style="width: <?php echo $dias_acumulados > 0 ? ($saldo_vacaciones/$dias_acumulados)*100 : 0; ?>%"></div>
                </div>
            </div>

            <div class="card-info">
                <div class="icono"><i class="ri-time-line"></i></div>
                <h4>Antigüedad</h4>
                <div class="valor"><?php echo $antiguedad_años; ?> año(s)</div>
                <div class="sub">Desde <?php echo date('d/m/Y', strtotime($emp['fecha_ingreso'])); ?></div>
            </div>

        </div>

        <!-- DATOS PERSONALES + ÚLTIMO RECIBO -->
        <div class="panel-grid">

            <!-- Datos personales -->
            <div class="seccion">
                <h3><i class="ri-user-line"></i> Mis datos personales</h3>
                <div class="dato-fila"><span>Cédula</span><span><?php echo $emp['cedula']; ?></span></div>
                <div class="dato-fila"><span>Nombre</span><span><?php echo $emp['nombre'] . ' ' . $emp['apellido']; ?></span></div>
                <div class="dato-fila"><span>Email</span><span><?php echo $emp['email']; ?></span></div>
                <div class="dato-fila"><span>Teléfono</span><span><?php echo $emp['telefono']; ?></span></div>
                <div class="dato-fila"><span>Dirección</span><span><?php echo $emp['direccion']; ?></span></div>
                <div class="dato-fila"><span>Estatus</span>
                    <span><span class="badge aprobada"><?php echo ucfirst($emp['estado']); ?></span></span>
                </div>
            </div>

            <!-- Último recibo -->
            <div class="seccion">
                <h3><i class="ri-file-text-line"></i> Último recibo de nómina</h3>
                <?php if ($recibo): ?>
                    <div class="dato-fila"><span>Período</span><span><?php echo $recibo['fecha_inicio'] . ' al ' . $recibo['fecha_fin']; ?></span></div>
                    
                    <div class="dato-fila"><span>Salario base</span><span>Bs. <?php echo number_format($recibo['salario_base'], 2); ?></span></div>
                    <div class="dato-fila"><span>Asignaciones</span><span>Bs. <?php echo number_format($recibo['total_asignaciones'], 2); ?></span></div>
                    <div class="dato-fila"><span>Deducciones</span><span>Bs. <?php echo number_format($recibo['total_deducciones'], 2); ?></span></div>
                    <div class="dato-fila"><span>Neto a cobrar</span><span style="color:#1f3a34;font-size:17px;">Bs. <?php echo number_format($recibo['total_pagar'], 2); ?></span></div>
                    <br>
                    <a class="btn-recibo" href="ver_detalle_individual.php?id_detalle=<?php echo $recibo['id_detalle']; ?>" target="_blank">
                        <i class="ri-download-line"></i> Descargar recibo PDF
                    </a>
                <?php else: ?>
                    <p class="sin-datos">Aún no tienes recibos de nómina registrados.</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- HISTORIAL DE NÓMINAS -->
        <div class="seccion">
            <h3><i class="ri-history-line"></i> Historial de nóminas</h3>
            <?php if (!empty($res_hist)): ?>
            <table>
                <tr>
                    <th>Período</th>
                    <th>Neto cobrado</th>
                    <th>Estado</th>
                </tr>
                <?php foreach ($res_hist as $h): ?>
                <tr>
                    <td><?php echo $h['fecha_inicio'] . ' al ' . $h['fecha_fin']; ?></td>
                    <td>Bs. <?php echo number_format($h['total_pagar'], 2); ?></td>
                    <td><span class="badge <?php echo $h['estado']; ?>"><?php echo ucfirst($h['estado']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <p class="sin-datos">No hay nóminas registradas todavía.</p>
            <?php endif; ?>
        </div>

        <!-- SOLICITUDES DE VACACIONES -->
        <div class="seccion">
            <h3><i class="ri-sun-line"></i> Mis solicitudes de vacaciones</h3>
            <?php if (!empty($res_vac)): ?>
            <table>
                <tr>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th>Días hábiles</th>
                    <th>Estado</th>
                    <th>Observaciones</th>
                </tr>
                <?php foreach ($res_vac as $v): ?>
                <tr>
                    <td><?php echo $v['fecha_inicio']; ?></td>
                    <td><?php echo $v['fecha_fin']; ?></td>
                    <td><?php echo $v['dias_habiles']; ?></td>
                    <td><span class="badge <?php echo $v['estado']; ?>"><?php echo ucfirst($v['estado']); ?></span></td>
                    <td><?php echo $v['observaciones'] ?: '—'; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <p class="sin-datos">No tienes solicitudes de vacaciones registradas.</p>
            <?php endif; ?>
        </div>

    </div><!-- fin contenido -->
</div><!-- fin main -->

</body>
</html>
<?php
mysqli_close($conexion);
?>