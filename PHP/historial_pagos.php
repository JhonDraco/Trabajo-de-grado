<?php
session_start(); // ⚠ Importante iniciar la sesión
include("db.php");

$sql = "SELECT p.*, n.fecha_inicio, n.fecha_fin 
        FROM pagos p 
        JOIN nomina n ON p.id_nomina = n.id_nomina
        ORDER BY fecha_pago DESC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Historial de Pagos</title>

<!-- CSS -->
<link rel="stylesheet" href="../css/historial_de_pagos.css">

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

    <nav class="sidebar-menu">
        <a href="administrador.php" class="menu-item"><i class="ri-home-4-line"></i> Inicio</a>
        <a href="nomina.php" class="menu-item active"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
        <a href="listar_empleados.php" class="menu-item"><i class="ri-team-line"></i> Empleados</a>
        <a href="usuarios.php" class="menu-item"><i class="ri-user-settings-line"></i> Usuarios</a>
        <a href="reportes.php" class="menu-item"><i class="ri-bar-chart-line"></i> Reportes</a>
    </nav>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php"><i class="ri-logout-box-line"></i> Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
        <a href="crear_asignacion.php" class="top-button"><i class="ri-add-circle-line"></i> Crear Asignación</a>
        <a href="crear_deduccion.php" class="top-button"><i class="ri-subtract-line"></i> Crear Deducción</a>
        <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
        <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
        <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Historial de Pagos</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <div class="card-container">
            <h2>📜 Historial de Pagos</h2>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>ID Nómina</th>
                            <th>Periodo</th>
                            <th>Fecha de Pago</th>
                            <th>Total Pagado</th>
                            <th>Método</th>
                            <th>Notas</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($p = mysqli_fetch_assoc($res)) { ?>
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

</body>
</html>
