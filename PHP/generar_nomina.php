<?php 
session_start();
if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php"); 
    exit(); 
}

include("db.php");

/* --------------------------------------------------------
   1. FECHA AUTOMÁTICA (SEMANA ACTUAL)
---------------------------------------------------------*/
$lunes   = date("Y-m-d", strtotime('monday this week'));
$domingo = date("Y-m-d", strtotime('sunday this week'));

$fecha_inicio = $_GET['inicio'] ?? $lunes;
$fecha_fin    = $_GET['fin'] ?? $domingo;

/* --------------------------------------------------------
   2. SIGUIENTE NÚMERO DE NÓMINA
---------------------------------------------------------*/
$res_nom = mysqli_query($conexion, "SELECT MAX(id_nomina) AS maximo FROM nomina");
$data_nom = mysqli_fetch_assoc($res_nom);
$siguiente_nomina = ($data_nom['maximo'] ?? 0) + 1;

/* --------------------------------------------------------
   3. CARGAR TABLAS
---------------------------------------------------------*/
$empleados    = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");
$asignaciones = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
$deducciones  = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");

$arr_asig = [];
while ($a = mysqli_fetch_assoc($asignaciones)) $arr_asig[] = $a;

$arr_ded = [];
while ($d = mysqli_fetch_assoc($deducciones)) $arr_ded[] = $d;


/* --------------------------------------------------------
   division de el salario base
---------------------------------------------------------*/
$tipo_nomina = $_GET['tipo'] ?? 'mensual';

switch ($tipo_nomina) {
    case 'semanal':
        $factor = 1 / 4;
        break;
    case 'quincenal':
        $factor = 1 / 2;
        break;
    default:
        $factor = 1;
}


/* --------------------------------------------------------
   4. CALCULAR PRE-NÓMINA (CON VACACIONES)
---------------------------------------------------------*/
$lista_empleados = [];
$total_general_asig = 0;
$total_general_ded = 0;
$total_general_pagar = 0;

