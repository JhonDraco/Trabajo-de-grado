<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include "db.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['name']);
    $usuario = trim($_POST['user']);
    $contraseña = $_POST['contraseña'];
    $cargo = (int)$_POST['cargo'];

    $sql = "INSERT INTO usuarios (nombre_apellido, usuario, clave, cargo_id) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("❌ Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("sssi", $nombre, $usuario, $contraseña, $cargo);

    if ($stmt->execute()) {
        $mensaje = "✅ Usuario registrado con éxito.";
    } else {
        $mensaje = "❌ Error al registrar el usuario: " . $stmt->error;
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

<!-- ICONOS REMIX ICON -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

<!-- TU CSS GENERAL -->
<link rel="stylesheet" href="../css/usuarios.css">

<style>
    

</style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php" >
        <i class="ri-team-line"></i> Empleados
    </a>
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

<!-- MAIN -->
<div class="main">

<!-- HEADER -->
<header>
    <h2><i class="ri-user-add-line"></i> Crear Usuario</h2>
    <div>
        <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<!-- TOP MENU -->
<div class="top-menu">
    <a href="listar_usuario.php" class="top-button"><i class="ri-file-list-2-line"></i> Lista de Usuarios</a>
   
</div>

<!-- FORMULARIO -->
<div class="form-card">

    <?php if ($mensaje): ?>
        <p style="text-align:center; color:green; font-weight:bold;">
            <?= htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <h2><i class="ri-user-settings-line"></i> Registro de Nuevo Usuario</h2>

    <form action="" method="post">
        
        <label>Nombre y Apellido</label>
        <input type="text" id="name" name="name" placeholder="Ingresar nombre y apellido" required>

        <label>Usuario</label>
        <input type="text" id="user" name="user" placeholder="Ingresa el usuario" required>

        <label>Contraseña</label>
        <input type="password" id="contraseña" name="contraseña" placeholder="Ingresa la contraseña" required>

        <label>Tipo de Usuario</label>
        <select name="cargo" id="cargo">
            <option value="1">Administrador</option>
            <option value="2">Trabajador</option>
        </select>

        <button type="submit"><i class="ri-save-3-line"></i> Guardar Usuario</button>

        <a href="administrador.php" class="cancel-btn"><i class="ri-arrow-left-line"></i> Cancelar</a>

    </form>

</div>

</div>
</body>
</html>
