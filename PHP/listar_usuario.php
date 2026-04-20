<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeListarUsuarios());

include("db.php");

$consulta = "SELECT u.id_usuario, u.nombre_apellido, u.usuario, c.nombre_cargo,
                    e.nombre AS emp_nombre, e.apellido AS emp_apellido
             FROM usuarios u
             LEFT JOIN cargo c ON u.cargo_id = c.cargo_id
             LEFT JOIN empleados e ON e.id = u.empleado_id
             WHERE u.activo = 1";


$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lista de Usuarios</title>

<link rel="stylesheet" href="../css/listar_usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>

<body>

<aside class="sidebar">

<div class="sidebar-header">
<img src="../img/logo.png" class="logo">
<h3 class="system-title">KAO SHOP</h3>
</div>

<a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
<a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
<a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
<a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
<a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
<a href="listar_usuario.php" class="active"><i class="ri-user-settings-line"></i> Roles</a>
<a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
<a href="contactar.php"><i class="ri-mail-line"></i> Email</a>

</aside>

<div class="main">

<header>
<h2>Panel de Administración - RRHH</h2>
<div>
<span>👤 <?php echo $_SESSION['usuario']; ?></span> |
<a href="cerrar_sesion.php">Cerrar sesión</a>
</div>
</header>

<div class="top-menu">
            <a href="registrar_empleado_usuario.php" class="top-button">
                <i class="ri-user-add-line"></i> Registrar Empleado
            </a>
        <a href="registrar_empleado_usuario.php?tipo=vincular" class="top-button">
            <i class="ri-links-line"></i> Vincular Existente
        </a>
</div>


<div class="contenido">

<h3>Usuarios Registrados</h3>

<table>

<tr>
<th>Nombre</th>
<th>Usuario</th>
<th>Cargo</th>
<th>Acciones</th>
</tr>

<?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>

<tr>

<td><?= $fila['nombre_apellido']; ?></td>

<td><?= $fila['usuario']; ?></td>

<td><?= $fila['nombre_cargo']; ?></td>

<td class="acciones">

<a class="btn editar" href="editar_usuario.php?id=<?= $fila['id_usuario']; ?>">
<i class="ri-edit-2-line"></i> Editar
</a>

<a class="btn eliminar"
href="eliminar_usuario.php?id=<?= $fila['id_usuario']; ?>"
onclick="return confirm('¿Eliminar usuario?');">

<i class="ri-delete-bin-6-line"></i> Eliminar

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>
</html>