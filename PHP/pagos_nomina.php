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
    <a href="administrador.php" >
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
        <a href="asignaciones.php" class="top-button">
            <i class="ri-add-circle-line"></i> Asignaciones
        </a>

        <a href="deducciones.php" class="top-button">
            <i class="ri-subtract-line"></i> Deducciones
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

    <h2> Pagos de Nómina</h2>

<table>
<thead>
    <tr>
        <th>ID Nómina</th>
        <th>Periodo</th>
        <th>Tipo</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
</thead>
<tbody>
<?php while ($n = mysqli_fetch_assoc($nominas)) { ?>
    <tr>
        <td>#<?= $n['id_nomina'] ?></td>
        <td>
            <i class="ri-calendar-line"></i> 
            <?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?>
        </td>
        <td><?= ucfirst($n['tipo']) ?></td>
        <td>
            <span style="padding: 4px 8px; border-radius: 4px; background: #eee; font-size: 12px; font-weight: bold;">
                <?= strtoupper($n['estado']) ?>
            </span>
        </td>
        <td>
            <a href="pagar_nomina.php?id=<?= $n['id_nomina'] ?>" class="btn-accion btn-pagar">
                <i class="ri-bank-card-line"></i> Registrar Pago
            </a>
        </td>
    </tr>
<?php } ?>
</tbody>
</table>

</body>
</html>

