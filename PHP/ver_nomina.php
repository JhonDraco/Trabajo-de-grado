<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

// Ordenar por fecha de creación descendente
$consulta = "SELECT * FROM nomina ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ver Nóminas</title>

<!-- CSS -->
<link rel="stylesheet" href="../css/ver_nomina.css">

<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>
<body>

<!-- SIDEBAR -->
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

    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
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
      <i class="ri-mail-line"></i> Email
    </a>
    
   
</aside>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php"><i class="ri-logout-box-line"></i> Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
       <a href="asignaciones.php" class="top-button"><i class="ri-add-circle-line"></i> Asignaciones</a>
       <a href="deducciones.php" class="top-button"><i class="ri-subtract-line"></i> Deducciónes</a>
       <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>

    </div>

    <!-- TABLA -->
    <h2>📄 Nóminas Generadas</h2>

    <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>ID Nómina</th>
                <th>Período</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Creada por</th>
                <th>Fecha creación</th>
                <th>Acciones</th>
            </tr>
            </thead>

            <tbody>
            <?php while ($n = mysqli_fetch_array($nominas)) { ?>
            <tr>
                <td><?= $n['id_nomina'] ?></td>
                <td><?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>
                <td><?= $n['tipo'] ?></td>
                <td><?= $n['estado'] ?></td>
                <td><?= $n['creada_por'] ?></td>
                <td><?= $n['fecha_creacion'] ?></td>
                <td>
                  <a href="#" onclick="toggleDetalle(<?= $n['id_nomina'] ?>); return false;">
                     <i class="ri-eye-line"></i> Ver
                    </a>
|
                    <a href="eliminar_nomina.php?id=<?= $n['id_nomina'] ?>" onclick="return confirm('¿Eliminar esta nómina?')"><i class="ri-delete-bin-line"></i> Eliminar</a>
                </td>
            </tr>
            <tr id="detalle-<?= $n['id_nomina'] ?>" style="display:none;">
    <td colspan="7">
        <div id="contenido-detalle-<?= $n['id_nomina'] ?>">
            Cargando...
        </div>
    </td>
</tr>

            <?php } ?>
            </tbody>

        </table>
    </div>

</div>
<script>
function toggleDetalle(idNomina) {

    const fila = document.getElementById('detalle-' + idNomina);
    const contenedor = document.getElementById('contenido-detalle-' + idNomina);

    // Si ya está visible, ocultar
    if (fila.style.display === 'table-row') {
        fila.style.display = 'none';
        return;
    }

    // Mostrar fila
    fila.style.display = 'table-row';

    // Si ya se cargó antes, no volver a pedir
    if (contenedor.dataset.cargado) {
        return;
    }

    fetch('ajax_detalle_nomina.php?id=' + idNomina)
        .then(response => response.text())
        .then(data => {
            contenedor.innerHTML = data;
            contenedor.dataset.cargado = true;
        })
        .catch(() => {
            contenedor.innerHTML = 'Error al cargar el detalle.';
        });
}
</script>

</body>
</html>
