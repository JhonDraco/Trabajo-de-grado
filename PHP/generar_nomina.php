<?php
session_start();
if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php"); 
    exit(); 
}

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo         = $_POST['tipo'];
    $creada_por   = $_SESSION['usuario'];

    // 1. Crear la nómina
    $sql = "INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por)
            VALUES ('$fecha_inicio', '$fecha_fin', '$tipo', '$creada_por')";
    mysqli_query($conexion, $sql);

    $id_nomina = mysqli_insert_id($conexion);

    // 2. Obtener tipos de deducción y asignación
    $deducciones = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
    $asignaciones = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");

    $arr_deducciones = [];
    while($d = mysqli_fetch_assoc($deducciones)){
        $arr_deducciones[] = $d;
    }

    $arr_asignaciones = [];
    while($a = mysqli_fetch_assoc($asignaciones)){
        $arr_asignaciones[] = $a;
    }

    // 3. Obtener empleados activos
    $empleados = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

    while ($emp = mysqli_fetch_assoc($empleados)) {

        $salario = floatval($emp['salario_base']);
        $total_asig = 0;
        $total_ded  = 0;

        foreach ($arr_asignaciones as $asig) {
            if ($asig['tipo'] == 'fijo') {
                $monto = floatval($asig['valor']);
            } else {
                $monto = $salario * (floatval($asig['valor']) / 100);
            }
            $total_asig += $monto;
        }

        foreach ($arr_deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje']) / 100);
            $total_ded += $monto;
        }

        $total_pagar = ($salario + $total_asig) - $total_ded;

        mysqli_query($conexion,
            "INSERT INTO detalle_nomina (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
             VALUES ($id_nomina, {$emp['id']}, $salario, $total_asig, $total_ded, $total_pagar)"
        );

        $id_detalle = mysqli_insert_id($conexion);

        foreach ($arr_asignaciones as $asig) {
            if ($asig['tipo'] == 'fijo') {
                $monto = floatval($asig['valor']);
            } else {
                $monto = $salario * (floatval($asig['valor']) / 100);
            }
            mysqli_query($conexion,
                "INSERT INTO detalle_asignacion (id_detalle, id_asignacion, monto)
                 VALUES ($id_detalle, {$asig['id_asignacion']}, $monto)"
            );
        }

        foreach ($arr_deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje']) / 100);
            mysqli_query($conexion,
                "INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto)
                 VALUES ($id_detalle, {$ded['id_tipo']}, $monto)"
            );
        }
    }

    header("Location: generar_nomina.php?ok=1&id=$id_nomina");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generar Nómina</title>
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
:root {
    --green-dark: #1f3a34;
    --green-mid: #2b4a42;
    --green-hover: #3f6f61;
    --white: #ffffff;
    --white-soft: #f7f7f7;
    --gray-light-text: #4a4f4e;
    --card-border: #e2e2e2;
    --shadow: 0 6px 18px rgba(0,0,0,0.15);
    --radius: 12px;
    --sidebar-width: 260px;
}

/* Reset */
* { box-sizing:border-box; margin:0; padding:0; font-family:Inter, Arial, sans-serif; }

body {
    display:flex;
    min-height:100vh;
    background: var(--white);
    color: var(--gray-light-text);
}

/* SIDEBAR */
.sidebar {
    width: var(--sidebar-width);
    background: linear-gradient(180deg, var(--green-dark), var(--green-mid));
    padding:30px 20px;
    color:white;
    box-shadow: var(--shadow);
    display:flex;
    flex-direction:column;
    border-radius: 0 16px 16px 0;
}

.sidebar h2 {
    text-align:center;
    margin-bottom:35px;
    font-size:22px;
    color:white;
}

.sidebar-menu {
    display:flex;
    flex-direction:column;
    gap:10px;
}

.menu-item {
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    color:white;
    text-decoration:none;
    font-size:15px;
    border-radius:10px;
    transition:0.25s;
    font-weight:500;
    position:relative;
}

.menu-item:hover {
    background:rgba(255,255,255,0.15);
    transform:translateX(6px);
}

.menu-item.active {
    background:rgba(0,0,0,0.35);
}

