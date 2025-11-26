<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Administrador</title>
<link rel="stylesheet" href="../css/inicio.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

</head>

<body>

<div class="login-container">
  <form action="validar.php" method="post" class="login-form">
    <h1><i class="ri-shield-user-line"></i> Ingresar Usuario</h1>
    <hr>

    <label for="user"><i class="ri-user-line icon"></i> Usuario:</label>
    <input type="text" id="user" name="user" placeholder="Ingresa tu usuario" required>

    <label for="contraseña"><i class="ri-lock-line icon"></i> Contraseña:</label>
    <input type="password" id="contraseña" name="contraseña" placeholder="Ingresa tu contraseña" required>

    <div class="buttons">
      <button type="submit"><i class="ri-login-box-line"></i> Ingresar</button>
      <button type="reset"><i class="ri-refresh-line"></i> Limpiar</button>
    </div>
  </form>
</div>

<footer>
  © 2025 Sistema de RRHH – Todos los derechos reservados
</footer>

</body>
</html>