mysqli_data_seek($empleados, 0);
while ($emp = mysqli_fetch_assoc($empleados)) {

    $salario_mensual = floatval($emp['salario_base']);
    $salario = round($salario_mensual * $factor, 2);

    $total_asig = 0;
    $total_ded = 0;

    /* ===============================
   DEDUCCIONES GENERALES
===============================*/
foreach ($arr_ded as $ded) {
    $monto = $salario * ($ded['porcentaje'] / 100);
    $total_ded += $monto;
}


/* DEDUCCIONES POR EMPLEADO (SOLO CALCULAR) */
$qDedEmp = mysqli_query($conexion, "
    SELECT * FROM deduccion_empleado
    WHERE empleado_id = {$emp['id']}
      AND activa = 1
      AND cuota_actual < cuotas
");

    while ($de = mysqli_fetch_assoc($qDedEmp)) {
    $cuota_mensual = $de['monto'] / $de['cuotas'];
    $total_ded += round($cuota_mensual * $factor, 2);
    }



    /* ===== VACACIONES APROBADAS EN EL PERÍODO ===== */
    $vacaciones = mysqli_query($conexion, "
        SELECT id_vacacion, dias_habiles
        FROM vacaciones
        WHERE empleado_id = {$emp['id']}
          AND estado = 'aprobada'
          AND fecha_inicio <= '$fecha_fin'
          AND fecha_fin >= '$fecha_inicio'
    ");

    $dias_vacaciones = 0;
    while ($v = mysqli_fetch_assoc($vacaciones)) {
        $dias_vacaciones += intval($v['dias_habiles']);
    }

    // Vacaciones NO descuentan salario (solo informativo)
    $total_pago = ($salario + $total_asig) - $total_ded;

    $lista_empleados[] = [
        "id"      => $emp['id'],
        "nombre"  => $emp['nombre']." ".$emp['apellido'],
        "salario" => $salario,
        "asig"    => $total_asig,
        "ded"     => $total_ded,
        "pagar"   => $total_pago,
        "vac"     => $dias_vacaciones
    ];

    $total_general_asig += $total_asig;
    $total_general_ded += $total_ded;
    $total_general_pagar += $total_pago;
}

/* --------------------------------------------------------
   5. GENERAR NÓMINA DEFINITIVA
---------------------------------------------------------*/
if (isset($_POST['generar_nomina'])) {

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo         = $_POST['tipo'];
    $creada_por   = $_SESSION['usuario'];

    mysqli_begin_transaction($conexion);

    try {

        mysqli_query($conexion, "
            INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por)
            VALUES ('$fecha_inicio', '$fecha_fin', '$tipo', '$creada_por')
        ");

        $id_nomina = mysqli_insert_id($conexion);

        foreach ($lista_empleados as $emp) {

              $insert_detalle = mysqli_query($conexion, "
                INSERT INTO detalle_nomina
                (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
                VALUES (
                    $id_nomina,
                    {$emp['id']},
                    {$emp['salario']},
                    {$emp['asig']},
                    {$emp['ded']},
                    {$emp['pagar']}
                )
                ");

                if (!$insert_detalle) {
                throw new Exception("Error detalle_nomina: " . mysqli_error($conexion));
                }

                $id_detalle = mysqli_insert_id($conexion);

                if ($id_detalle <= 0) {
                    throw new Exception("No se pudo obtener id_detalle");
                }


            /* Asignaciones */
            foreach ($arr_asig as $asig) {
                $monto = ($asig['tipo'] == 'fijo')
                    ? $asig['valor']
                    : $emp['salario'] * ($asig['valor'] / 100);

                mysqli_query($conexion, "
                    INSERT INTO detalle_asignacion (id_detalle, id_asignacion, monto)
                    VALUES ($id_detalle, {$asig['id_asignacion']}, $monto)
                ");
            }

            /* Deducciones */
            foreach ($arr_ded as $ded) {
                $monto = $emp['salario'] * ($ded['porcentaje'] / 100);

                mysqli_query($conexion, "
                    INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto)
                    VALUES ($id_detalle, {$ded['id_tipo']}, $monto)
                ");
            }

             /* Deducciones por empleado por cuotas */
               $qDedEmp = mysqli_query($conexion, "
                SELECT * FROM deduccion_empleado
                WHERE empleado_id = {$emp['id']}
                AND activa = 1
                AND cuota_actual < cuotas
            ");

            while ($de = mysqli_fetch_assoc($qDedEmp)) {

                $monto_cuota = $de['monto'] / $de['cuotas'];

                // Insertar como deducción personalizada
                mysqli_query($conexion, "
                    INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto)
                    VALUES ($id_detalle, NULL, $monto_cuota)
                ");

                // Avanzar cuota
                mysqli_query($conexion, "
                    UPDATE deduccion_empleado
                    SET cuota_actual = cuota_actual + 1
                    WHERE id_deduccion_emp = {$de['id_deduccion_emp']}
                ");

                // Finalizar deducción
                mysqli_query($conexion, "
                    UPDATE deduccion_empleado
                    SET activa = 0
                    WHERE id_deduccion_emp = {$de['id_deduccion_emp']}
                    AND cuota_actual >= cuotas
                ");
            }


    
            /* Vacaciones → vincular con nómina */
            $resVac = mysqli_query($conexion, "
                SELECT id_vacacion, dias_habiles
                FROM vacaciones
                WHERE empleado_id = {$emp['id']}
                  AND estado = 'aprobada'
                  AND fecha_inicio <= '$fecha_fin'
                  AND fecha_fin >= '$fecha_inicio'
            ");

            while ($v = mysqli_fetch_assoc($resVac)) {
                mysqli_query($conexion, "
                    INSERT INTO nomina_vacaciones
                    (id_nomina, id_vacacion, empleado_id, dias_pagados)
                    VALUES ($id_nomina, {$v['id_vacacion']}, {$emp['id']}, {$v['dias_habiles']})
                ");
            }
        }

        mysqli_commit($conexion);
        header("Location: ver_nomina.php?id=$id_nomina");
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("❌ Error al generar nómina: " . $e->getMessage());
    }
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
    <a href="administrador.php">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php" class="active">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    

    </a>
     <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>
    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Agendar entrevistas 
    </a>
    
   
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

            <select name="tipo">
    <option value="semanal" <?= $tipo_nomina=='semanal'?'selected':'' ?>>Semanal</option>
    <option value="quincenal" <?= $tipo_nomina=='quincenal'?'selected':'' ?>>Quincenal</option>
    <option value="mensual" <?= $tipo_nomina=='mensual'?'selected':'' ?>>Mensual</option>
</select>

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

                <button type="submit" name="generar_nomina" class="btn-generar">
                      <i class="ri-check-double-line"></i> Generar Nómina Definitiva
                </button>

            </form>
        </div>

    </div>
</div>


</body>
</html>
