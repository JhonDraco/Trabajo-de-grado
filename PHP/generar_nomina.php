<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

// NOOOOOOOO  ESTA TERMINADO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $tipo = $_POST['tipo'];

    // 1) crear la nómina
    mysqli_query($conexion, "INSERT INTO nomina (fecha_inicio, fecha_fin, tipo, creada_por) VALUES ('$fecha_inicio','$fecha_fin','$tipo','{$_SESSION['usuario']}')");
    $id_nomina = mysqli_insert_id($conexion);

    // 2) obtener deducciones y asignaciones
    $deducciones = [];
    $res = mysqli_query($conexion, "SELECT * FROM tipo_deduccion");
    while($r = mysqli_fetch_assoc($res)) $deducciones[] = $r;

    $asignaciones = [];
    $res2 = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");
    while($r2 = mysqli_fetch_assoc($res2)) $asignaciones[] = $r2;

    // 3) obtener empleados activos
    $emps = mysqli_query($conexion, "SELECT * FROM empleados WHERE estado='activo'");

    while($emp = mysqli_fetch_assoc($emps)) {
        $salario = floatval($emp['salario_base']);
        $total_asig = 0.0;
        $total_ded = 0.0;

        // aplicar asignaciones (tipo fijo o porcentaje)
        foreach($asignaciones as $asig) {
            if ($asig['tipo'] === 'fijo') {
                $monto = floatval($asig['valor']);
            } else { // porcentaje
                $monto = $salario * (floatval($asig['valor'])/100.0);
            }
            // si el monto es 0 y quieres asignarlo manualmente, aquí podrías omitirlo
            $total_asig += $monto;
        }

        // aplicar deducciones por porcentaje
        foreach($deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje'])/100.0);
            $total_ded += $monto;
        }

        $total_pagar = ($salario + $total_asig) - $total_ded;

        // 4) insertar detalle_nomina
        $s = mysqli_real_escape_string($conexion, $salario);
        $ta = mysqli_real_escape_string($conexion, $total_asig);
        $td = mysqli_real_escape_string($conexion, $total_ded);
        $tp = mysqli_real_escape_string($conexion, $total_pagar);

        mysqli_query($conexion, "INSERT INTO detalle_nomina (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar) VALUES ($id_nomina, {$emp['id']}, $s, $ta, $td, $tp)");
        $id_det = mysqli_insert_id($conexion);

        // 5) podemos guardar detalle_asignacion y detalle_deduccion (opcional)
        foreach($asignaciones as $asig) {
            if ($asig['tipo'] === 'fijo') $monto = floatval($asig['valor']);
            else $monto = $salario * (floatval($asig['valor'])/100.0);
            mysqli_query($conexion, "INSERT INTO detalle_asignacion (id_detalle, id_asignacion, monto) VALUES ($id_det, {$asig['id_asignacion']}, $monto)");
        }
        foreach($deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje'])/100.0);
            mysqli_query($conexion, "INSERT INTO detalle_deduccion (id_detalle, id_tipo, monto) VALUES ($id_det, {$ded['id_tipo']}, $monto)");
        }
    }

    header("Location: generar_nomina.php?ok=1&id=$id_nomina");
    exit();
}

// mostrar formulario
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Generar Nómina</title></head>
<body>
<h2>Generar Nómina</h2>
<?php if (isset($_GET['ok'])) echo "<p>Nómina generada: ID " . intval($_GET['id']) . "</p>"; ?>
<form method="post">
    <label>Fecha inicio: <input type="date" name="fecha_inicio" required></label><br>
    <label>Fecha fin: <input type="date" name="fecha_fin" required></label><br>
    <label>Tipo:
        <select name="tipo">
            <option value="mensual">Mensual</option>
            <option value="quincenal">Quincenal</option>
            <option value="semanal">Semanal</option>
        </select>
    </label><br>
    <button type="submit">Generar</button>
</form>

</body>
</html>
