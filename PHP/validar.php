<?php 
session_start();
include('db.php');

$usuario = $_POST['user'];
$contrasena = $_POST['contraseña']; // evita usar "ñ" en la BD y nombres de columnas si puedes

// Consulta a la base de datos
$consulta = "SELECT * FROM usuarios WHERE usuario='$usuario' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta) or die("Error en la consulta: " . mysqli_error($conexion));

$filas = mysqli_fetch_array($resultado);

if ($filas) {
    // Guardamos datos en la sesión
    $_SESSION['usuario'] = $usuario;
    $_SESSION['cargo'] = $filas['id_cargo']; // 1 = admin, 2 = trabajador

    // Redirección según el cargo
    if ($filas['id_cargo'] == 1) {
        header("Location: administrador.php");
    } elseif ($filas['id_cargo'] == 2) {
        header("Location: trabajador.php");
    }
} else {
    // Si el usuario o contraseña son incorrectos
    echo "<script>
            alert('Usuario o contraseña incorrectos');
            window.location = 'index.php';
          </script>";
}

mysqli_free_result($resultado);
mysqli_close($conexion);
?>
