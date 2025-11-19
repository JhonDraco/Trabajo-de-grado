


<?php
include "db.php"; // Conexión a la base de datos
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $salario_base = $_POST['salario_base'];

    $stmt = $conexion->prepare("INSERT INTO empleados 
        (cedula, nombre, apellido, direccion, telefono, email, fecha_ingreso, salario_base) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("❌ Error al preparar la consulta: " . $conexion->error);
    }
    $stmt->bind_param("sssssssd", $cedula, $nombre, $apellido, $direccion, $telefono, $email, $fecha_ingreso, $salario_base);

    if ($stmt->execute()) {
        $mensaje = "✅ Empleado registrado con éxito.";
    } else {
        $mensaje = "❌ Error al registrar el empleado: " . $stmt->error;
    }
    $stmt->close();
}

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
<style>
    :root {
        --blue:#1e4c8f;
        --blue-dark:#123766;
        --blue-light:#4a90e2;
        --white:#ffffff;
        --gray:#f4f6fb;
        --shadow:0 8px 20px rgba(0,0,0,0.12);
        --radius:12px;
        --sidebar-width:260px;
        --text-dark:#1a1a1a;
        --text-muted:#6b7685;
        --table-bg:#ffffff;
        --table-header-bg:var(--blue);
        --table-header-color:#fff;
        --table-row-hover:#f1f6ff;
    }

    *{box-sizing:border-box; margin:0; padding:0; font-family:Inter, Arial, sans-serif;}
    body{
        display:flex;
        min-height:100vh;
        background:var(--gray);
        color:var(--text-dark);
    }

    /* ===== SIDEBAR ===== */
    .sidebar{
        width:var(--sidebar-width);
        background:linear-gradient(180deg,var(--blue) 0%, var(--blue-dark) 100%);
        padding:22px;
        color:white;
        box-shadow:var(--shadow);
    }
    .sidebar h2{ font-size:20px; margin-bottom:20px; text-align:center; }
    .sidebar a{
        display:block; padding:12px; margin-bottom:10px;
        color:white; text-decoration:none; border-radius:10px; transition:0.2s;
    }
    .sidebar a:hover{ background:rgba(255,255,255,0.12); transform:translateX(4px); }
    .sidebar a.active{ background:rgba(0,0,0,0.18); }

    .main{ flex:1; display:flex; flex-direction:column; padding:20px; }

    /* ===== TOP MENU HORIZONTAL ===== */
    .top-menu{
        background:var(--white);
        padding:12px 20px;
        display:flex; gap:12px;
        box-shadow:var(--shadow);
        border-bottom:3px solid var(--blue);
        flex-wrap:wrap;
        margin-bottom:20px;
    }
    .top-button{
        padding:8px 16px;
        background:var(--blue);
        color:white;
        border-radius:8px;
        text-decoration:none;
        font-size:14px;
        font-weight:600;
        transition:0.2s;
    }
    .top-button:hover{ background:var(--blue-light); }

    /* ===== HEADER ===== */
    header{
        background:var(--white);
        padding:12px 20px;
        display:flex;
        justify-content:space-between;
        align-items:center;
        box-shadow:var(--shadow);
        margin-bottom:20px;
    }
    header h2{ font-size:18px; color:var(--blue-dark); }
    header a{ color:var(--blue); text-decoration:none; font-weight:600; margin-left:8px; }

    /* ===== TABLA ESTILIZADA ===== */
    table{
        width:100%;
        border-collapse:collapse;
        background:var(--table-bg);
        border-radius:var(--radius);
        overflow:hidden;
        box-shadow:var(--shadow);
        margin-bottom:40px;
    }
    th, td{
        padding:12px 15px;
        text-align:left;
    }
    th{
        background:var(--table-header-bg);
        color:var(--table-header-color);
        font-weight:600;
        text-transform:uppercase;
        font-size:14px;
    }
    tr:nth-child(even){ background:#f9faff; }
    tr:hover{ background:var(--table-row-hover); }

    /* ===== BOTONES ACCIONES ===== */
    .btn{
        padding:6px 12px;
        border-radius:8px;
        text-decoration:none;
        color:white;
        font-size:13px;
        margin-right:5px;
        transition:0.2s;
    }
    .editar{ background:#4a90e2; }
    .editar:hover{ background:#1e4c8f; }
    .eliminar{ background:#e74c3c; }
    .eliminar:hover{ background:#c0392b; }

    /* ===== RESPONSIVE ===== */
    @media (max-width:880px){
        body{ flex-direction:column; }
        .sidebar{ width:100%; display:flex; overflow-x:auto; }
        .sidebar a{ flex-shrink:0; margin-right:10px; }
        .top-menu{ justify-content:flex-start; overflow-x:auto; }
        table, th, td{ font-size:12px; }
        .top-button{ font-size:12px; padding:6px 12px; }
    }


    form {
    background: var(--white);
    padding: 15px 20px; /* menos espacio interno */
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    max-width: 500px; /* más estrecho */
    width: 100%;
    margin: 20px auto;
    display: flex;
    flex-direction: column;
}

form h2 {
    color: var(--blue-dark);
    margin-bottom: 15px;
    text-align: center;
    font-size: 18px; /* tamaño más reducido */
}

form label {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 13px;
}

form input, form textarea {
    padding: 8px;
    margin-bottom: 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 13px;
    width: 100%;
}

form textarea { resize: vertical; min-height: 50px; }

form button {
    background: var(--blue);
    color: white;
    border: none;
    padding: 8px 14px; /* más pequeño */
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    font-size: 13px;
    margin-right: 8px;
    transition: 0.2s;
}

form button:hover {
    background: var(--blue-dark);
}

form a {
    display: inline-block;
    text-decoration: none;
    background: #ccc;
    color: #333;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
    margin-top: 4px;
    text-align: center;
    transition: 0.2s;
}

form a:hover { background: #999; }

.mensaje{
    padding: 8px 12px;
    background: #dff0d8;
    color: #3c763d;
    border-radius: 6px;
    margin-bottom: 15px;
    border: 1px solid #3c763d33;
    font-size: 13px;
    text-align: center;
}

/* Responsive */
@media (max-width: 880px){
    form {
        padding: 15px;
    }
    form button, form a {
        width: 100%;
        margin-bottom: 8px;
    }
}

</style>
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
    <a href="#">Reportes</a>
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
    <a href="listar_empleados.php" class="top-button">lista de empleados</a>    
    <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
    </div>


    <form method="POST">
        <label for="cedula">Cédula:</label>
        <input type="text" name="cedula" id="cedula" required>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" required>

        <label for="direccion">Dirección:</label>
        <textarea name="direccion" id="direccion"></textarea>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono">

        <label for="email">Email:</label>
        <input type="email" name="email" id="email">

        <label for="fecha_ingreso">Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" required>    

        <label for="salario_base">Salario base:</label>
        <input type="number" step="0.01" name="salario_base" id="salario_base" required>

        <button type="submit">Enviar</button>
        <a href="listar_empleados.php">Volver</a>
    </form>
</div>

</div>
</body>
</html>


