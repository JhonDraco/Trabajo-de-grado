<?php
session_start();
if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php"); 
    exit(); 
}

include("db.php");

/* --------------------------------------------------------
   1. CALCULAR FECHA AUTOMÁTICA DE PERÍODO (SEMANA ACTUAL)
---------------------------------------------------------*/
$hoy = date("Y-m-d");
$lunes = date("Y-m-d", strtotime('monday this week'));
$domingo = date("Y-m-d", strtotime('sunday this week'));

/* --------------------------------------------------------
   2. SI SE AJUSTA EL PERÍODO, RECALCULAR PRE-NÓMINA
---------------------------------------------------------*/
$fecha_inicio = $_GET['inicio'] ?? $lunes;
$fecha_fin     = $_GET['fin']     ?? $domingo;

/* --------------------------------------------------------
   3. OBTENER SIGUIENTE NÚMERO DE NÓMINA
---------------------------------------------------------*/
$res_nom = mysqli_query($conexion, "SELECT MAX(id_nomina) AS maximo FROM nomina");
$data_nom = mysqli_fetch_assoc($res_nom);
$siguiente_nomina = $data_nom['maximo'] + 1;

/* --------------------------------------------------------
   4. CARGAR TABLAS NECESARIAS
---------------------------------------------------------*/
$empleados      = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");
$asignaciones   = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
$deducciones    = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");

/* Convertir a arrays para reusar */
$arr_asig = [];
while ($a = mysqli_fetch_assoc($asignaciones)) $arr_asig[] = $a;

$arr_ded = [];
while ($d = mysqli_fetch_assoc($deducciones)) $arr_ded[] = $d;

/* --------------------------------------------------------
   5. CALCULAR PRE-NÓMINA COMPLETA
---------------------------------------------------------*/
$lista_empleados = [];
$total_general_asig = 0;
$total_general_ded  = 0;
$total_general_pagar = 0;

while ($emp = mysqli_fetch_assoc($empleados)) {

    $salario = floatval($emp['salario_base']);
    $total_asig = 0;
    $total_ded  = 0;

    // Cálculo de asignaciones
    foreach ($arr_asig as $asig) {
        $monto = ($asig['tipo'] == 'fijo')
            ? floatval($asig['valor'])
            : $salario * (floatval($asig['valor']) / 100);

        $total_asig += $monto;
    }

    // Cálculo de deducciones
    foreach ($arr_ded as $ded) {
        $monto = $salario * (floatval($ded['porcentaje']) / 100);
        $total_ded += $monto;
    }

    $total_pago = ($salario + $total_asig) - $total_ded;

    $lista_empleados[] = [
        "id"        => $emp['id'],
        "nombre"    => $emp['nombre'] . " " . $emp['apellido'],
        "salario"   => $salario,
        "asig"      => $total_asig,
        "ded"       => $total_ded,
        "pagar"     => $total_pago,
    ];

    $total_general_asig += $total_asig;
    $total_general_ded  += $total_ded;
    $total_general_pagar += $total_pago;
}

