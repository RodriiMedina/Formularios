<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/formularios/css/login.css">
</head>
<body>

<div class="login-box">
    <h2>Iniciar Sesión</h2>

    <form action="procesar.php" method="POST">
        
        <input type="text" name="usuario" placeholder="Usuario" required>
        
        <input type="password" name="password" placeholder="Contraseña" required>

        <button type="submit">Entrar</button>
        
    </form>
</div>

</body>
</html>