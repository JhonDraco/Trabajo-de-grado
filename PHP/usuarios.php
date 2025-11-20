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
<link rel="stylesheet" href="../css/usuarios.css">
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="listar_usuario.php">Usuarios</a>
    <a href="reportes.php">Reportes</a>
</aside>

<div class="main">

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="top-menu">
    <a href="listar_usuarios.php" class="top-button">Lista de Usuarios</a>
    <a href="usuarios.php" class="top-button">Crear Usuario</a>
</div>

<div class="login-container">
    <?php if ($mensaje): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <form action="" method="post" class="login-form">
        <h1>Crear un usuario nuevo</h1>
        <label for="name">Nombre y Apellido:</label>
        <input type="text" id="name" name="name" placeholder="Ingresar nombre y apellido" required>

        <label for="user">Usuario:</label>
        <input type="text" id="user" name="user" placeholder="Ingresa tu usuario" required>

        <label for="contraseña">Contraseña:</label>
        <input type="password" id="contraseña" name="contraseña" placeholder="Ingresa tu contraseña" required>

        <label for="cargo">Tipo de usuario:</label>
        <select name="cargo" id="cargo">
            <option value="1">Administrador</option>
            <option value="2">Trabajador</option>
        </select>

        <div class="buttons">
            <button type="submit">Guardar</button>
            <a href="administrador.php"><button type="button">Cancelar</button></a>
        </div>
    </form>
</div>

</div>
</body>
</html>
