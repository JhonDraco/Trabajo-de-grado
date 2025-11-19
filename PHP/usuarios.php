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
<style>
:root {
    --blue:#1e4c8f;
    --blue-dark:#123766;
    --blue-light:#4a90e2;
    --white:#ffffff;
    --gray:#f4f6fb;
    --shadow:0 8px 20px rgba(0,0,0,0.12);
    --radius:12px;
    --sidebar-width:220px;
    --text-dark:#1a1a1a;
}

/* Reset */
*{box-sizing:border-box; margin:0; padding:0; font-family:Inter, Arial, sans-serif;}
body{ display:flex; min-height:100vh; background:var(--gray); color:var(--text-dark); }

/* Sidebar vertical */
.sidebar{
    width:var(--sidebar-width);
    background:linear-gradient(180deg,var(--blue) 0%, var(--blue-dark) 100%);
    padding:22px;
    color:white;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    flex-shrink:0;
}
.sidebar h2{ font-size:18px; margin-bottom:20px; text-align:center; }
.sidebar a{
    display:block; padding:10px 12px; margin-bottom:8px;
    color:white; text-decoration:none; border-radius:8px; transition:0.2s;
}
.sidebar a:hover{ background:rgba(255,255,255,0.12); }
.sidebar a.active{ background:rgba(0,0,0,0.18); }

/* Main content */
.main{
    flex:1;
    display:flex;
    flex-direction:column;
    padding:20px;
    align-items:center; /* centrar horizontal */
}

/* Header */
header{
    background:var(--white);
    padding:12px 16px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:var(--shadow);
    border-radius:var(--radius);
    width:100%;
    margin-bottom:15px;
}
header h2{ font-size:16px; color:var(--blue-dark); }
header a{ color:var(--blue); text-decoration:none; font-weight:600; }

/* Top menu horizontal */
.top-menu{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}
.top-button{
    padding:8px 14px;
    background:var(--blue);
    color:white;
    border-radius:6px;
    text-decoration:none;
    font-size:13px;
    font-weight:600;
    transition:0.2s;
}
.top-button:hover{ background:var(--blue-dark); }

/* Formulario menos compacto */
.login-form{
    background:var(--white);
    padding:30px 25px; /* más espacio interno */
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    max-width:600px; /* más ancho */
    width:100%;
    display:flex;
    flex-direction:column;
    gap:14px; /* separación entre campos */
}

.login-form h1{
    text-align:center;
    color:var(--blue-dark);
    margin-bottom:20px;
    font-size:20px;
}

.login-form label{
    display:block;
    font-weight:600;
    margin-bottom:6px;
    font-size:14px;
}

.login-form input, .login-form select{
    width:100%;
    padding:10px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:14px;
}

.login-form .buttons{
    display:flex;
    gap:12px;
    justify-content:flex-start;
    flex-wrap:wrap;
    margin-top:8px;
}

.login-form button{
    background:var(--blue);
    color:white;
    border:none;
    padding:10px 16px; /* más grande */
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
    font-size:14px;
    transition:0.2s;
}

.login-form button:hover{ background:var(--blue-dark); }

.login-form a button{
    background:#ccc;
    color:#333;
    border:none;
}

.login-form a button:hover{ background:#999; }

.mensaje{
    padding:10px 14px;
    background:#dff0d8;
    color:#3c763d;
    border-radius:6px;
    margin-bottom:15px;
    border:1px solid #3c763d33;
    font-size:14px;
    text-align:center;
}

/* Responsive */
@media (max-width:880px){
    body{ flex-direction:column; }
    .sidebar{ width:100%; display:flex; overflow-x:auto; }
    .sidebar a{ flex-shrink:0; margin-right:10px; }
    .top-menu{ flex-wrap:wrap; justify-content:center; }
    .login-form{ max-width:100%; padding:20px; }
    .login-form button, .login-form a button{ width:100%; margin-bottom:8px; }
}
</style>
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="listar_usuario.php">Usuarios</a>
    <a href="#">Reportes</a>
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
