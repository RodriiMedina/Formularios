<?php
session_start(); // Es vital iniciarla para poder cerrarla[cite: 1]

// 1. Vaciamos todas las variables de sesión
$_SESSION = array();

// 2. Si se usan cookies de sesión, las borramos también
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Destruimos la sesión en el servidor
session_destroy();

// 4. Redireccionamos al login o index
header("Location: index.php");
exit();
?>