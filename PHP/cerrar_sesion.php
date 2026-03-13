<?php
session_start();

// Limpiar todas las variables de sesión
$_SESSION = array();

//borrar toda la sesion para que no se repita
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al index o login
header("Location: index.php");
exit();
?>