.menu-item.active::before {
    content:"";
    position:absolute;
    left:-10px;
    top:50%;
    transform:translateY(-50%);
    width:6px;
    height:28px;
    background:white;
    border-radius:4px;
}

/* MAIN */
.main {
    flex:1;
    display:flex;
    flex-direction:column;
    background: var(--white);
}

/* HEADER */
header {
    background:var(--white);
    padding:12px 20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow: var(--shadow);
}

header h2 {
    font-size:18px;
    color:var(--green-dark);
}

header a {
    color:var(--green-mid);
    text-decoration:none;
    font-weight:600;
    margin-left:8px;
}

/* TOP MENU */
.top-menu {
    background: var(--white-soft);
    padding:12px 20px;
    display:flex;
    gap:12px;
    box-shadow:var(--shadow);
    border-bottom:3px solid var(--green-mid);
    flex-wrap:wrap;
}

.top-button {
    padding:8px 16px;
    background:var(--green-mid);
    color:white;
    border-radius:8px;
    text-decoration:none;
    font-size:14px;
    font-weight:600;
    transition:0.2s;
}

.top-button:hover {
    background:var(--green-hover);
}

/* CONTENIDO */
.contenido {
    padding:24px;
    flex:1;
}

.contenido h2 {
    margin-bottom:20px;
    color:var(--green-mid);
    text-align:center;
}

/* FORMULARIO */
.nomina-container {
    display:flex;
    justify-content:center;
    margin-top:20px;
}

.nomina-box {
    background: var(--white-soft);
    padding:25px;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    width:420px;
}

.nomina-box h2 {
    color: var(--green-dark);
    margin-bottom:15px;
    text-align:center;
}

.nomina-box label {
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

.nomina-box input,
.nomina-box select {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:8px;
    margin-bottom:15px;
}

.nomina-box button {
    width:100%;
    padding:10px;
    background:var(--green-mid);
    color:white;
    border:none;
    border-radius:8px;
    font-weight:600;
    cursor:pointer;
}

.nomina-box button:hover {
    background:var(--green-hover);
}
</style>
</head>
<body>

<aside class="sidebar">
    <h2>RRHH Admin</h2>
    <nav class="sidebar-menu">
        <a href="administrador.php" class="menu-item">
            <i class="ri-home-4-line"></i> Inicio
        </a>
        <a href="nomina.php" class="menu-item active">
            <i class="ri-money-dollar-circle-line"></i> Nómina
        </a>
        <a href="listar_empleados.php" class="menu-item">
            <i class="ri-team-line"></i> Empleados
        </a>
        <a href="usuarios.php" class="menu-item">
            <i class="ri-user-settings-line"></i> Usuarios
        </a>
        <a href="reportes.php" class="menu-item">
            <i class="ri-bar-chart-line"></i> Reportes
        </a>
    </nav>
</aside>

<div class="main">
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="crear_asignacion.php" class="top-button"><i class="ri-add-circle-line"></i> Crear Asignación</a>
        <a href="crear_deduccion.php" class="top-button"><i class="ri-subtract-line"></i> Crear Deducción</a>
        <a href="generar_nomina.php" class="top-button"><i class="ri-file-list-line"></i> Generar Nómina</a>
        <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
    </div>

    <div class="contenido">
        <h2>🧾 Generar Nómina</h2>
        <?php if (isset($_GET['ok'])) { ?>
            <p style="color:green; text-align:center;">Nómina generada exitosamente. ID: <?= $_GET['id'] ?></p>
        <?php } ?>

        <div class="nomina-container">
            <div class="nomina-box">
                <h2>Formulario de Nómina</h2>
                <form method="post">
                    <label>Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" required>

                    <label>Fecha Fin:</label>
                    <input type="date" name="fecha_fin" required>

                    <label>Tipo de Nómina:</label>
                    <select name="tipo">
                        <option value="mensual">Mensual</option>
                        <option value="quincenal">Quincenal</option>
                        <option value="semanal">Semanal</option>
                    </select>

                    <button type="submit"><i class="ri-file-text-line"></i> Generar Nómina</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
