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
<title>Generar Nómina</title>
<link rel="stylesheet" href="../css/generar_nomina.css">
</head>
<body>

<div class="main-container">

<h1>🧾 Pre-Nómina — Vista Previa</h1>

<!-- 🔹 Información General -->
<div class="info-box">
    <p><strong>Nómina #:</strong> <?= $siguiente_nomina ?></p>
    <p><strong>Período:</strong> <?= $fecha_inicio ?> → <?= $fecha_fin ?></p>
    <p><strong>Generada por:</strong> <?= $_SESSION['usuario'] ?></p>
</div>

<!-- 🔹 Formulario de Ajuste de Período -->
<form method="GET" class="periodo-form">
    <label>Fecha Inicio:</label>
    <input type="date" name="inicio" value="<?= $fecha_inicio ?>">

    <label>Fecha Fin:</label>
    <input type="date" name="fin" value="<?= $fecha_fin ?>">

    <button type="submit">Actualizar Período</button>
</form>

<!-- 🔹 Tabla Pre-Nómina -->
<table class="tabla-nomina">
    <tr>
        <th>Empleado</th>
        <th>Salario Base</th>
        <th>Asignaciones</th>
        <th>Deducciones</th>
        <th>Total a Pagar</th>
    </tr>

    <?php foreach ($lista_empleados as $emp) { ?>
    <tr>
        <td><?= $emp['nombre'] ?></td>
        <td><?= number_format($emp['salario'],2) ?> Bs</td>
        <td><?= number_format($emp['asig'],2) ?> Bs</td>
        <td><?= number_format($emp['ded'],2) ?> Bs</td>
        <td><strong><?= number_format($emp['pagar'],2) ?> Bs</strong></td>
    </tr>
    <?php } ?>
</table>

<!-- 🔹 Totales Generales -->
<div class="totales-box">
    <p>🟢 Total Asignaciones: <strong><?= number_format($total_general_asig,2) ?> Bs</strong></p>
    <p>🔴 Total Deducciones: <strong><?= number_format($total_general_ded,2) ?> Bs</strong></p>
    <p>💰 Total Neto a Pagar: <strong><?= number_format($total_general_pagar,2) ?> Bs</strong></p>
</div>

<!-- 🔹 Botón Generar Nómina -->
<form method="POST">
    <input type="hidden" name="fecha_inicio" value="<?= $fecha_inicio ?>">
    <input type="hidden" name="fecha_fin" value="<?= $fecha_fin ?>">

    <label><b>Tipo de Nómina:</b></label>
    <select name="tipo">
        <option value="semanal">Semanal</option>
        <option value="quincenal">Quincenal</option>
        <option value="mensual">Mensual</option>
    </select>

    <button name="generar_nomina" class="btn-generar">✔ Generar Nómina Definitiva</button>
</form>

</div>

</body>
</html>
