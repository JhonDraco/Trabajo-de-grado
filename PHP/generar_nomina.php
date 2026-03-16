<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeGenerarNomina());

include("db.php");

/* --------------------------------------------------------
   CONFIGURACIÓN GENERAL
---------------------------------------------------------*/
$TOPE_DEDUCCION = 0.40; // 40% del salario del período

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
$empleados = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

/* Tipo de nómina */
$tipo_nomina = $_GET['tipo'] ?? 'mensual';
switch ($tipo_nomina) {
    case 'semanal': $factor = 1/4; break;
    case 'quincenal': $factor = 1/2; break;
    default: $factor = 1;
}

/* --------------------------------------------------------
   4. PRE-NÓMINA
---------------------------------------------------------*/
$lista_empleados = [];
$total_general_asig = 0;
$total_general_ded = 0;
$total_general_pagar = 0;

while($emp = mysqli_fetch_assoc($empleados)) {

    $salario = round($emp['salario_base'] * $factor, 2);
    $total_asig = 0;
    $total_ded = 0;

    $id_emp = $emp['id'];
    $max_deducciones = round($salario * $TOPE_DEDUCCION, 2);

    /* ===== ASIGNACIONES: tipo_asignacion + asignacion_empleado ===== */
    $asig_list = [];

    $qAsig = mysqli_query($conexion, "
        SELECT ta.id_asignacion, ta.nombre, ta.tipo, ta.porcentaje, ae.monto AS monto_emp
        FROM tipo_asignacion ta
        LEFT JOIN asignacion_empleado ae
        ON ae.id_asignacion = ta.id_asignacion AND ae.empleado_id = $id_emp AND ae.activa=1
    ");

    while($a = mysqli_fetch_assoc($qAsig)) {
        // Calcular monto: fijo = monto emp si existe, sino ta.porcentaje, porcentaje = porcentaje sobre salario
        if($a['tipo'] == 'fijo') {
            $monto = $a['monto_emp'] !== null ? $a['monto_emp'] : $a['porcentaje'];
        } else {
            $monto = round($salario * ($a['porcentaje']/100), 2);
        }
        $total_asig += $monto;
        $asig_list[] = ['nombre'=>$a['nombre'],'monto'=>$monto];
    }

    /* ===== DEDUCCIONES ===== */
$ded_list = [];
$total_ded_aplicada = 0;

/* ===== DEDUCCIONES GENERALES (LEY) ===== */
$qDed = mysqli_query($conexion, "
SELECT *
FROM tipo_deduccion
WHERE activo = 1
");

while($d = mysqli_fetch_assoc($qDed)){

    $monto = round($salario * ($d['porcentaje']/100),2);

    // aplicar tope
    if(($total_ded_aplicada + $monto) > $max_deducciones){
        $monto = $max_deducciones - $total_ded_aplicada;
    }

    if($monto <= 0) break;

    $total_ded_aplicada += $monto;

    $ded_list[] = [
        'nombre'=>$d['nombre'],
        'monto'=>$monto
    ];
}


            /* ===== DEDUCCIONES INDIVIDUALES (PRÉSTAMOS, ADELANTOS) ===== */
            $qDedEmp = mysqli_query($conexion, "
            SELECT *
            FROM deduccion_empleado
            WHERE empleado_id = $id_emp
            AND activa = 1
            ");

            while($d = mysqli_fetch_assoc($qDedEmp)){

                // calcular cuota
                $cuota = $d['monto'] / $d['cuotas'];

                $cuota = round($cuota,2);

                // aplicar tope 40%
                if(($total_ded_aplicada + $cuota) > $max_deducciones){
                    $cuota = $max_deducciones - $total_ded_aplicada;
                }

                if($cuota <= 0) break;

                $total_ded_aplicada += $cuota;

                $ded_list[] = [
                    'nombre'=>$d['nombre'],
                    'monto'=>$cuota
                ];
            }

    /* ===== VACACIONES (informativo) ===== */
    $vacaciones = mysqli_query($conexion, "
        SELECT dias_habiles FROM vacaciones
        WHERE empleado_id = $id_emp
        AND estado = 'aprobado'
        AND fecha_inicio <= '$fecha_fin'
        AND fecha_fin >= '$fecha_inicio'
    ");
    $dias_vac = 0;
    while($v = mysqli_fetch_assoc($vacaciones)){
        $dias_vac += intval($v['dias_habiles']);
    }

    $total_pago = max(0, ($salario + $total_asig) - $total_ded_aplicada);

    $lista_empleados[] = [
        'id'=>$id_emp,
        'nombre'=>$emp['nombre']." ".$emp['apellido'],
        'salario'=>$salario,
        'asig'=>$total_asig,
        'asig_detalle'=>$asig_list,
        'ded'=>$total_ded_aplicada,
        'ded_detalle'=>$ded_list,
        'pagar'=>$total_pago,
        'vac'=>$dias_vac
    ];

    $total_general_asig += $total_asig;
    $total_general_ded += $total_ded_aplicada;
    $total_general_pagar += $total_pago;
}

/* --------------------------------------------------------
   5. GENERAR NÓMINA DEFINITIVA
---------------------------------------------------------*/
if(isset($_POST['generar_nomina'])){
    $empleados_seleccionados = $_POST['empleados'] ?? [];
    mysqli_begin_transaction($conexion);

    try {
        mysqli_query($conexion, "
            INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por)
            VALUES ('$fecha_inicio','$fecha_fin','$tipo_nomina','{$_SESSION['usuario']}')
        ");
        $id_nomina = mysqli_insert_id($conexion);

        foreach($lista_empleados as $emp){

                  // si el empleado no está seleccionado, lo saltamos
                if(!in_array($emp['id'], $empleados_seleccionados)){
                 continue;
                }

            // insertar detalle_nomina
            mysqli_query($conexion, "
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
            $id_detalle = mysqli_insert_id($conexion);

            // detalle_asignacion
            foreach($emp['asig_detalle'] as $a){
                mysqli_query($conexion, "
                    INSERT INTO detalle_asignacion
                    (id_detalle, id_asignacion, monto)
                    VALUES ($id_detalle, (SELECT id_asignacion FROM tipo_asignacion WHERE nombre='".mysqli_real_escape_string($conexion,$a['nombre'])."'), {$a['monto']})
                ");
            }

            // detalle_deduccion
            foreach($emp['ded_detalle'] as $d){
                mysqli_query($conexion, "
                    INSERT INTO detalle_deduccion
                    (id_detalle, id_tipo, monto)
                    VALUES ($id_detalle, (SELECT id_tipo FROM tipo_deduccion WHERE nombre='".mysqli_real_escape_string($conexion,$d['nombre'])."'), {$d['monto']})
                ");
            }
        }

        mysqli_commit($conexion);
        header("Location: ver_nomina.php?id=$id_nomina");
        exit();
    } catch(Exception $e){
        mysqli_rollback($conexion);
        die("Error al generar nómina: ".$e->getMessage());
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
<div id="overlay" class="overlay"></div>
<!-- ===== SIDEBAR ===== -->

<aside class="sidebar">

<div class="sidebar-header">
<img src="../img/logo.png" alt="Logo" class="logo">
<h3 class="system-title">KAO SHOP</h3>
</div>

<a href="administrador.php">
<i class="ri-home-4-line"></i> Inicio
</a>

<a href="nomina.php" class="active">
<i class="ri-money-dollar-circle-line"></i> Nómina
</a>

<a href="liquidacion.php">
<i class="ri-ball-pen-line"></i> Liquidación
</a>

<a href="vacaciones.php">
<i class="ri-sun-line"></i> Vacaciones
</a>

<a href="listar_empleados.php">
<i class="ri-team-line"></i> Empleados
</a>

<a href="listar_usuario.php">
<i class="ri-user-settings-line"></i> Roles
</a>

<a href="reportes.php">
<i class="ri-bar-chart-line"></i> Reportes
</a>

<a href="contactar.php">
<i class="ri-mail-line"></i> Email
</a>

</aside>

<!-- ===== MAIN ===== -->

<div class="main">

<!-- ===== HEADER ===== -->

<header>

<h2>Panel de Administración - RRHH</h2>

<div>
<span>👤 <?= $_SESSION['usuario'] ?></span> |
<a href="cerrar_sesion.php">Cerrar sesión</a>
</div>

</header>


<!-- ===== TOP MENU ===== -->

<div class="top-menu">

<a href="asignaciones.php" class="top-button">
<i class="ri-add-circle-line"></i> Asignaciones
</a>

<a href="deducciones.php" class="top-button">
<i class="ri-subtract-line"></i> Deducciones
</a>

<a href="generar_nomina.php" class="top-button">
<i class="ri-calculator-line"></i> Generar Nómina
</a>

<a href="ver_nomina.php" class="top-button">
<i class="ri-file-list-line"></i> Ver Nóminas
</a>

<a href="pagar_nomina.php" class="top-button">
<i class="ri-eye-line"></i> Pagar Nóminas
</a>

<a href="historial_pagos.php" class="top-button">
<i class="ri-file-text-line"></i> Historial de Pagos
</a>

</div>


<!-- ===== CONTENIDO ===== -->

<div class="contenido">

<div class="card-container">

<h2> Pre-Nómina </h2>

<div class="panel-nomina">

<!-- INFORMACION NOMINA -->

<div class="info-nomina">

<div class="info-card">
<span class="info-titulo">Nómina</span>
<span class="info-valor">#<?= $siguiente_nomina ?></span>
</div>

<div class="info-card">
<span class="info-titulo">Período</span>
<span class="info-valor"><?= $fecha_inicio ?> → <?= $fecha_fin ?></span>
</div>

<div class="info-card">
<span class="info-titulo">Generado por</span>
<span class="info-valor"><?= $_SESSION['usuario'] ?></span>
</div>

</div>

<!-- FILTROS NOMINA -->

<div class="filtros-nomina">

<form method="GET" class="form-nomina">

<div class="grupo-form">

<label>Tipo de Nómina</label>

<select name="tipo">
<option value="semanal" <?= $tipo_nomina=='semanal'?'selected':'' ?>>Semanal</option>
<option value="quincenal" <?= $tipo_nomina=='quincenal'?'selected':'' ?>>Quincenal</option>
<option value="mensual" <?= $tipo_nomina=='mensual'?'selected':'' ?>>Mensual</option>
</select>

</div>

<div class="grupo-form">

<label>Fecha Inicio</label>
<input type="date" name="inicio" value="<?= $fecha_inicio ?>">

</div>

<div class="grupo-form">

<label>Fecha Fin</label>
<input type="date" name="fin" value="<?= $fecha_fin ?>">

</div>

<div class="grupo-form boton">

<button type="submit">
<i class="ri-refresh-line"></i>
Actualizar
</button>

</div>

</form>

</div>

<!-- CONTROLES -->

<div class="controles-nomina">

<div class="controles-izq">

<button type="button" onclick="seleccionarTodos()" class="btn-control">
Seleccionar todos
</button>

<button type="button" onclick="quitarTodos()" class="btn-control rojo">
Quitar todos
</button>

</div>

<div class="contador-empleados">

Incluidos:
<strong id="emp_incluidos">0</strong>

|

Excluidos:
<strong id="emp_excluidos">0</strong>

</div>

</div>

</div>

<!-- FORM PRINCIPAL -->

<form method="POST">

<!-- BUSCADOR -->

<div class="buscador-empleados">

<i class="ri-search-line"></i>

<input
type="text"
id="buscarEmpleado"
placeholder="Buscar empleado...">

</div>

<!-- TABLA -->

<div class="table-container">

<table>

<thead>

<tr>
<th></th>
<th>Empleado</th>
<th>Estado</th>
<th>Salario Base</th>
<th>Asignaciones</th>
<th>Deducciones</th>
<th>Total a Pagar</th>
</tr>

</thead>

<tbody>

<?php foreach ($lista_empleados as $emp) { ?>

<tr>

<td>

<input type="checkbox"
class="check-empleado"
data-asig="<?= $emp['asig'] ?>"
data-ded="<?= $emp['ded'] ?>"
data-total="<?= $emp['pagar'] ?>"
name="empleados[]"
value="<?= $emp['id'] ?>"
checked>

</td>
<td>

<a href="#" onclick="verDetalle(<?= $emp['id'] ?>); return false;">
<i class="ri-user-3-line"></i>
<?= $emp['nombre'] ?>
</a>

</td>

<td>

<?php
if($emp['vac'] > 0){
echo '<span class="estado vac">Vacaciones</span>';
}else{
echo '<span class="estado activo">Activo</span>';
}
?>

</td>

<td><?= number_format($emp['salario'],2) ?> Bs</td>
<td><?= number_format($emp['asig'],2) ?> Bs</td>
<td><?= number_format($emp['ded'],2) ?> Bs</td>
<td><strong><?= number_format($emp['pagar'],2) ?> Bs</strong></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- PANEL DETALLE -->

<div id="panelDetalle" class="panel-detalle">

<div class="panel-contenido">

<button onclick="cerrarPanel()" class="cerrar-btn">✖</button>

<div id="contenidoDetalle"></div>

</div>

</div>

<!-- TOTALES -->

<div class="totales-box">

<div class="total-card asig">
🟢 Total Asignaciones
<strong id="total_asig">
<?= number_format($total_general_asig,2) ?>
</strong> Bs
</div>

<div class="total-card ded">
🔴 Total Deducciones
<strong id="total_ded">
<?= number_format($total_general_ded,2) ?>
</strong> Bs
</div>

<div class="total-card pagar">
💰 Total Neto a Pagar
<strong id="total_pagar">
<?= number_format($total_general_pagar,2) ?>
</strong> Bs
</div>

</div>

<!-- BOTON GENERAR -->

<div class="card generar-card">

<button type="submit" name="generar_nomina" class="btn-generar">

<i class="ri-check-double-line"></i>

Generar Nómina Definitiva

</button>

</div>

</form>

</div>

</div>
</body>
</html>

<script>
function verDetalle(id){

const inicio = "<?= $fecha_inicio ?>";
const fin = "<?= $fecha_fin ?>";
const tipo = "<?= $tipo_nomina ?>";

fetch("detalle_prenomina.php?id="+id+"&inicio="+inicio+"&fin="+fin+"&tipo="+tipo)
.then(res => res.text())
.then(data => {

document.getElementById("contenidoDetalle").innerHTML = data;

document.getElementById("panelDetalle").classList.add("activo");
document.getElementById("overlay").classList.add("activo");

});

}

function cerrarPanel(){

document.getElementById("panelDetalle").classList.remove("activo");
document.getElementById("overlay").classList.remove("activo");

}
document.getElementById("overlay").onclick = cerrarPanel;
</script>


<script>

function actualizarTotales(){

        let totalAsig = 0;
        let totalDed = 0;
        let totalPagar = 0;

        document.querySelectorAll(".check-empleado").forEach(check => {

        if(check.checked){

        totalAsig += parseFloat(check.dataset.asig);
        totalDed += parseFloat(check.dataset.ded);
        totalPagar += parseFloat(check.dataset.total);

        }

        });

        document.getElementById("total_asig").innerText = totalAsig.toFixed(2);
        document.getElementById("total_ded").innerText = totalDed.toFixed(2);
        document.getElementById("total_pagar").innerText = totalPagar.toFixed(2);

        }

        document.querySelectorAll(".check-empleado").forEach(check => {

        check.addEventListener("change", actualizarTotales);

        });

</script>

<script>

function actualizarTotales(){

let totalAsig = 0;
let totalDed = 0;
let totalPagar = 0;

let incluidos = 0;
let excluidos = 0;

document.querySelectorAll(".check-empleado").forEach(check => {

if(check.checked){

totalAsig += parseFloat(check.dataset.asig);
totalDed += parseFloat(check.dataset.ded);
totalPagar += parseFloat(check.dataset.total);

incluidos++;

}else{

excluidos++;

}

});

document.getElementById("total_asig").innerText = totalAsig.toFixed(2);
document.getElementById("total_ded").innerText = totalDed.toFixed(2);
document.getElementById("total_pagar").innerText = totalPagar.toFixed(2);

document.getElementById("emp_incluidos").innerText = incluidos;
document.getElementById("emp_excluidos").innerText = excluidos;

}


function seleccionarTodos(){

document.querySelectorAll(".check-empleado").forEach(check => {

check.checked = true;

});

actualizarTotales();

}

function quitarTodos(){

document.querySelectorAll(".check-empleado").forEach(check => {

check.checked = false;

});

actualizarTotales();

}

document.querySelectorAll(".check-empleado").forEach(check => {

check.addEventListener("change", actualizarTotales);

});

window.onload = actualizarTotales;

const buscador = document.getElementById("buscarEmpleado");

buscador.addEventListener("keyup", function(){

let filtro = buscador.value.toLowerCase();
let filas = document.querySelectorAll("table tbody tr");

filas.forEach(fila => {

let texto = fila.innerText.toLowerCase();

if(texto.includes(filtro)){

fila.style.display = "";

}else{

fila.style.display = "none";

}

});

});

</script>

</body>
</html>
