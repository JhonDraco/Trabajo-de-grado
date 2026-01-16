<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

if (!isset($_GET['id'])) {
    die("ID de vacación no especificado");
}

$id_vacacion = intval($_GET['id']);

/* =========================
   OBTENER DATOS DE VACACIÓN
========================= */
$sql = "
SELECT v.*, e.nombre, e.apellido
FROM vacaciones v
INNER JOIN empleados e ON v.empleado_id = e.id
WHERE v.id_vacacion = $id_vacacion
";

$res = mysqli_query($conexion, $sql);
$vacacion = mysqli_fetch_assoc($res);

if (!$vacacion) {
    die("Vacación no encontrada");
}

/* ❗ Evitar reprocesar */
if ($vacacion['estado'] !== 'pendiente') {
    die("Esta solicitud ya fue procesada.");
}

$anio = date('Y', strtotime($vacacion['fecha_inicio']));
$empleado_id = $vacacion['empleado_id'];
$dias_habiles = intval($vacacion['dias_habiles']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Aprobar Vacaciones</title>
<link rel="stylesheet" href="../css/vacaciones.css">
</head>
<body>

<h2>🌴 Aprobar Vacaciones</h2>

<div class="card">
    <p><strong>Empleado:</strong> <?= $vacacion['nombre'] ?> <?= $vacacion['apellido'] ?></p>
    <p><strong>Periodo:</strong> <?= $vacacion['fecha_inicio'] ?> → <?= $vacacion['fecha_fin'] ?></p>
    <p><strong>Días solicitados:</strong> <?= $vacacion['dias_solicitados'] ?></p>
    <p><strong>Días hábiles:</strong> <?= $vacacion['dias_habiles'] ?></p>
    <p><strong>Días feriados:</strong> <?= $vacacion['dias_feriados'] ?></p>
    <p><strong>Estado actual:</strong> <?= strtoupper($vacacion['estado']) ?></p>
</div>

<form method="post">
    <label>Observaciones:</label>
    <textarea name="observaciones"></textarea>

    <button type="submit" name="accion" value="aprobar">✅ Aprobar</button>
    <button type="submit" name="accion" value="rechazar">❌ Rechazar</button>
</form>

</body>
</html>

<?php
/* =========================
   PROCESAR ACCIÓN
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'];
    $obs = mysqli_real_escape_string($conexion, $_POST['observaciones']);

    mysqli_begin_transaction($conexion);

    try {

        /* =====================
           RECHAZAR
        ===================== */
        if ($accion === 'rechazar') {

            mysqli_query($conexion, "
                UPDATE vacaciones
                SET estado='rechazada',
                    observaciones='$obs'
                WHERE id_vacacion=$id_vacacion
            ");

            mysqli_commit($conexion);
            header("Location: vacaciones.php?msg=rechazada");
            exit();
        }

        /* =====================
           APROBAR
        ===================== */
        if ($accion === 'aprobar') {

            /* Obtener saldo */
            $resSaldo = mysqli_query($conexion, "
                SELECT * FROM vacaciones_saldo
                WHERE empleado_id=$empleado_id AND anio=$anio
            ");

            if (mysqli_num_rows($resSaldo) == 0) {
                throw new Exception("El empleado no tiene saldo de vacaciones para el año $anio");
            }

            $saldo = mysqli_fetch_assoc($resSaldo);

            $nuevo_disfrutado = $saldo['dias_disfrutados'] + $dias_habiles;
            $nuevo_pendiente  = $saldo['dias_pendientes'] - $dias_habiles;

            if ($nuevo_pendiente < 0) {
                throw new Exception("El empleado no tiene suficientes días disponibles");
            }

            /* Actualizar vacaciones */
            mysqli_query($conexion, "
                UPDATE vacaciones
                SET estado='aprobada',
                    observaciones='$obs'
                WHERE id_vacacion=$id_vacacion
            ");

            /* Actualizar saldo */
            mysqli_query($conexion, "
                UPDATE vacaciones_saldo
                SET dias_disfrutados=$nuevo_disfrutado,
                    dias_pendientes=$nuevo_pendiente,
                    actualizado_en=NOW()
                WHERE id_saldo={$saldo['id_saldo']}
            ");

            mysqli_commit($conexion);
            header("Location: vacaciones.php?msg=aprobada");
            exit();
        }

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        die("Error: " . $e->getMessage());
    }
}
?>
