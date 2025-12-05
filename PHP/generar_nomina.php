<?php
session_start();
if (!isset($_SESSION['usuario'])) { 
    header("Location: index.php");
    exit();
}

include("db.php");

// ============================
// PASO 1: CARGAR ASIGNACIONES Y DEDUCCIONES
// ============================
$asignaciones = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
$deducciones  = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");

// Guardarlos en arrays
$arr_asig = [];
while ($a = mysqli_fetch_assoc($asignaciones)) { $arr_asig[] = $a; }

$arr_ded = [];
while ($d = mysqli_fetch_assoc($deducciones)) { $arr_ded[] = $d; }

// ============================
// PASO 2: CARGAR EMPLEADOS ACTIVOS
// ============================
$empleados = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

// Pre-cálculo para vista previa
$preview = [];
$total_general_asig = 0;
$total_general_ded  = 0;
$total_general_neto = 0;

foreach ($empleados as $emp) {

    $salario = floatval($emp['salario_base']);
    $t_asig = 0;
    $t_ded = 0;

    foreach ($arr_asig as $as) {
        if ($as['tipo'] == 'fijo') {
            $monto = floatval($as['valor']);
        } else {
            $monto = $salario * (floatval($as['valor']) / 100);
        }
        $t_asig += $monto;
    }

    foreach ($arr_ded as $dd) {
        $monto = $salario * (floatval($dd['porcentaje']) / 100);
        $t_ded += $monto;
    }

    $neto = $salario + $t_asig - $t_ded;

    $preview[] = [
        "id" => $emp['id'],
        "nombre" => $emp['nombre']." ".$emp['apellido'],
        "salario" => $salario,
        "asig" => $t_asig,
        "ded" => $t_ded,
        "neto" => $neto
    ];

    $total_general_asig += $t_asig;
    $total_general_ded  += $t_ded;
    $total_general_neto += $neto;
}

// ============================
// PASO 3: NÚMERO SIGUIENTE DE NÓMINA
// ============================
$ultimo = mysqli_query($conexion, "SELECT id_nomina FROM nomina ORDER BY id_nomina DESC LIMIT 1");
$next_id = 1;
if (mysqli_num_rows($ultimo) > 0) {
    $u = mysqli_fetch_assoc($ultimo);
    $next_id = $u['id_nomina'] + 1;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pre-Nómina</title>

<style>
body {
    font-family: Arial;
    background:#f5f6f7;
    padding:20px;
}
h2 { text-align:center; }

.table {
    width:100%;
    border-collapse:collapse;
    background:white;
    margin-top:10px;
}
.table th, .table td {
    border:1px solid #ccc;
    padding:8px;
    text-align:center;
}
.box {
    background:white;
    padding:15px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
    margin-bottom:20px;
}
.total-box {
    background:#d9f5e3;
    padding:15px;
    border-radius:10px;
    margin-top:20px;
}
.btn {
    background:#2b4a42;
    color:white;
    padding:10px 20px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
    display:block;
    margin:20px auto;
}
</style>
</head>

<body>

<h2>🧮 Pre-Nómina</h2>

<div class="box">
    <h3>Datos del Período</h3>

    <form method="POST" action="procesar_nomina.php">

        <label>Número de Nómina:</label>
        <input type="text" value="<?= $next_id ?>" disabled style="padding:8px; width:120px;">

        <br><br>

        <label>Fecha Inicio:</label>
        <input type="date" name="fecha_inicio" required style="padding:8px;">  

        <label style="margin-left:20px;">Fecha Fin:</label>
        <input type="date" name="fecha_fin" required style="padding:8px;">

        <br><br>

        <label>Tipo de Nómina:</label>
        <select name="tipo" style="padding:8px;">
            <option value="mensual">Mensual</option>
            <option value="quincenal">Quincenal</option>
            <option value="semanal">Semanal</option>
        </select>

        <br><br>

        <h3>Vista Previa de Empleados</h3>

        <table class="table">
            <tr>
                <th>Empleado</th>
                <th>Salario</th>
                <th>Asignaciones</th>
                <th>Deducciones</th>
                <th>Neto a Pagar</th>
            </tr>

            <?php foreach ($preview as $p): ?>
            <tr>
                <td><?= $p['nombre'] ?></td>
                <td><?= number_format($p['salario'],2) ?></td>
                <td><?= number_format($p['asig'],2) ?></td>
                <td><?= number_format($p['ded'],2) ?></td>
                <td><?= number_format($p['neto'],2) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <div class="total-box">
            <h3>Totales Generales</h3>
            <p><b>Total Asignaciones:</b> <?= number_format($total_general_asig,2) ?></p>
            <p><b>Total Deducciones:</b> <?= number_format($total_general_ded,2) ?></p>
            <p><b>Total Neto a Pagar:</b> <?= number_format($total_general_neto,2) ?></p>
        </div>

        <button class="btn" type="submit">✔ Generar Nómina</button>
    </form>
</div>

</body>
</html>
