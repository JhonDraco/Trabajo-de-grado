
<?php 
$usuario = $_POST['user'];
$contrasena = $_POST['contraseña']; // evita la ñ en la BD y aquí también

session_start();
$_SESSION['usuario'] = $usuario;

include('db.php');

$consulta = "SELECT * FROM usuarios WHERE usuario='$usuario' AND contrasena='$contrasena'";
$resultado = mysqli_query($conexion, $consulta) or die("Error en la consulta: " . mysqli_error($conexion));

$filas = mysqli_fetch_array($resultado);

if ($filas) {
    if ($filas['id_cargo'] == 1) {
        header("location:administrador.php");
    } elseif ($filas['id_cargo'] == 2) {
        header("location:trabajador.php");
    }
} else {
    include("index.php");
    echo '<h1 class="bad">Error de autentificación</h1>';
}

mysqli_free_result($resultado);
mysqli_close($conexion);
?>