/* --------------------------------------------------------
   6. SI EL USUARIO PRESIONA "GENERAR NOMINA"
---------------------------------------------------------*/
if (isset($_POST['generar_nomina'])) {

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo         = $_POST['tipo'];
    $creada_por   = $_SESSION['usuario'];

    // Crear la nómina
    mysqli_query($conexion,
        "INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por)
         VALUES ('$fecha_inicio', '$fecha_fin', '$tipo', '$creada_por')"
    );

    $id_nomina = mysqli_insert_id($conexion);

    // Insertar detalle por empleado
    foreach ($lista_empleados as $emp) {

        mysqli_query($conexion,
            "INSERT INTO detalle_nomina
            (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
            VALUES (
                $id_nomina,
                {$emp['id']},
                {$emp['salario']},
                {$emp['asig']},
                {$emp['ded']},
                {$emp['pagar']}
            )"
        );

        $id_detalle = mysqli_insert_id($conexion);

        // Detalle asignación
        foreach ($arr_asig as $asig){
            $monto = ($asig['tipo']=='fijo') ? $asig['valor'] : $emp['salario'] * ($asig['valor']/100);
            mysqli_query($conexion,
                "INSERT INTO detalle_asignacion (id_detalle, id_asignacion, monto)
                 VALUES ($id_detalle, {$asig['id_asignacion']}, $monto)"
            );
        }

        // Detalle deducción
        foreach ($arr_ded as $ded){
            $monto = $emp['salario'] * ($ded['porcentaje']/100);
            mysqli_query($conexion,
                "INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto)
                 VALUES ($id_detalle, {$ded['id_tipo']}, $monto)"
            );
        }
    }

    header("Location: ver_nomina.php?id=$id_nomina");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pre-Nómina</title>

<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<link rel="stylesheet" href="../css/generar_nomina.css">
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>

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

<!-- ===== MAIN ===== -->
<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?= $_SESSION['usuario'] ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU -->
   <div class="top-menu">
        <a href="crear_asignacion.php" class="top-button">
            <i class="ri-add-circle-line"></i> Crear Asignación
        </a>

        <a href="crear_deduccion.php" class="top-button">
            <i class="ri-subtract-line"></i> Crear Deducción
        </a>

        <a href="generar_nomina.php" class="top-button">
            <i class="ri-calculator-line"></i> Generar Nómina
        </a>

        <a href="ver_nomina.php" class="top-button">
            <i class="ri-file-list-line"></i> Ver Nóminas
        </a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>
    </div>

    <!-- ===== CONTENIDO ===== -->
<!-- ===== CONTENIDO ===== -->
<div class="contenido">
    <div class="card-container">

        <h2>🧾 Pre-Nómina — Vista Previa</h2>

        <!-- INFO GENERAL -->
        <div class="card info-box">
            <p><strong>Nómina #:</strong> <?= $siguiente_nomina ?></p>
            <p><strong>Período:</strong> <?= $fecha_inicio ?> → <?= $fecha_fin ?></p>
            <p><strong>Generada por:</strong> <?= $_SESSION['usuario'] ?></p>
        </div>

        <!-- FORM PERÍODO -->
        <div class="card">
            <form method="GET" class="periodo-form">
                <label>Fecha Inicio</label>
                <input type="date" name="inicio" value="<?= $fecha_inicio ?>">

                <label>Fecha Fin</label>
                <input type="date" name="fin" value="<?= $fecha_fin ?>">

                <button type="submit">
                    <i class="ri-refresh-line"></i> Actualizar Período
                </button>
            </form>
        </div>

        <!-- TABLA -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Salario Base</th>
                        <th>Asignaciones</th>
                        <th>Deducciones</th>
                        <th>Total a Pagar</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($lista_empleados as $emp) { ?>
                    <tr>
                        <td><?= $emp['nombre'] ?></td>
                        <td><?= number_format($emp['salario'],2) ?> Bs</td>
                        <td><?= number_format($emp['asig'],2) ?> Bs</td>
                        <td><?= number_format($emp['ded'],2) ?> Bs</td>
                        <td><strong><?= number_format($emp['pagar'],2) ?> Bs</strong></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- TOTALES -->
        <div class="card totales-box">
            <p>🟢 Total Asignaciones: <strong><?= number_format($total_general_asig,2) ?> Bs</strong></p>
            <p>🔴 Total Deducciones: <strong><?= number_format($total_general_ded,2) ?> Bs</strong></p>
            <p>💰 Total Neto a Pagar: <strong><?= number_format($total_general_pagar,2) ?> Bs</strong></p>
        </div>

        <!-- GENERAR -->
        <div class="card">
            <form method="POST">
                <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
                <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">

                <label><b>Tipo de Nómina</b></label>
                <select name="tipo">
                    <option value="semanal">Semanal</option>
                    <option value="quincenal">Quincenal</option>
                    <option value="mensual">Mensual</option>
                </select>

                <button class="btn-generar">
                    <i class="ri-check-double-line"></i> Generar Nómina Definitiva
                </button>
            </form>
        </div>

    </div>
</div>


</body>
</html>
