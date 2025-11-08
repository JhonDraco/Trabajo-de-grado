<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleado</title>
    <link rel="stylesheet" href="../css/formulario_para_registrar_empleado.css">

</head>
<body>

<?php
include "db.php";
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $fecha_ingreso = $_POST['fecha_ingreso'];
    $cargo_id = $_POST['cargo_id'];
    $salario_base = $_POST['salario_base'];

    $stmt = $conn->prepare("INSERT INTO empleados (cedula, nombre, apellido, direccion, telefono, email, fecha_ingreso, cargo_id, salario_base) 
                            VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssssid", $cedula, $nombre, $apellido, $direccion, $telefono, $email, $fecha_ingreso, $cargo_id, $salario_base);
    $stmt->execute();

    $mensaje = "✅ Empleado registrado con éxito.";
}
?>

<div class="formulario">
    <h2>Registrar Empleado</h2>

    <?php if (!empty($mensaje)) echo "<div class='mensaje'>$mensaje</div>"; ?>

    <form method="POST">
        <label for="cedula">Cédula:</label>
        <input type="text" name="cedula" id="cedula" required>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" required>

        <label for="apellido">Apellido:</label>
        <input type="text" name="apellido" id="apellido" required>

        <label for="direccion">Dirección:</label>
        <textarea name="direccion" id="direccion"></textarea>

        <label for="telefono">Teléfono:</label>
        <input type="text" name="telefono" id="telefono">

        <label for="email">Email:</label>
        <input type="email" name="email" id="email">

        <label for="fecha_ingreso">Fecha de ingreso:</label>
        <input type="date" name="fecha_ingreso" id="fecha_ingreso" required>
        
        <label for="cargo_id">Cargo:</label>
       <select name="cargo_id" id="cargo_id">
            
           /* $result = $conn->query("SELECT * FROM cargo");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['cargo_id']}'>{$row['nombre_cargo']}</option>";
                }
            } else {
                echo "<option>Error al cargar cargos</option>";
            }
            ?*/
        </select>        

        <label for="salario_base">Salario base:</label>
        <input type="number" step="0.01" name="salario_base" id="salario_base" required>

        <button type="submit">enviar</button>
        <a href="administrador.php">volver</a>
    </form>
</div>

</body>
</html>

