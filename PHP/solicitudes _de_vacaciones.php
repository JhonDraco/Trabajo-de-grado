<?php


include("db.php");


/* ===========================
   DATOS PARA VISTA
=========================== */
$empleados = mysqli_query($conexion, "
    SELECT id, nombre, apellido 
    FROM empleados 
    WHERE estado='activo'
");

$vacaciones = mysqli_query($conexion, "
    SELECT v.*, e.nombre, e.apellido
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id = e.id
    ORDER BY v.creada_en DESC
");

?>

<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/solicitudes _de_vacaciones.css">
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
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php" class="active">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
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
        <a href="vacaciones.php" class="top-button">Gestion de vacasiones</a>

    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
     
<!-- LISTADO -->
<h3> Solicitudes</h3>
<table border="1" cellpadding="8">
<tr>
    <th>Empleado</th>
    <th>Periodo</th>
    <th>Días</th>
    <th>Estado</th>
    <th>Acciones</th>
</tr>

<?php while ($v = mysqli_fetch_assoc($vacaciones)) { ?>
<tr>
    <td><?= $v['nombre']." ".$v['apellido'] ?></td>
    <td><?= $v['fecha_inicio']." al ".$v['fecha_fin'] ?></td>
    <td><?= $v['dias_habiles'] ?></td>
    <td><?= ucfirst($v['estado']) ?></td>
<td>
    <div class="celda-acciones">
        <a href="aprobar_vacaciones.php?id=<?= $v['id_vacacion'] ?>" class="btn-accion btn-aprobar">
            <i class="ri-checkbox-circle-line"></i> Aprobar
        </a>

        <a href="rechazar_vacaciones.php?id=<?= $v['id_vacacion'] ?>" class="btn-accion btn-rechazar">
            <i class="ri-close-circle-line"></i> Rechazar
        </a>
    </div>
</td>
</tr>
<?php } ?>
</table>

    </div>
</div>

</body>
</html>
