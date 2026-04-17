<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: index.php"); exit(); }

include("db.php");

if (!isset($_GET['id'])) {
    header("Location: pagos_nomina.php");
    exit();
}

$id_nomina = intval($_GET['id']);

// obtener total de la nómina
$sql = "SELECT SUM(total_pagar) AS total FROM detalle_nomina WHERE id_nomina = $id_nomina";
$res = mysqli_query($conexion, $sql);
$data = mysqli_fetch_assoc($res);
$total_nomina = $data['total'] ?? 0;

// registrar pago
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fecha = $_POST['fecha_pago'];
    $metodo = mysqli_real_escape_string($conexion, $_POST['metodo']);
    $notas = mysqli_real_escape_string($conexion, $_POST['notas']);

    mysqli_query($conexion, "
        INSERT INTO pagos (id_nomina, fecha_pago, total_pagado, metodo, notas)
        VALUES ($id_nomina, '$fecha', $total_nomina, '$metodo', '$notas')
    ");

    // actualizar estado de nomina
    mysqli_query($conexion, "UPDATE nomina SET estado='pagada' WHERE id_nomina=$id_nomina");
    registrar_auditoria($conexion, 'PAGAR', 'Nómina', "Pagó nómina ID $id_nomina — Total: $total_nomina Bs.");

    header("Location: pagos_nomina.php?ok=1");
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Pagar Nómina</title>


</head>
<body>

<h2>💰 Registrar Pago de la Nómina #<?= $id_nomina ?></h2>

<p><strong>Total a Pagar:</strong> Bs <?= number_format($total_nomina, 2) ?></p>

<form method="POST">

    <label>Fecha de Pago:
        <input type="date" name="fecha_pago" required>
    </label><br><br>

    <label>Método:
        <select name="metodo">
            <option value="transferencia">Transferencia</option>
            <option value="efectivo">Efectivo</option>
            <option value="pago móvil">Pago Móvil</option>
        </select>
    </label><br><br>

    <label>Notas:
        <textarea name="notas" rows="3" cols="40"></textarea>
    </label><br><br>

    <button type="submit">Registrar Pago</button>

</form>

</body>
</html>
