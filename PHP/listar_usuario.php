<?php
include("db.php");

$consulta = "SELECT id_usuario, nombre_apellido, usuario, cargo_id FROM usuarios";
$resultado = mysqli_query($conexion, $consulta);

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
<link rel="stylesheet" href="../css/listar_usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

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

    
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados

    <a href="listar_usuario.php"  class="active">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email 
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

    <!-- TOP MENU -->
    <div class="top-menu">
        <a href="usuarios.php" class="top-button"><i class="ri-user-add-line"></i> Registrar usuario</a>
       
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <h3>Usuarios Registrados</h3>

        <table>
            <tr>
                <th>Nombre y Apellido</th>
                <th>Usuario</th>
                <th>Cargo</th>
                <th>Acciones</th>
            </tr>

            <?php 
$cargos = [
    1 => "Administrador",
    2 => "Empleado"
];
?>

<?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
<tr>
    <td><?php echo $fila['nombre_apellido']; ?></td>
    <td><?php echo $fila['usuario']; ?></td>
    <td><?php echo $cargos[$fila['cargo_id']] ?? "Desconocido"; ?></td>
    <td class="acciones">
        <a class="btn editar" href="editar_usuario.php?id=<?php echo $fila['id_usuario']; ?>">
            <i class="ri-edit-2-line"></i> Editar
        </a>
        <a class="btn eliminar" href="eliminar_usuario.php?id=<?php echo $fila['id_usuario']; ?>" onclick="return confirm('¿Eliminar usuario?');">
            <i class="ri-delete-bin-6-line"></i> Eliminar
        </a>
    </td>
</tr>
<?php } ?>

        </table>

    </div> <!-- FIN contenido -->

</div> <!-- FIN main -->

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
</body>
</html>
