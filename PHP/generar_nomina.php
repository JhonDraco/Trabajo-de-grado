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

    // Convertir en arreglo para reutilizar
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

        // --- ASIGNACIONES ---
        foreach ($arr_asignaciones as $asig) {
            if ($asig['tipo'] == 'fijo') {
                $monto = floatval($asig['valor']);
            } else {
                $monto = $salario * (floatval($asig['valor']) / 100);
            }
            $total_asig += $monto;
        }

        // --- DEDUCCIONES ---
        foreach ($arr_deducciones as $ded) {
            $monto = $salario * (floatval($ded['porcentaje']) / 100);
            $total_ded += $monto;
        }

        $total_pagar = ($salario + $total_asig) - $total_ded;

        // 4. Insertar en detalle_nomina
        mysqli_query($conexion,
            "INSERT INTO detalle_nomina (id_nomina, empleado_id, salario_base, total_asignaciones, total_deducciones, total_pagar)
             VALUES ($id_nomina, {$emp['id']}, $salario, $total_asig, $total_ded, $total_pagar)"
        );

        $id_detalle = mysqli_insert_id($conexion);

        // 5. Guardar detalle_asignación
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

        // 6. Guardar detalle_deducción
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
    <title>Generar Nómina</title>
</head>
<body>

<h2>🧾 Generar Nómina</h2>
<?php if (isset($_GET['ok'])) { ?>
    <p style="color:green;">Nómina generada exitosamente. ID: <?= $_GET['id'] ?></p>
<?php } ?>

<form method="post">
    <label>Fecha Inicio:</label>
    <input type="date" name="fecha_inicio" required><br><br>

    <label>Fecha Fin:</label>
    <input type="date" name="fecha_fin" required><br><br>

    <label>Tipo de Nómina:</label>
    <select name="tipo">
        <option value="mensual">Mensual</option>
        <option value="quincenal">Quincenal</option>
        <option value="semanal">Semanal</option>
    </select><br><br>

    <button type="submit">Generar Nómina</button>
</form>

</body>
</html>
