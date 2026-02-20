<?php
// Desactivar la impresión de errores en pantalla para que no ensucien el JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Prueba de conexión
$conexion = new mysqli("localhost", "root", "", "rrhh");

if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexion a BD']);
    exit;
}

if (isset($_POST['cedula'])) {
    $cedula = trim($_POST['cedula']);
    
    // Consulta exacta
    $query = "SELECT nombre, apellido, salario_base, fecha_ingreso FROM empleados WHERE cedula = '$cedula' LIMIT 1";
    $resultado = $conexion->query($query);

    if ($resultado && $resultado->num_rows > 0) {
        $empleado = $resultado->fetch_assoc();
        echo json_encode($empleado);
    } else {
        // Si no hay resultados, enviamos un objeto vacío que no rompa el fetch
        echo json_encode([]); 
    }
} else {
    echo json_encode(['error' => 'No se recibio cedula']);
}

$conexion->close();
?>