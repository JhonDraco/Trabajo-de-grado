<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// 1. PROCESAR EL PAGO SI SE ENVÍA EL FORMULARIO (LOGICA POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_nomina'])) {
    $id_nomina = intval($_POST['id_nomina']);
    $fecha = $_POST['fecha_pago'];
    $metodo = mysqli_real_escape_string($conexion, $_POST['metodo']);
    $notas = mysqli_real_escape_string($conexion, $_POST['notas']);

    // Obtener total para el registro histórico
    $sql = "SELECT SUM(total_pagar) AS total FROM detalle_nomina WHERE id_nomina = $id_nomina";
    $res = mysqli_query($conexion, $sql);
    $data = mysqli_fetch_assoc($res);
    $total_nomina = $data['total'] ?? 0;

    // Insertar en tabla pagos
    mysqli_query($conexion, "
        INSERT INTO pagos (id_nomina, fecha_pago, total_pagado, metodo, notas)
        VALUES ($id_nomina, '$fecha', $total_nomina, '$metodo', '$notas')
    ");

    // Actualizar estado de nomina
    mysqli_query($conexion, "UPDATE nomina SET estado='pagada' WHERE id_nomina=$id_nomina");

    header("Location: pagar_nomina.php?ok=1");
    exit();
}

// 2. OBTENER NÓMINAS PENDIENTES
$consulta = "SELECT * FROM nomina WHERE estado != 'pagada' ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagos de Nómina</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/pagos_nomina.css">
    <style>

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
    <a href="nomina.php" class="active">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>

    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Agendar entrevistas 
    </a>
    
   
</aside>

<div class="main">
    <header>
        <h2>Gestión de Pagos</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
       <a href="asignaciones.php" class="top-button"><i class="ri-add-circle-line"></i> Asignaciones</a>
       <a href="deducciones.php" class="top-button"><i class="ri-subtract-line"></i> Deducciónes</a>
       <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>

    </div>

    <div class="contenido">
        <?php if(isset($_GET['ok'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
                <i class="ri-checkbox-circle-line"></i> El pago se ha registrado y la nómina ha sido cerrada.
            </div>
        <?php endif; ?>

        <h3>Nóminas Pendientes de Pago</h3>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Periodo</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($n = mysqli_fetch_assoc($nominas)) { 
                    $id_n = $n['id_nomina'];
                    // Obtener total dinámico
                    $s = mysqli_query($conexion, "SELECT SUM(total_pagar) as t FROM detalle_nomina WHERE id_nomina=$id_n");
                    $r = mysqli_fetch_assoc($s);
                    $monto = $r['t'] ?? 0;
                ?>
                    <tr>
                        <td><strong>#<?= $id_n ?></strong></td>
                        <td><i class="ri-calendar-line"></i> <?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>
                        <td><?= ucfirst($n['tipo']) ?></td>
                        <td><span class="badge-pendiente"><?= strtoupper($n['estado']) ?></span></td>
                        <td>
                            <button onclick="toggleFormulario(<?= $id_n ?>)" class="top-button" style="background: var(--green-mid); border:none; cursor:pointer; width: 100%;">
                                <i class="ri-arrow-down-s-line"></i> Pagar Bs. <?= number_format($monto, 2) ?>
                            </button>
                        </td>
                    </tr>

                    <tr id="row-<?= $id_n ?>" class="fila-pago">
                        <td colspan="5">
                            <div class="form-pago-container">
                                <form method="POST" class="form-flex">
                                    <input type="hidden" name="id_nomina" value="<?= $id_n ?>">
                                    
                                    <div class="input-mini">
                                        <label>Fecha de Pago</label>
                                        <input type="date" name="fecha_pago" value="<?= date('Y-m-d') ?>" required>
                                    </div>

                                    <div class="input-mini">
                                        <label>Método</label>
                                        <select name="metodo">
                                            <option value="transferencia">Transferencia</option>
                                            <option value="pago móvil">Pago Móvil</option>
                                            <option value="efectivo">Efectivo</option>
                                        </select>
                                    </div>

                                    <div class="input-mini" style="flex: 2;">
                                        <label>Referencia / Notas</label>
                                        <input type="text" name="notas" placeholder="Nro de confirmación o comentario">
                                    </div>

                                    <button type="submit" class="btn-confirmar">
                                        <i class="ri-check-double-line"></i> Confirmar Pago
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleFormulario(id) {
    // Cerrar cualquier otro que esté abierto (opcional, para limpieza)
    // document.querySelectorAll('.fila-pago').forEach(el => el.style.display = 'none');

    var fila = document.getElementById('row-' + id);
    if (fila.style.display === "table-row") {
        fila.style.display = "none";
    } else {
        fila.style.display = "table-row";
    }
}
</script>

</body>
</html>