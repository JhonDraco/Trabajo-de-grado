<?php
session_start();
include('db.php');
include('seguridad.php');

$usuario    = $_POST['user'];
$contrasena = $_POST['contraseña'];

$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$filas = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($filas) {
    $clave_bd    = $filas['clave'];
    $es_bcrypt   = password_verify($contrasena, $clave_bd);
    $es_plano    = ($clave_bd === $contrasena);
    $autenticado = $es_bcrypt || $es_plano;

    if ($autenticado) {

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

        // ✅ Auditoría DENTRO del bloque exitoso, ANTES del exit
        registrar_auditoria($conexion, 'LOGIN', 'Acceso',
            "Ingresó al sistema desde IP: " . $_SERVER['REMOTE_ADDR']);

        mysqli_close($conexion); // ✅ cerrar antes de redirigir

        $destino = match((int)$filas['cargo_id']) {
            2       => 'trabajador.php',
            default => 'administrador.php',
        };
        header("Location: $destino");
        exit();
    }
}

mysqli_close($conexion);

echo "
<!DOCTYPE html>
<html lang='es'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Acceso denegado</title>
  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css'>
  <style>
    :root {
      --green-dark:      #1f3a34;
      --green-mid:       #2b4a42;
      --green-hover:     #3f6f61;
      --white:           #ffffff;
      --white-soft:      #f7f7f7;
      --gray-light-text: #4a4f4e;
      --card-border:     #e2e2e2;
      --shadow:          0 6px 18px rgba(0,0,0,0.15);
      --radius:          12px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: rgba(31, 58, 52, 0.72);
      font-family: sans-serif;
    }
    .modal {
      background: var(--white);
      border-radius: var(--radius);
      border: 1px solid var(--card-border);
      box-shadow: var(--shadow);
      padding: 2rem 2rem 1.5rem;
      max-width: 360px;
      width: 90%;
      text-align: center;
    }
    .icono {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: var(--white-soft);
      border: 1.5px solid var(--card-border);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
    }
    .icono i    { font-size: 28px; color: var(--green-dark); }
    .titulo     { font-size: 18px; font-weight: 600; color: var(--green-dark); margin-bottom: 0.5rem; }
    .mensaje    { font-size: 14px; color: var(--gray-light-text); line-height: 1.65; margin-bottom: 1.75rem; }
    .btn {
      display: block;
      width: 100%;
      padding: 0.65rem 1rem;
      border-radius: var(--radius);
      background: var(--green-mid);
      color: var(--white);
      border: none;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s;
    }
    .btn:hover { background: var(--green-hover); }
  </style>
</head>
<body>
  <div class='modal'>
    <div class='icono'><i class='ti ti-lock'></i></div>
    <p class='titulo'>Acceso denegado</p>
    <p class='mensaje'>El usuario o la contraseña son incorrectos.<br>Por favor verifica tus datos e intenta de nuevo.</p>
    <a href='index.php' class='btn'>Volver al inicio de sesión</a>
  </div>
</body>
</html>";