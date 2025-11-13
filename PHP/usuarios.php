<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include "db.php"; // Asegúrate que $conexion sea un objeto mysqli
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger los datos del formulario
    $nombre = trim($_POST['name']);
    $usuario = trim($_POST['user']);
    $contraseña = $_POST['contraseña'];
    $cargo = (int)$_POST['cargo']; // Aseguramos que sea un número entero

    // Consulta preparada corregida (sin coma extra)
    $sql = "INSERT INTO usuarios (nombre_apellido, usuario, clave, cargo_id) VALUES (?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        die("❌ Error al preparar la consulta: " . $conexion->error);
    }

    // Vincular parámetros: string, string, string, int
    $stmt->bind_param("sssi", $nombre, $usuario, $contraseña, $cargo);

    // Ejecutar la consulta
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
    <title>Panel del Administrador</title>
    <link rel="stylesheet" href="../css/usuarios.css">
    <style>
    
    </style>
</head>
<body>

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?php echo htmlspecialchars($_SESSION['usuario']); ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<nav class="menu">
    <a href="administrador.php">➖Inicio</a>
    <a href="nomina.php">💵 Nomina de empleados</a>
    <a href="formulario_para_registrar_empleado.php">🧑‍💼 Registrar Empleado</a>
    <a href="listar_empleados.php">📋 Listar Empleados</a>
    <a href="usuarios.php">👥 Gestionar Usuarios</a>
    <a href="cargos.php">🧰 Gestionar Cargos</a>
    <a href="">💸 Deducciones</a>
    <a href="">✔ Asignaciones</a>
    <a href="">📰 Reportes</a>
    
    
</nav>

<div class="login-container">
    <?php if ($mensaje): ?>
        <p><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <form action="" method="post" class="login-form">
        <h1>Crear un usuario nuevo</h1>
        <hr>
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

</body>
</html>
