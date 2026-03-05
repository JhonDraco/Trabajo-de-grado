<?php

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo = ($_POST['tipo'] === 'porcentaje') ? 'porcentaje' : 'fijo';
    $valor = floatval($_POST['valor']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    mysqli_query($conexion, "INSERT INTO tipo_asignacion (nombre, tipo, valor, descripcion) VALUES ('$nombre','$tipo',$valor,'$desc')");
    header("Location: asignaciones.php");
    exit();
}

$res = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/asignaciones.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


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
    <a href="nomina.php"class="active">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>

    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email
    </a>
    
   
</a>

   
</aside>


<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU HORIZONTAL -->
    <div class="top-menu">
       <a href="asignaciones.php" class="top-button"><i class="ri-add-circle-line"></i> Asignaciones</a>
       <a href="deducciones.php" class="top-button"><i class="ri-subtract-line"></i> Deducciónes</a>
       <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>

    </div>

    <!-- CONTENIDO -->
<div class="contenido">
    <h3><i class="ri-add-box-line"></i> Nueva Asignación</h3>

    <div class="form-container-compact">
        <form method="post" class="form-grid">
            <div class="form-group-compact">
                <label>Nombre</label>
                <input type="text" name="nombre" placeholder="Nombre de asignación" required>
            </div>

            <div class="form-group-compact">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="fijo">Fijo (Bs)</option>
                    <option value="porcentaje">Porcentaje (%)</option>
                </select>
            </div>

            <div class="form-group-compact">
                <label>Valor</label>
                <input type="number" name="valor" step="0.01" value="0.00" required>
            </div>

            <div class="form-group-compact">
                <label>Descripción corta</label>
                <input type="text" name="descripcion" placeholder="Opcional...">
            </div>

            <button type="submit" class="btn-guardar-compact" style="grid-column: span 1;">
                <i class="ri-save-3-line"></i> Guardar
            </button>
        </form>
    </div>
    <h3>Lista de Asignaciones</h3>
    <table border="1" cellpadding="5">
<tr><th>Nombre</th><th>Tipo</th><th>Valor</th><th>Acción</th></tr>
<?php while($d = mysqli_fetch_assoc($res)) { ?>
<tr>
  <td><?=htmlspecialchars($d['nombre'])?></td>
  <td><?= $d['tipo'] ?></td>
  <td><?= number_format($d['valor'],2) ?><?= $d['tipo']=='porcentaje' ? '%' : '' ?></td>
  <td><a href="eliminar_asignacion.php?id=<?=$d['id_asignacion']?>" onclick="return confirm('Eliminar?')">Eliminar</a></td>
</tr>
<?php } ?>
</table>
    </div>


</div>

</body>
</html>

