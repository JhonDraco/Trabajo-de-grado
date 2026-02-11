<?php
include("db.php");
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

/* =============================================
   LÓGICA DE PROCESAMIENTO (Antes de la vista)
   ============================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_vacacion'])) {
    $id_vacacion = intval($_POST['id_vacacion']);
    $accion = $_POST['accion'];
    $obs = mysqli_real_escape_string($conexion, $_POST['observaciones']);

    // Obtener datos críticos de la vacación para el saldo
    $queryInfo = mysqli_query($conexion, "SELECT empleado_id, dias_habiles, fecha_inicio FROM vacaciones WHERE id_vacacion = $id_vacacion");
    $dataV = mysqli_fetch_assoc($queryInfo);
    
    $empleado_id = $dataV['empleado_id'];
    $dias_habiles = intval($dataV['dias_habiles']);
    $anio = date('Y', strtotime($dataV['fecha_inicio']));

    mysqli_begin_transaction($conexion);
    try {
        if ($accion === 'rechazar') {
            mysqli_query($conexion, "UPDATE vacaciones SET estado='rechazada', observaciones='$obs' WHERE id_vacacion=$id_vacacion");
        } else if ($accion === 'aprobar') {
            $resSaldo = mysqli_query($conexion, "SELECT * FROM vacaciones_saldo WHERE empleado_id=$empleado_id AND anio=$anio");
            if (mysqli_num_rows($resSaldo) == 0) throw new Exception("Sin saldo configurado para el año $anio");

            $saldo = mysqli_fetch_assoc($resSaldo);
            $nuevo_disfrutado = $saldo['dias_disfrutados'] + $dias_habiles;
            $nuevo_pendiente  = $saldo['dias_pendientes'] - $dias_habiles;

            if ($nuevo_pendiente < 0) throw new Exception("Días insuficientes en el saldo.");

            mysqli_query($conexion, "UPDATE vacaciones SET estado='aprobada', observaciones='$obs' WHERE id_vacacion=$id_vacacion");
            mysqli_query($conexion, "UPDATE vacaciones_saldo SET dias_disfrutados=$nuevo_disfrutado, dias_pendientes=$nuevo_pendiente, actualizado_en=NOW() WHERE id_saldo={$saldo['id_saldo']}");
        }
        mysqli_commit($conexion);
        header("Location: vacaciones.php?msg=" . $accion);
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $error_msg = $e->getMessage();
    }
}

/* ===========================
   DATOS PARA VISTA
   =========================== */
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vacaciones</title>
    <link rel="stylesheet" href="../css/solicitudes _de_vacaciones.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        /* Estilos para el desplegable */
        .fila-detalle { display: none; background-color: #f9f9f9; }
        .form-desplegable { padding: 15px; border: 1px solid #ddd; border-radius: 8px; margin: 10px; background: white; }
       .grid-info { 
    display: grid; 
    /* Nota la 'u' y la 's' al final de columns */
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); 
    gap: 10px; 
    margin-bottom: 15px; 
    font-size: 0.9em; 
}
        .btn-form { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .btn-aprobar-f { background: #28a745; color: white; }
        .btn-rechazar-f { background: #dc3545; color: white; }
        textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-top: 5px; }
    </style>
</head>
<body>

<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php"class="active">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php" >
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Agendar entrevistas 
    </a>
    
   
</aside>



<div class="main">
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="vacaciones.php" class="top-button">Gestión de Vacaciones</a>
    </div>

    <div class="contenido">
        <h3>Solicitudes de Vacaciones</h3>
        
        <?php if(isset($error_msg)): ?>
            <div style="color: red; padding: 10px; background: #fee; border-radius: 5px; margin-bottom: 10px;">
                <strong>Error:</strong> <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <table border="0" class="tabla-vacaciones">
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Periodo</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($v = mysqli_fetch_assoc($vacaciones)) { 
                $id = $v['id_vacacion'];
                $esPendiente = ($v['estado'] === 'pendiente');
            ?>
                <tr>
                    <td><?= $v['nombre']." ".$v['apellido'] ?></td>
                    <td><?= $v['fecha_inicio']." al ".$v['fecha_fin'] ?></td>
                    <td><?= $v['dias_habiles'] ?></td>
                    <td><span class="badge-<?= $v['estado'] ?>"><?= ucfirst($v['estado']) ?></span></td>
                    <td>
                        <?php if($esPendiente): ?>
                            <button class="btn-accion btn-aprobar" onclick="toggleForm(<?= $id ?>)">
                                <i class="ri-edit-2-line"></i> Procesar
                            </button>
                        <?php else: ?>
                            <span class="text-muted">Procesada</span>
                        <?php endif; ?>
                    </td>
                </tr>

                <?php if($esPendiente): ?>
                <tr id="detalle-<?= $id ?>" class="fila-detalle">
                    <td colspan="5">
                        <div class="form-desplegable shadow">
                            <h4 style="margin-top:0"><i class="ri-information-line"></i> Detalles de la Solicitud</h4>
                            <div class="grid-info">
                                <div><strong>Días Solicitados:</strong> <?= $v['dias_solicitados'] ?></div>
                                <div><strong>Días Hábiles:</strong> <?= $v['dias_habiles'] ?></div>
                                <div><strong>Feriados/Libres:</strong> <?= $v['dias_feriados'] ?></div>
                            </div>
                            
                            <form method="post">
                                <input type="hidden" name="id_vacacion" value="<?= $id ?>">
                                <label><strong>Observaciones del Administrador:</strong></label>
                                <textarea name="observaciones" placeholder="Escriba el motivo de la aprobación o rechazo..."></textarea>
                                
                                <div style="margin-top:15px; display: flex; gap: 10px;">
                                    <button type="submit" name="accion" value="aprobar" class="btn-form btn-aprobar-f">✅ Confirmar Aprobación</button>
                                    <button type="submit" name="accion" value="rechazar" class="btn-form btn-rechazar-f">❌ Rechazar Solicitud</button>
                                    <button type="button" class="btn-form" onclick="toggleForm(<?= $id ?>)" style="background:#ccc">Cancelar</button>
                                </div>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleForm(id) {
    const fila = document.getElementById('detalle-' + id);
    if (fila.style.display === 'table-row') {
        fila.style.display = 'none';
    } else {
        // Opcional: Cerrar otros formularios abiertos primero
        document.querySelectorAll('.fila-detalle').forEach(f => f.style.display = 'none');
        fila.style.display = 'table-row';
    }
}
</script>

</body>
</html>