<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeVerNomina());

include("db.php");


$sql = "SELECT p.*, n.fecha_inicio, n.fecha_fin 
        FROM pagos p 
        JOIN nomina n ON p.id_nomina = n.id_nomina
        ORDER BY p.fecha_pago DESC";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de Pagos</title>

<link rel="stylesheet" href="../css/historial_de_pagos.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

<style>
.buscador {
    width: 100%;
    padding: 10px;
    margin: 15px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
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
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <?php if (puedeGenerarNomina()): ?>
    <a href="generar_nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>
    <?php elseif (puedeVerNomina()): ?>
    <a href="ver_nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>
    <?php endif; ?>

    <?php if (puedeVerLiquidacion()): ?>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <?php endif; ?>
    <?php if (puedeVerVacaciones()): ?>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    <?php endif; ?>
    <?php if (puedeVerHorasExtra()): ?>
    <a href="horas_extras.php"><i class="ri-time-line"></i> Horas Extra</a>
    <?php endif; ?>
    
    <?php if (puedeListarEmpleados()): ?>
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <?php endif; ?>

    <?php if (puedeVerUsuarios()): ?>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <?php endif; ?>
    <?php if (puedeReportes()): ?>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
    <?php endif; ?>
    <?php if (esAdmin()): ?>
     <?php if (puedeVerBitacora()): ?>
     <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
     <?php endif; ?>
    <?php endif; ?>         
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email
    </a>
    
   
</a>

   
</aside>>

<!-- MAIN -->
<div class="main">

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?= $_SESSION['usuario'] ?></span> 
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

    <div class="top-menu">
       <?php if (puedeVerAsignaciones()): ?>
       <a href="asignaciones.php" class="top-button"><i class="ri-add-circle-line"></i> Asignaciones</a>
       <?php endif; ?>
       <?php if (puedeVerDeducciones()): ?>
       <a href="deducciones.php" class="top-button"><i class="ri-subtract-line"></i> Deducciónes</a>
       <?php endif; ?>
       <?php if (puedeGenerarNomina()): ?>
    <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
    <?php elseif (puedeVerNomina()): ?>
        <?php endif; ?>
       <?php if (puedeVerNomina()): ?>
       <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
       <?php endif; ?>
       <?php if (puedePagarNomina()): ?>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
       <?php endif; ?>
    
    </div>

<div class="contenido">
    <div class="card-container">

        <h2> Historial de Pagos</h2>

        <!-- BUSCADOR -->
        <input type="text" id="buscador" class="buscador" placeholder=" Buscar por ID, método, fecha, notas...">

        <div class="table-container">
            <table id="tablaPagos">
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>ID Nómina</th>
                        <th>Período</th>
                        <th>Fecha de Pago</th>
                        <th>Total Pagado</th>
                        <th>Método</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($res)) { ?>
                    <tr>
                        <td><?= $p['id_pago'] ?></td>
                        <td><?= $p['id_nomina'] ?></td>
                        <td><?= $p['fecha_inicio'] ?> / <?= $p['fecha_fin'] ?></td>
                        <td><?= $p['fecha_pago'] ?></td>
                        <td>Bs <?= number_format($p['total_pagado'],2) ?></td>
                        <td><?= $p['metodo'] ?></td>
                        <td><?= $p['notas'] ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</div>

<!-- BUSCADOR AUTOMÁTICO -->
<script>
document.getElementById('buscador').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaPagos tbody tr');

    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
});
</script>

</body>
</html>
