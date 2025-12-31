<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// obtener nominas abiertas o cerradas (no pagadas)
$consulta = "SELECT * FROM nomina WHERE estado != 'pagada' ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagos de Nómina</title>
</head>
<body>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pre-Nómina</title>

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="../css/pagos_nomina.css">
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>

    <nav class="sidebar-menu">
        <a href="administrador.php" class="menu-item">
            <i class="ri-home-4-line"></i> Inicio
        </a>
        <a href="nomina.php" class="menu-item active">
            <i class="ri-money-dollar-circle-line"></i> Nómina
        </a>
        <a href="listar_empleados.php" class="menu-item">
            <i class="ri-team-line"></i> Empleados
        </a>
           <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
        <a href="usuarios.php" class="menu-item">
            <i class="ri-user-settings-line"></i> Usuarios
        </a>
        <a href="reportes.php" class="menu-item">
            <i class="ri-bar-chart-line"></i> Reportes
        </a>
        <a href="contactar.php">
         <i class="ri-mail-line"></i> Agendar entrevistas 
        </a>
    </nav>
</aside>

<!-- ===== MAIN ===== -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
   <div class="top-menu">
        <a href="crear_asignacion.php" class="top-button">
            <i class="ri-add-circle-line"></i> Crear Asignación
        </a>

        <a href="crear_deduccion.php" class="top-button">
            <i class="ri-subtract-line"></i> Crear Deducción
        </a>

        <a href="generar_nomina.php" class="top-button">
            <i class="ri-calculator-line"></i> Generar Nómina
        </a>

        <a href="ver_nomina.php" class="top-button">
            <i class="ri-file-list-line"></i> Ver Nóminas
        </a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>

        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>
    </div>

    <h2>💵 Pagos de Nómina</h2>

<table border="1" cellpadding="8">
<tr>
    <th>ID Nómina</th>
    <th>Periodo</th>
    <th>Tipo</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php while ($n = mysqli_fetch_assoc($nominas)) { ?>
<tr>
    <td><?= $n['id_nomina'] ?></td>
    <td><?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>
    <td><?= ucfirst($n['tipo']) ?></td>
    <td><?= $n['estado'] ?></td>
    <td>
        <a href="pagar_nomina.php?id=<?= $n['id_nomina'] ?>">💰 Registrar Pago</a>
    </td>
</tr>
<?php } ?>

</table>

</body>
</html>

