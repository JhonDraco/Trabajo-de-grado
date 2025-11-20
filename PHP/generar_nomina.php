<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// PROCESAR FORMULARIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo = $_POST['tipo'];

    mysqli_query($conexion, "INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por) 
                             VALUES ('$fecha_inicio','$fecha_fin','$tipo','{$_SESSION['usuario']}')");
    $id_nomina = mysqli_insert_id($conexion);

     $deducciones = [];
    $res = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
    while($r = mysqli_fetch_assoc($res)) $deducciones[] = $r;

    $asignaciones = [];
    $res2 = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
    while($r2 = mysqli_fetch_assoc($res2)) $asignaciones[] = $r2;

   $emps = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

    while($emp = mysqli_fetch_assoc($emps)) {
        $salario = floatval($emp['salario_base']);
        $total_asig = 0.0;
        $total_ded = 0.0;

       foreach($asignaciones as $asig) {
            $monto = ($asig['tipo'] === 'fijo') ? floatval($asig['valor'])
                                                : $salario * (floatval($asig['valor'])/100);
            $total_asig += $monto;
        }

    foreach($deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje'])/100);
            $total_ded += $monto;
        }

        $total_pagar = ($salario + $total_asig) - $total_ded;

        mysqli_query($conexion, "INSERT INTO detalle_nomina 
                                (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
                                VALUES ($id_nomina, {$emp['id']}, $salario, $total_asig, $total_ded, $total_pagar)");

        $id_det = mysqli_insert_id($conexion);

       foreach($asignaciones as $asig) {
            $monto = ($asig['tipo'] === 'fijo') ? floatval($asig['valor'])
                                                : $salario * (floatval($asig['valor'])/100);
            mysqli_query($conexion, "INSERT INTO detalle_asignacion 
                                    (id_detalle, id_asignacion, monto) 
                                    VALUES ($id_det, {$asig['id_asignacion']}, $monto)");
        }

        foreach($deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje'])/100);
            mysqli_query($conexion, "INSERT INTO detalle_deduccion 
                                    (id_detalle, id_tipo, monto) 
                                    VALUES ($id_det, {$ded['id_tipo']}, $monto)");
        }
    }

    header("Location: generar_nomina.php?ok=1&id=$id_nomina");
    exit();
}?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>

  <link rel="stylesheet" href="../css/generar_nomina.css">
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">

    <h2>RRHH Admin</h2>
    <a href="administrador.php" class="active">Inicio</a>
    <a href="nomina.php">Nómina </a>
    <a href="listar_empleados.php">Empleados</a>
    <a href="usuarios.php">Usuarios</a>
      <a href="">Reportes</a>
</aside>

<!-- MAIN -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
    <div class="top-menu">
       <a href="crear_asignacion.php"class="top-button"> Crear Asignación</a>
       <a href="crear_deduccion.php" class="top-button"> Crear Deducción</a>
       <a href="generar_nomina.php"class="top-button"> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"> Ver Nóminas</a>
    </div>

    <!-- FORMULARIO CENTRADO -->
    <div class="nomina-container">
        <div class="nomina-box">

            <h2>Generar Nómina</h2>

            <?php 
                if (isset($_GET['ok'])) 
                    echo "<p style='color:green; font-weight:600;'>Nómina generada: ID " . intval($_GET['id']) . "</p>"; 
            ?>

            <form method="post">
                <label>Fecha inicio:
                    <input type="date" name="fecha_inicio" required>
                </label>

                <label>Fecha fin:
                    <input type="date" name="fecha_fin" required>
                </label>

                <label>Tipo:
                    <select name="tipo">
                        <option value="mensual">Mensual</option>
                        <option value="quincenal">Quincenal</option>
                        <option value="semanal">Semanal</option>
                    </select>
                </label>

                <button type="submit">Generar</button>
            </form>

        </div>
    </div>

</div> <!-- CIERRE CORRECTO DE MAIN -->

</body>
</html>
