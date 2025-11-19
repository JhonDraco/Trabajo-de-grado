<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

if (!isset($_GET['id'])) {
    echo "ID de empleado no recibido.";
    exit();
}

$id = $_GET['id'];

$consulta = "SELECT * FROM empleados WHERE id = $id";
$resultado = mysqli_query($conexion, $consulta);
$empleado = mysqli_fetch_assoc($resultado);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $salario_base = $_POST['salario_base'];
    $estado = $_POST['estado'];

    $update = "
        UPDATE empleados SET
            cedula='$cedula',
            nombre='$nombre',
            apellido='$apellido',
            direccion='$direccion',
            telefono='$telefono',
            email='$email',
            fecha_ingreso='$fecha_ingreso',
            salario_base='$salario_base',
            estado='$estado'
        WHERE id=$id
    ";

    if (mysqli_query($conexion, $update)) {
        header("Location: listar_empleados.php");
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
<title>Editar Empleado</title>
<style>
:root {
    --blue:#1e4c8f;
    --blue-dark:#123766;
    --blue-light:#4a90e2;
    --white:#ffffff;
    --gray:#f4f6fb;
    --shadow:0 8px 20px rgba(0,0,0,0.12);
    --radius:12px;
    --text-dark:#1a1a1a;
    --sidebar-width:220px;
}

/* Reset */
*{box-sizing:border-box; margin:0; padding:0; font-family:Inter, Arial, sans-serif;}
body{ display:flex; min-height:100vh; background:var(--gray); color:var(--text-dark); }

/* ===== SIDEBAR ===== */
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

/* ===== MAIN CONTENT ===== */
.main{
    flex:1;
    display:flex;
    flex-direction:column;
    padding:20px;
    align-items: center; /* centrado horizontal */
}

/* Header */
header{
    background:var(--white);
    padding:10px 16px;
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
    margin-bottom:15px;
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

/* Formulario compacto */
.formulario{
    background:var(--white);
    padding:20px;
    border-radius:var(--radius);
    box-shadow:var(--shadow);
    max-width:500px;
    width:100%;
}

.formulario h2{
    text-align:center;
    color:var(--blue-dark);
    margin-bottom:15px;
    font-size:18px;
}

.formulario label{
    display:block;
    font-weight:600;
    margin-bottom:4px;
    font-size:13px;
}

.formulario input, 
.formulario select, 
.formulario textarea{
    width:100%;
    padding:8px;
    margin-bottom:12px;
    border-radius:6px;
    border:1px solid #ccc;
    font-size:13px;
}

.formulario textarea{ resize:vertical; min-height:50px; }

.formulario button{
    background:var(--blue);
    color:white;
    border:none;
    padding:8px 14px;
    border-radius:6px;
    font-weight:600;
    cursor:pointer;
    font-size:13px;
    margin-right:8px;
    transition:0.2s;
}

.formulario button:hover{ background:var(--blue-dark); }

.formulario a{
    display:inline-block;
    text-decoration:none;
    background:#ccc;
    color:#333;
    padding:8px 14px;
    border-radius:6px;
    font-weight:600;
    font-size:13px;
    margin-top:4px;
    text-align:center;
    transition:0.2s;
}

.formulario a:hover{ background:#999; }

/* Responsive */
@media (max-width:880px){
    body{ flex-direction:column; }
    .sidebar{ width:100%; display:flex; overflow-x:auto; }
    .sidebar a{ flex-shrink:0; margin-right:10px; }
    .top-menu{ flex-wrap:wrap; justify-content:center; }
    .formulario{ max-width:100%; padding:15px; }
    .formulario button, .formulario a{ width:100%; margin-bottom:8px; }
}
</style>
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <a href="administrador.php">Inicio</a>
    <a href="nomina.php">Nómina</a>
    <a href="listar_empleados.php" class="active">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
    <a href="#">Reportes</a>
</aside>

<div class="main">

<header>
    <h2>Editar Empleado</h2>
    <div>
        <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="top-menu">
    <a href="listar_empleados.php" class="top-button">Lista de Empleados</a>
    <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
</div>

<div class="formulario">
    <form method="POST">
        <label>Cédula:</label>
        <input type="text" name="cedula" value="<?php echo $empleado['cedula']; ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo $empleado['nombre']; ?>">

        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?php echo $empleado['apellido']; ?>">

        <label>Dirección:</label>
        <input type="text" name="direccion" value="<?php echo $empleado['direccion']; ?>">

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?php echo $empleado['telefono']; ?>">

        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $empleado['email']; ?>">

        <label>Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" value="<?php echo $empleado['fecha_ingreso']; ?>">

        <label>Salario Base:</label>
        <input type="text" name="salario_base" value="<?php echo $empleado['salario_base']; ?>">

        <label>Estado:</label>
        <select name="estado">
            <option value="Activo" <?php if ($empleado['estado'] == 'Activo') echo 'selected'; ?>>Activo</option>
            <option value="Inactivo" <?php if ($empleado['estado'] == 'Inactivo') echo 'selected'; ?>>Inactivo</option>
        </select>

        <button type="submit">Guardar Cambios</button>
        <a href="listar_empleados.php">Cancelar</a>
    </form>
</div>

</div>
</body>
</html>

<?php
mysqli_close($conexion);
?>
