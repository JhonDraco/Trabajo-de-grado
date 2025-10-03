

<?php
include "db.php";

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

    echo "✅ Empleado registrado con éxito.";
}
?>

<form method="POST">
    Cédula: <input type="text" name="cedula" required><br>
    Nombre: <input type="text" name="nombre" required><br>
    Apellido: <input type="text" name="apellido" required><br>
    Dirección: <textarea name="direccion"></textarea><br>
    Teléfono: <input type="text" name="telefono"><br>
    Email: <input type="email" name="email"><br>
    Fecha de ingreso: <input type="date" name="fecha_ingreso" required><br>
    Cargo: 
    <select name="cargo_id">
        <?php
        $result = $conn->query("SELECT * FROM cargo");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='{$row['id']}'>{$row['nombre_cargo']}</option>";
        }
        ?>
    </select><br>
    Salario base: <input type="number" step="0.01" name="salario_base" required><br>
    <button type="submit">Guardar</button>
</form>
