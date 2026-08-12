<?php
include("seguridad.php");
include("db.php");

verificarSesion();
bloquearSiNo(puedeCambiarRoles());

if (!isset($_GET['id'])) {
    echo "ID de usuario no recibido.";
    exit();
}

$id = intval($_GET['id']);

$consulta = "SELECT * FROM usuarios WHERE id_usuario = $id";
$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

$cargo = mysqli_query($conexion, "SELECT * FROM cargo");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_apellido = mysqli_real_escape_string($conexion, $_POST['nombre_apellido']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $cargo_id = intval($_POST['cargo_id']);

    // Solo se re-hashea la clave si el usuario escribió una nueva.
    // Antes esto rehasheaba el hash ya guardado en cada edición, dejando
    // la cuenta sin poder iniciar sesión con la clave real.
    $clave_sql = "";
    if (!empty($_POST['clave'])) {
        $clave_nueva = password_hash($_POST['clave'], PASSWORD_BCRYPT);
        $clave_sql = ", clave = '$clave_nueva'";
    }

    $update = "
    UPDATE usuarios SET
    usuario = '$usuario',
    nombre_apellido = '$nombre_apellido',
    cargo_id = $cargo_id
    $clave_sql
    WHERE id_usuario = $id
    ";

    if (mysqli_query($conexion, $update)) {

        registrar_auditoria(
            $conexion,
            'EDITAR',
            'Usuarios',
            "Editó usuario ID $id"
        );

        header("Location: listar_usuario.php");
        exit();

    } else {
        echo "Error al actualizar: " . mysqli_error($conexion);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/editar_usuario.css">
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
    <a href="administrador.php">
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
    <a href="liquidacion.php">
        <i class="ri-ball-pen-line"></i> Liquidacion
    </a>
    <?php endif; ?>

    <?php if (puedeVerVacaciones()): ?>
    <a href="vacaciones.php">
        <i class="ri-sun-line"></i> Vacaciones
    </a>
    <?php endif; ?>
    <?php if (puedeVerHorasExtra()): ?>
    <a href="horas_extras.php"><i class="ri-time-line"></i> Horas Extra</a>
    <?php endif; ?>

    <?php if (puedeListarEmpleados()): ?>
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <?php endif; ?>

    <a href="listar_usuario.php"  class="active">
        <i class="ri-user-settings-line"></i> Roles
    </a>

    <?php if (puedeReportes()): ?>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
    <?php endif; ?>
    <?php if (puedeVerBitacora()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
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

    <!-- TOP MENU HORIZONTAL -->
    <div class="top-menu">
      
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <div class="form-card">
            <h2>Editar Usuario</h2>
    <form method="POST">
        <label><i class="ri-user-line"></i> Nombre y Apellido:</label>
        <input type="text" name="nombre_apellido"
        value="<?php echo htmlspecialchars($empleado['nombre_apellido']); ?>">

        <label><i class="ri-id-card-line"></i> Usuario:</label>
        <input type="text" name="usuario" value="<?php echo htmlspecialchars($empleado['usuario']); ?>">

        <label><i class="ri-user-line"></i> Nueva Contraseña:</label>
        <input type="password" name="clave" placeholder="Dejar en blanco para no cambiarla">


            <select name="cargo_id">
            <?php while($c = mysqli_fetch_assoc($cargo)): ?>

            <option value="<?= $c['cargo_id'] ?>" <?= $c['cargo_id'] == $empleado['cargo_id'] ? 'selected' : '' ?>>

            <?= htmlspecialchars($c['nombre_cargo']) ?>

            </option>

            <?php endwhile; ?>

            </select>
            
        <div class="acciones">
            <button type="submit"><i class="ri-save-line"></i> Guardar Cambios</button>
            <a href="listar_usuario.php" class="cancel-btn"></i> Cancelar</a>
        </div>

    </form>
</div>



        

    </div>
</div>

</body>
</html>
