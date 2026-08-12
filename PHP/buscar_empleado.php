<?php
// Desactivar la impresión de errores en pantalla para que no ensucien el JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

include("seguridad.php");
verificarSesion();
if (!puedeListarEmpleados()) {
    echo json_encode(['error' => 'Sin permiso']);
    exit;
}

include("db.php");

if (isset($_POST['cedula'])) {
    $cedula = trim($_POST['cedula']);

    $stmt = mysqli_prepare($conexion, "SELECT id, nombre, apellido, salario_base, fecha_ingreso FROM empleados WHERE cedula = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $cedula);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $empleado = mysqli_fetch_assoc($resultado);
        echo json_encode($empleado);
    } else {
        // Si no hay resultados, enviamos un objeto vacío que no rompa el fetch
        echo json_encode([]);
    }
} else {
    echo json_encode(['error' => 'No se recibio cedula']);
}

mysqli_close($conexion);
?>
