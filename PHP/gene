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
<link rel="stylesheet" href="../css/generar_nomina.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


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
