<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Administrador</title>
  <link rel="stylesheet" href="../css/inicio.css">
</head>
<body>

  <div class="login-container">
    <form action="validar.php" method="post" class="login-form">
      <h1>Ingresar Usuario</h1>
      <hr>

      <label for="user">Usuario:</label>
      <input type="text" id="user" name="user" placeholder="Ingresa tu usuario" required>

      <label for="contraseña">Contraseña:</label>
      <input type="password" id="contraseña" name="contraseña" placeholder="Ingresa tu contraseña" required>

      <div class="buttons">
        <button type="submit">Enviar</button>
        <button type="reset">Limpiar</button>
      </div>
    </form>
  </div>

</body>
</html>
