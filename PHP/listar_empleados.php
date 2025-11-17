<?php
include("db.php"); // incluye la conexión

// Consulta SQL
$consulta = "SELECT id, cedula, nombre, apellido, email, telefono FROM empleados";

// Ejecutar consulta
$resultado = mysqli_query($conexion, $consulta);

// Verificar errores
if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
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
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
    <a href="listar_empleados.php" class="top-button">lista de empleados</a>    
    <a href="formulario_para_registrar_empleado.php" class="top-button">Registrar Empleado</a>
    </div>

    <h2>Lista de Empleados Registrados</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>

        <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
            <tr>
                <td><?php echo $fila['id']; ?></td>
                <td><?php echo $fila['cedula']; ?></td>
                <td><?php echo $fila['nombre']; ?></td>
                <td><?php echo $fila['apellido']; ?></td>
                <td><?php echo $fila['email']; ?></td>
                <td><?php echo $fila['telefono']; ?></td>
                <td class="acciones">
                    <a class="btn editar" href="editar_empleado.php?id=<?php echo $fila['id']; ?>">Editar</a>
                    <a class="btn eliminar" href="eliminar_empleado.php?id=<?php echo $fila['id']; ?>" onclick="return confirm('¿Eliminar empleado?');">Eliminar</a>
                </td>
            </tr>
        <?php } ?>
    </table>

</div>

</body>
</html>

<?php
mysqli_free_result($resultado);
mysqli_close($conexion);
?>
