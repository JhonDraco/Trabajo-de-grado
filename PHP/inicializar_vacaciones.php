<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeInicializarVacaciones());

include("db.php");

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $anio = intval($_POST['anio']);
    $dias_anuales = 15; // regla base

    // 1️⃣ Obtener empleados activos
    $empleados = mysqli_query($conexion, "SELECT id FROM empleados WHERE estado = 'activo'");

    $creados = 0;
    $omitidos = 0;

    while ($emp = mysqli_fetch_assoc($empleados)) {

        $empleado_id = $emp['id'];

        // 2️⃣ Verificar si ya existe saldo para ese año
        $check = mysqli_query(
            $conexion,
            "SELECT id_saldo FROM vacaciones_saldo 
             WHERE empleado_id = $empleado_id AND anio = $anio"
        );

        if (mysqli_num_rows($check) > 0) {
            $omitidos++;
            continue;
        }

        // 3️⃣ Crear saldo
        mysqli_query(
            $conexion,
            "INSERT INTO vacaciones_saldo
            (empleado_id, anio, dias_acumulados, dias_disfrutados, dias_pendientes)
            VALUES
            ($empleado_id, $anio, $dias_anuales, 0, $dias_anuales)"
        );

        $creados++;
    }

    $mensaje = "✔ Vacaciones inicializadas para $creados empleados. 
                ⚠ $omitidos ya tenían saldo creado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicializar Vacaciones</title>
    <link rel="stylesheet" href="../css/nomina.css">
</head>
<body>

<h2>🏖️ Inicializar Vacaciones por Año</h2>

<?php if ($mensaje): ?>
    <p style="color:green; font-weight:bold;"><?= $mensaje ?></p>
<?php endif; ?>

<form method="post" style="max-width:400px;">
    <label>Año:</label>
    <input type="number" name="anio" value="<?= date('Y') ?>" required>

    <p style="font-size:14px;color:#555;">
        ⚠ Esto creará el saldo de vacaciones para todos los empleados activos.
        Solo debe ejecutarse una vez por año.
    </p>

    <button type="submit">Inicializar Vacaciones</button>
</form>

</body>
</html>
