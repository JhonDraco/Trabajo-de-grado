<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Administrador</title>
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
:root {
  --green-dark: #1f3a34;
  --green-mid: #2b4a42;
  --green-hover: #3f6f61;
  --white: #ffffff;
  --white-soft: #f7f7f7;
  --gray-light-text: #4a4f4e;
  --card-border: #e2e2e2;
  --shadow: 0 6px 18px rgba(0,0,0,0.15);
  --radius: 12px;
}

/* Reset */
* { box-sizing:border-box; margin:0; padding:0; font-family:Inter, Arial, sans-serif; }

body {
  display:flex;
  justify-content:center;
  align-items:center;
  min-height:100vh;
  background: linear-gradient(135deg, var(--green-dark), var(--green-mid), var(--white));
  background-attachment: fixed;
  color: var(--gray-light-text);
}

/* Caja de login */
.login-container {
  background: var(--white);
  padding: 35px 40px;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  width: 400px;
}

.login-form h1 {
  text-align:center;
  color: var(--green-dark);
  margin-bottom:20px;
  font-size:24px;
}

.login-form hr {
  border:none;
  height:1px;
  background: var(--card-border);
  margin-bottom:25px;
}

.login-form label {
  display:block;
  margin-bottom:5px;
  font-weight:500;
}

.login-form input {
  width:100%;
  padding:10px;
  margin-bottom:15px;
  border:1px solid #ccc;
  border-radius:8px;
}

.buttons {
  display:flex;
  gap:10px;
}

.buttons button {
  flex:1;
  padding:12px;
  border:none;
  border-radius:8px;
  font-weight:600;
  cursor:pointer;
  color:white;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
}

.buttons button[type="submit"] {
  background: var(--green-mid);
}

.buttons button[type="submit"]:hover {
  background: var(--green-hover);
}

.buttons button[type="reset"] {
  background: #999;
}

.buttons button[type="reset"]:hover {
  background: #777;
}

/* Footer */
footer {
  text-align:center;
  margin-top:25px;
  font-size:14px;
  color: wheat;
  position: fixed;
  bottom:10px;
  width:100%;
}

.login-form .icon {
  color: var(--green-mid);
}
</style>
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
