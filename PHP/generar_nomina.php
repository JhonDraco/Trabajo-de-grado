<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}
include("db.php");

/* ----------------------------------------------------------
   1. DEFINIR FECHAS POR DEFECTO (si no hay POST aún)
-----------------------------------------------------------*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    // Fecha inicio -> lunes de la semana actual
    $diaSemana = date('N'); 
    $fecha_inicio = date('Y-m-d', strtotime("-".($diaSemana-1)." days"));

    // Fecha fin -> domingo de la semana actual
    $fecha_fin = date('Y-m-d', strtotime("+".(7-$diaSemana)." days"));

    // Tipo por defecto
    $tipo = "semanal";

} else {
    // Si viene de POST:
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo         = $_POST['tipo'];
}

/* ----------------------------------------------------------
   2. CALCULAR EL NÚMERO DE LA PRÓXIMA NÓMINA
-----------------------------------------------------------*/
$res = mysqli_query($conexion, "SELECT MAX(id_nomina) AS maximo FROM nomina");
$row = mysqli_fetch_assoc($res);
$proxima_nomina = $row['maximo'] + 1;

/* ----------------------------------------------------------
   3. OBTENER DEDUCCIONES Y ASIGNACIONES
-----------------------------------------------------------*/
$deducciones = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
$asignaciones = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");

$arr_dedu = [];
while ($d = mysqli_fetch_assoc($deducciones)) $arr_dedu[] = $d;

$arr_asig = [];
while ($a = mysqli_fetch_assoc($asignaciones)) $arr_asig[] = $a;

/* ----------------------------------------------------------
   4. OBTENER EMPLEADOS ACTIVOS Y CALCULAR PRE-NÓMINA
-----------------------------------------------------------*/
$empleados = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

$lista = [];
$total_general_asig = 0;
$total_general_ded = 0;
$total_general_pagar = 0;

while ($emp = mysqli_fetch_assoc($empleados)) {

    $salario = $emp['salario_base'];
    $total_asig = 0;
    $total_ded = 0;

    foreach ($arr_asig as $asig) {
        if ($asig['tipo'] == 'fijo') {
            $monto = floatval($asig['valor']);
        } else {
            $monto = $salario * (floatval($asig['valor']) / 100);
        }
        $total_asig += $monto;
    }

    foreach ($arr_dedu as $ded) {
        $monto = $salario * (floatval($ded['porcentaje']) / 100);
        $total_ded += $monto;
    }

    $total_pagar = ($salario + $total_asig) - $total_ded;

    $lista[] = [
        'id'       => $emp['id'],
        'nombre'   => $emp['nombre'] . ' ' . $emp['apellido'],
        'cedula'   => $emp['cedula'],
        'salario'  => $salario,
        'asig'     => $total_asig,
        'ded'      => $total_ded,
        'pagar'    => $total_pagar
    ];

    $total_general_asig  += $total_asig;
    $total_general_ded   += $total_ded;
    $total_general_pagar += $total_pagar;
}

/* ----------------------------------------------------------
   5. SI SE PRESIONA BOTÓN -> GENERAR NÓMINA
-----------------------------------------------------------*/
if (isset($_POST['generar_nomina'])) {

    $creada_por = $_SESSION['usuario'];

    mysqli_query($conexion, 
        "INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por) 
         VALUES ('$fecha_inicio','$fecha_fin','$tipo','$creada_por')"
    );

    $id_nomina = mysqli_insert_id($conexion);

    foreach ($lista as $emp) {

        mysqli_query($conexion,
            "INSERT INTO detalle_nomina
            (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
             VALUES ($id_nomina, {$emp['id']}, {$emp['salario']}, {$emp['asig']}, {$emp['ded']}, {$emp['pagar']})"
        );

        $id_detalle = mysqli_insert_id($conexion);

        foreach ($arr_asig as $asig) {
            if ($asig['tipo'] == 'fijo') $monto = floatval($asig['valor']);
            else $monto = $emp['salario'] * (floatval($asig['valor']) / 100);

            mysqli_query($conexion,
                "INSERT INTO detalle_asignacion (id_detalle, id_asignacion, monto)
                 VALUES ($id_detalle, {$asig['id_asignacion']}, $monto)"
            );
        }

        foreach ($arr_dedu as $ded) {
            $monto = $emp['salario'] * (floatval($ded['porcentaje']) / 100);

            mysqli_query($conexion,
                "INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto)
                 VALUES ($id_detalle, {$ded['id_tipo']}, $monto)"
            );
        }
    }

    header("Location: ver_nomina.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pre-Nómina</title>
<link rel="stylesheet" href="../css/generar_nomina.css">
</head>

<body>

<div class="main">
    <h2>🧾 Pre-Nómina - Vista Previa</h2>

    <!-- Info de la nómina -->
    <div class="info-box">
        <p><strong>N° de Nómina:</strong> <?= $proxima_nomina ?></p>
        <p><strong>Periodo:</strong> <?= $fecha_inicio ?> → <?= $fecha_fin ?></p>
        <p><strong>Tipo:</strong> <?= strtoupper($tipo) ?></p>
    </div>

    <!-- Formulario para recalcular -->
    <form method="post" class="form-periodo">
        <label>Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= $fecha_inicio ?>">

        <label>Fecha Fin:</label>
        <input type="date" name="fecha_fin" value="<?= $fecha_fin ?>">

        <label>Tipo:</label>
        <select name="tipo">
            <option value="semanal"   <?= $tipo=='semanal'?'selected':'' ?>>Semanal</option>
            <option value="quincenal" <?= $tipo=='quincenal'?'selected':'' ?>>Quincenal</option>
            <option value="mensual"   <?= $tipo=='mensual'?'selected':'' ?>>Mensual</option>
        </select>

        <button type="submit">Actualizar Pre-Nómina</button>
    </form>

    <!-- TABLA DE EMPLEADOS -->
    <table class="tabla-nomina">
        <tr>
            <th>Cédula</th>
            <th>Empleado</th>
            <th>Salario Base</th>
            <th>Asignaciones</th>
            <th>Deducciones</th>
            <th>Total a Pagar</th>
        </tr>

        <?php foreach ($lista as $emp) { ?>
        <tr>
            <td><?= $emp['cedula'] ?></td>
            <td><?= $emp['nombre'] ?></td>
            <td><?= number_format($emp['salario'],2) ?></td>
            <td><?= number_format($emp['asig'],2) ?></td>
            <td><?= number_format($emp['ded'],2) ?></td>
            <td><strong><?= number_format($emp['pagar'],2) ?></strong></td>
        </tr>
        <?php } ?>
    </table>

    <!-- TOTALES GENERALES -->
    <div class="totales">
        <p><strong>Total Asignaciones:</strong> <?= number_format($total_general_asig,2) ?></p>
        <p><strong>Total Deducciones:</strong> <?= number_format($total_general_ded,2) ?></p>
        <p><strong>Total Neto a Pagar:</strong> <?= number_format($total_general_pagar,2) ?></p>
    </div>

    <!-- BOTÓN GENERAR NOMINA -->
    <form method="post">
        <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
        <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">
        <input type="hidden" name="tipo" value="<?= $tipo ?>">
        <button name="generar_nomina" class="btn-generar">Generar Nómina Definitiva</button>
    </form>

</div>

</body>
</html>
