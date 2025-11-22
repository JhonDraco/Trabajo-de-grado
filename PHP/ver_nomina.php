<?php
include("db.php");

// Ordenar por la fecha en que se creó la nómina
$consulta = "SELECT * FROM nomina ORDER BY fecha_creacion DESC";
$nominas = mysqli_query($conexion, $consulta);
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
<title>Ver Nóminas</title>
 <link rel="stylesheet" href="../css/ver_nomina.css">

</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
    <a href="reportes.php">Reportes</a>
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
       <a href="crear_asignacion.php"class="top-button"> Crear Asignación</a>
       <a href="crear_deduccion.php" class="top-button"> Crear Deducción</a>
       <a href="generar_nomina.php"class="top-button"> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"> Ver Nóminas</a>
    </div>

    <!-- AQUI DEBE IR LA TABLA  -->
    <h2>📄 Nóminas Generadas</h2>

    <div class="table-container">
        <table>
            <tr>
                <th>ID Nómina</th>
                <th>Período</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Creada por</th>
                <th>Fecha creación</th>
                <th>Acciones</th>
            </tr>

            <?php while ($n = mysqli_fetch_array($nominas)) { ?>
            <tr>
                <td><?= $n['id_nomina'] ?></td>
                <td><?= $n['fecha_inicio'] ?> / <?= $n['fecha_fin'] ?></td>
                <td><?= $n['tipo'] ?></td>
                <td><?= $n['estado'] ?></td>
                <td><?= $n['creada_por'] ?></td>
                <td><?= $n['fecha_creacion'] ?></td>
                <td>
                    <a href="ver_detalle_nomina.php?id=<?= $n['id_nomina'] ?>">Ver Detalle</a> | 
                    <a href="eliminar_nomina.php?id=<?= $n['id_nomina'] ?>" onclick="return confirm('¿Eliminar esta nómina?')">Eliminar</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

</div> <!-- CIERRE REAL DE MAIN -->

</body>

</html>
