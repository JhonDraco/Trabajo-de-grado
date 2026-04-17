<?php
session_start();
include('db.php');

$usuario   = $_POST['user'];
$contrasena = $_POST['contraseña'];

// Busca solo por usuario, sin comparar clave en SQL
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$filas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($filas) {

    $clave_bd   = $filas['clave'];
    $es_bcrypt  = password_verify($contrasena, $clave_bd);
    $es_plano   = ($clave_bd === $contrasena);
    $autenticado = $es_bcrypt || $es_plano;

    if ($autenticado) {

        // Si la clave era texto plano, migrarla a hash ahora mismo
        if ($es_plano && !$es_bcrypt) {
            $nuevo_hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $upd = $conexion->prepare("UPDATE usuarios SET clave = ? WHERE usuario = ?");
            $upd->bind_param("ss", $nuevo_hash, $usuario);
            $upd->execute();
            $upd->close();
        }

        

        $_SESSION['usuario']     = $usuario;
        $_SESSION['cargo_id']    = $filas['cargo_id'];
        $_SESSION['empleado_id'] = $filas['empleado_id'];

        if ($filas['cargo_id'] == 1)      header("Location: administrador.php");
        elseif ($filas['cargo_id'] == 2)  header("Location: trabajador.php");
        elseif ($filas['cargo_id'] == 3)  header("Location: administrador.php");
        elseif ($filas['cargo_id'] == 4)  header("Location: administrador.php");
        elseif ($filas['cargo_id'] == 5)  header("Location: administrador.php");
        exit();
        
    }
registrar_auditoria($conexion, 'LOGIN', 'Acceso', "Ingresó al sistema desde IP: " . $_SERVER['REMOTE_ADDR']);
}

echo "<script>
        alert('Usuario o contraseña incorrectos');
        window.location = 'index.php';
      </script>";

mysqli_close($conexion);
?>