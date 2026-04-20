<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeSalariosArchivos());

include("db.php");

if (!isset($_GET['id'])) {
    header("Location: listar_empleados.php");
    exit();
}

$id = (int)$_GET['id'];

/* =============================================
   1. DATOS PERSONALES
   ============================================= */
$emp = mysqli_fetch_assoc(mysqli_query($conexion,
    "SELECT * FROM empleados WHERE id = $id"
));

if (!$emp) {
    echo "<script>alert('Empleado no encontrado.'); window.location='listar_empleados.php';</script>";
    exit();
}

$ingreso      = new DateTime($emp['fecha_ingreso']);
$hoy          = new DateTime();
$diff         = $ingreso->diff($hoy);
$antiguedad   = $diff->y . ' año(s) y ' . $diff->m . ' mes(es)';

/* =============================================
   2. ASIGNACIONES ACTIVAS DEL EMPLEADO
   ============================================= */
$asignaciones = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT ae.id_asig_emp, ta.nombre, ae.monto, ta.tipo, ae.creada_en
    FROM asignacion_empleado ae
    JOIN tipo_asignacion ta ON ta.id_asignacion = ae.id_asignacion
    WHERE ae.empleado_id = $id AND ae.activa = 1
    ORDER BY ae.creada_en DESC
"), MYSQLI_ASSOC);

/* =============================================
   3. DEDUCCIONES ACTIVAS DEL EMPLEADO
   ============================================= */
$deducciones = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT id_deduccion_emp, nombre, monto, cuotas, cuota_actual, activa
    FROM deduccion_empleado
    WHERE empleado_id = $id AND activa = 1
    ORDER BY id_deduccion_emp DESC
"), MYSQLI_ASSOC);

/* =============================================
   4. ÚLTIMAS 5 NÓMINAS
   ============================================= */
