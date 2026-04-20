<?php
include("seguridad.php");

verificarSesion();

include "db.php";

$mensaje = "";

/* OBTENER CARGOS DESDE BASE DE DATOS */
$cargos = mysqli_query($conexion,"SELECT cargo_id, nombre_cargo FROM cargo");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = trim($_POST['name']);
    $usuario = trim($_POST['user']);
    $contraseña = password_hash($_POST['contraseña'], PASSWORD_BCRYPT);
    $cargo = (int)$_POST['cargo'];

    $sql = "INSERT INTO usuarios (nombre_apellido, usuario, clave, cargo_id) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("Error en consulta: " . $conexion->error);
    }

    $stmt->bind_param("sssi", $nombre, $usuario, $contraseña, $cargo);

    if ($stmt->execute()) {
    registrar_auditoria($conexion, 'CREAR', 'Usuarios', "Creó usuario '$usuario' con cargo_id $cargo");
    $mensaje = "Usuario registrado con éxito.";
    } else {
        $mensaje = "Error al registrar usuario.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Crear Usuario</title>

<link rel="stylesheet" href="../css/usuarios.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

<div class="sidebar-header">
<img src="../img/logo.png" class="logo">
<h3 class="system-title">KAO SHOP</h3>
</div>

<a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
<a href="generar_nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
<a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
<a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
<a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
<a href="listar_usuario.php" class="active"><i class="ri-user-settings-line"></i> Roles</a>
<a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
<?php if (esAdmin()): ?>
<a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
<?php endif; ?>
<a href="contactar.php"><i class="ri-mail-line"></i> Email</a>

</aside>

<!-- MAIN -->
<div class="main">

<header>
<h2><i class="ri-user-add-line"></i> Crear Usuario</h2>
<div>
<span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
<a href="cerrar_sesion.php">Cerrar sesión</a>
</div>
</header>

<div class="top-menu">
<a href="listar_usuario.php" class="top-button">
<i class="ri-file-list-2-line"></i> Lista de Usuarios
</a>
</div>

<div class="form-card">

<?php if ($mensaje): ?>
<p style="text-align:center;color:green;font-weight:bold;">
<?= htmlspecialchars($mensaje); ?>
</p>
<?php endif; ?>

<h2><i class="ri-user-settings-line"></i> Registro de Nuevo Usuario</h2>

<form action="" method="post">

<label>Nombre y Apellido</label>
<input type="text" name="name" required>

<label>Usuario</label>
<input type="text" name="user" required>

<label>Contraseña</label>
<input type="password" name="contraseña" required>

<label>Tipo de Usuario</label>

<select name="cargo" required>

<?php while($c = mysqli_fetch_assoc($cargos)){ ?>

<option value="<?= $c['cargo_id'] ?>">
<?= $c['nombre_cargo'] ?>
</option>

<?php } ?>

</select>

<button type="submit">
<i class="ri-save-3-line"></i> Guardar Usuario
</button>

<a href="administrador.php" class="cancel-btn">
<i class="ri-arrow-left-line"></i> Cancelar
</a>

</form>

</div>

</div>

</body>
</html>