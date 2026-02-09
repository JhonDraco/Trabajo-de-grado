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
<link rel="stylesheet" href="../css/administrador.css">
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
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php"class="active">
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
       <h2>Gestionar Asignaciones</h2>

<form method="post">
    <label>Nombre: <input name="nombre" required></label><br>
    <label>Tipo:
        <select name="tipo">
            <option value="fijo">Fijo</option>
            <option value="porcentaje">Porcentaje</option>
        </select>
    </label><br>
    <label>Valor (monto o %): <input name="valor" step="0.01" value="0.00"></label><br>
    <label>Descripción:<br><textarea name="descripcion"></textarea></label><br>
    <button type="submit">Guardar</button>
</form>

<h3>Lista</h3>
<table border="1" cellpadding="6">
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

</body>
</html>