$nominas = mysqli_fetch_all(mysqli_query($conexion, "
    SELECT dn.id_detalle, dn.total_pagar, n.fecha_inicio, n.fecha_fin, n.tipo, n.estado
    FROM detalle_nomina dn
    JOIN nomina n ON n.id_nomina = dn.id_nomina
    WHERE dn.empleado_id = $id
    ORDER BY n.fecha_inicio DESC
    LIMIT 5
"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salarios y Archivos - <?php echo $emp['nombre'].' '.$emp['apellido']; ?></title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .perfil-header {
        display: flex;
        align-items: center;
        gap: 20px;
        background: linear-gradient(135deg, #1f3a34, #2b4a42);
        color: white;
        padding: 24px 28px;
        border-radius: 12px;
        margin-bottom: 24px;
    }
    .perfil-avatar {
        width: 64px;
        height: 64px;
        background: rgba(255,255,255,0.15);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        flex-shrink: 0;
    }
    .perfil-nombre { font-size: 22px; font-weight: 700; }
    .perfil-sub    { font-size: 13px; opacity: 0.75; margin-top: 4px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 24px; }

    .card {
        background: #f7f7f7;
        border: 1px solid #e2e2e2;
        border-radius: 12px;
        padding: 20px 24px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .card h4 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
        margin-bottom: 6px;
    }
    .card .valor {
        font-size: 24px;
        font-weight: 700;
        color: #1f3a34;
    }
    .card .icono { font-size: 24px; color: #2b4a42; margin-bottom: 8px; }

    .seccion {
        background: #f7f7f7;
        border: 1px solid #e2e2e2;
        border-radius: 12px;
        padding: 22px 24px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .seccion h3 {
        font-size: 15px;
        font-weight: 600;
        color: #1f3a34;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e0e0e0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dato-fila {
        display: flex;
        justify-content: space-between;
        padding: 7px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 14px;
    }
    .dato-fila:last-child { border-bottom: none; }
    .dato-fila span:first-child { color: #888; }
    .dato-fila span:last-child  { font-weight: 600; color: #1f3a34; }

    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th { background: #1f3a34; color: white; padding: 10px 12px; text-align: left; }
    td { padding: 9px 12px; border-bottom: 1px solid #f0f0f0; }
    tr:last-child td { border-bottom: none; }

    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge.activo   { background:#d4edda; color:#155724; }
    .badge.inactivo { background:#f8d7da; color:#721c24; }
    .badge.pagada   { background:#d4edda; color:#155724; }
    .badge.generada { background:#d1ecf1; color:#0c5460; }

    .btn-accion {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.2s;
        cursor: pointer;
        border: none;
    }
    .btn-verde   { background:#1f3a34; color:white; }
    .btn-verde:hover { background:#2b4a42; }
    .btn-rojo    { background:#dc3545; color:white; }
    .btn-rojo:hover  { background:#b02a37; }
    .btn-azul    { background:#0d6efd; color:white; }
    .btn-azul:hover  { background:#0b5ed7; }

    .docs-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .doc-card {
        border: 1px solid #e2e2e2;
        border-radius: 10px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        background: white;
    }
    .doc-icono {
        font-size: 28px;
        color: #2b4a42;
        flex-shrink: 0;
    }
    .doc-info p  { font-size: 13px; color:#888; margin-top:2px; }
    .doc-info strong { font-size: 14px; color:#1f3a34; }
    .sin-datos { color:#aaa; font-size:14px; padding:8px 0; }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="generar_nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php" class="active"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <?php if (esAdmin()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">

    <header>
        <h2><i class="ri-folder-user-line"></i> Salarios y Archivos</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="listar_empleados.php" class="top-button">
            <i class="ri-arrow-left-line"></i> Volver a Empleados
        </a>
        <a href="editar_empleado.php?id=<?= $id ?>" class="top-button">
            <i class="ri-edit-2-line"></i> Editar Datos
        </a>
        <a href="asignaciones.php" class="top-button">
            <i class="ri-add-circle-line"></i> Gestionar Asignaciones
        </a>
        <a href="deducciones.php" class="top-button">
            <i class="ri-subtract-line"></i> Gestionar Deducciones
        </a>
    </div>

    <div class="contenido">

        <!-- CABECERA DEL EMPLEADO -->
        <div class="perfil-header">
            <div class="perfil-avatar"><i class="ri-user-line"></i></div>
            <div>
                <div class="perfil-nombre"><?= $emp['nombre'].' '.$emp['apellido'] ?></div>
                <div class="perfil-sub">C.I: <?= $emp['cedula'] ?> &nbsp;|&nbsp; Ingreso: <?= date('d/m/Y', strtotime($emp['fecha_ingreso'])) ?> &nbsp;|&nbsp; Antigüedad: <?= $antiguedad ?></div>
                <div class="perfil-sub" style="margin-top:6px;">
                    <span class="badge <?= strtolower($emp['estado']) == 'activo' ? 'activo' : 'inactivo' ?>">
                        <?= ucfirst($emp['estado']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- TARJETAS RESUMEN -->
        <div class="grid-3">
            <div class="card">
                <div class="icono"><i class="ri-money-dollar-circle-line"></i></div>
                <h4>Salario base</h4>
                <div class="valor">Bs. <?= number_format($emp['salario_base'], 2) ?></div>
            </div>
            <div class="card">
                <div class="icono"><i class="ri-add-circle-line"></i></div>
                <h4>Asignaciones activas</h4>
                <div class="valor"><?= count($asignaciones) ?></div>
            </div>
            <div class="card">
                <div class="icono"><i class="ri-subtract-line"></i></div>
                <h4>Deducciones activas</h4>
                <div class="valor"><?= count($deducciones) ?></div>
            </div>
        </div>

        <!-- DATOS PERSONALES + NÓMINAS -->
        <div class="grid-2">

            <div class="seccion">
                <h3><i class="ri-user-line"></i> Datos personales</h3>
                <div class="dato-fila"><span>Cédula</span><span><?= $emp['cedula'] ?></span></div>
                <div class="dato-fila"><span>Nombre</span><span><?= $emp['nombre'].' '.$emp['apellido'] ?></span></div>
                <div class="dato-fila"><span>Email</span><span><?= $emp['email'] ?: '—' ?></span></div>
                <div class="dato-fila"><span>Teléfono</span><span><?= $emp['telefono'] ?: '—' ?></span></div>
                <div class="dato-fila"><span>Dirección</span><span><?= $emp['direccion'] ?: '—' ?></span></div>
                <div class="dato-fila"><span>Fecha de ingreso</span><span><?= date('d/m/Y', strtotime($emp['fecha_ingreso'])) ?></span></div>
                <div class="dato-fila"><span>Antigüedad</span><span><?= $antiguedad ?></span></div>
                <br>
                <a href="editar_empleado.php?id=<?= $id ?>" class="btn-accion btn-verde">
                    <i class="ri-edit-2-line"></i> Editar datos
                </a>
            </div>

            <div class="seccion">
                <h3><i class="ri-history-line"></i> Últimas nóminas</h3>
                <?php if (!empty($nominas)): ?>
                <table>
                    <tr>
                        <th>Período</th>
                        <th>Tipo</th>
                        <th>Neto</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    <?php foreach ($nominas as $n): ?>
                    <tr>
                        <td><?= $n['fecha_inicio'].' al '.$n['fecha_fin'] ?></td>
                        <td><?= ucfirst($n['tipo']) ?></td>
                        <td>Bs. <?= number_format($n['total_pagar'], 2) ?></td>
                        <td><span class="badge <?= $n['estado'] ?>"><?= ucfirst($n['estado']) ?></span></td>
                        <td>
                            <a href="ver_detalle_individual.php?id_detalle=<?= $n['id_detalle'] ?>"
                               target="_blank" class="btn-accion btn-azul" style="padding:3px 10px; font-size:12px;">
                               <i class="ri-file-pdf-line"></i> PDF
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <?php else: ?>
                    <p class="sin-datos">No hay nóminas registradas para este empleado.</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- ASIGNACIONES -->
        <div class="seccion">
            <h3><i class="ri-add-circle-line"></i> Asignaciones activas</h3>
            <?php if (!empty($asignaciones)): ?>
            <table>
                <tr>
                    <th>Concepto</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Desde</th>
                    <th>Acción</th>
                </tr>
                <?php foreach ($asignaciones as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['nombre']) ?></td>
                    <td><?= ucfirst($a['tipo']) ?></td>
                    <td><?= $a['tipo'] == 'porcentaje' ? $a['monto'].'%' : 'Bs. '.number_format($a['monto'],2) ?></td>
                    <td><?= date('d/m/Y', strtotime($a['creada_en'])) ?></td>
                    <td>
                        <a href="eliminar_asignacion_empleado.php?id=<?= $a['id_asig_emp'] ?>"
                           onclick="return confirm('¿Desactivar esta asignación?')"
                           class="btn-accion btn-rojo" style="padding:3px 10px; font-size:12px;">
                           <i class="ri-close-line"></i> Desactivar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <p class="sin-datos">No tiene asignaciones activas.</p>
            <?php endif; ?>
        </div>

        <!-- DEDUCCIONES -->
        <div class="seccion">
            <h3><i class="ri-subtract-line"></i> Deducciones activas</h3>
            <?php if (!empty($deducciones)): ?>
            <table>
                <tr>
                    <th>Concepto</th>
                    <th>Monto total</th>
                    <th>Progreso cuotas</th>
                    <th>Acción</th>
                </tr>
                <?php foreach ($deducciones as $d): ?>
                <tr>
                    <td><?= htmlspecialchars($d['nombre']) ?></td>
                    <td>Bs. <?= number_format($d['monto'], 2) ?></td>
                    <td><?= $d['cuota_actual'].' / '.$d['cuotas'] ?></td>
                    <td>
                        <a href="eliminar_deduccion_empleado.php?id=<?= $d['id_deduccion_emp'] ?>"
                           onclick="return confirm('¿Desactivar esta deducción?')"
                           class="btn-accion btn-rojo" style="padding:3px 10px; font-size:12px;">
                           <i class="ri-close-line"></i> Desactivar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
                <p class="sin-datos">No tiene deducciones activas.</p>
            <?php endif; ?>
        </div>

        <!-- DOCUMENTOS -->
        <div class="seccion">
            <h3><i class="ri-folder-line"></i> Documentos y archivos</h3>
            <div class="docs-grid">

                <div class="doc-card">
                    <div class="doc-icono"><i class="ri-file-text-line"></i></div>
                    <div class="doc-info">
                        <strong>Carta de Trabajo</strong>
                        <p>Constancia de empleo en formato PDF</p>
                        <form method="POST" action="carta_de trabajo_pdf.php" target="_blank" style="margin-top:8px;">
                            <input type="hidden" name="name"     value="<?= $emp['nombre'] ?>">
                            <input type="hidden" name="apellido" value="<?= $emp['apellido'] ?>">
                            <input type="hidden" name="cedula"   value="<?= $emp['cedula'] ?>">
                            <button type="submit" class="btn-accion btn-verde">
                                <i class="ri-download-line"></i> Generar PDF
                            </button>
                        </form>
                    </div>
                </div>

                <div class="doc-card">
                    <div class="doc-icono"><i class="ri-award-line"></i></div>
                    <div class="doc-info">
                        <strong>Referencia Laboral</strong>
                        <p>Referencia formal del empleado en PDF</p>
                        <form method="POST" action="referencia_laboral_pdf.php" target="_blank" style="margin-top:8px;">
                            <input type="hidden" name="name"   value="<?= $emp['nombre'].' '.$emp['apellido'] ?>">
                            <input type="hidden" name="cedula" value="<?= $emp['cedula'] ?>">
                            <button type="submit" class="btn-accion btn-verde">
                                <i class="ri-download-line"></i> Generar PDF
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>

    </div><!-- fin contenido -->
</div><!-- fin main -->

</body>
</html>
<?php mysqli_close($conexion); ?>