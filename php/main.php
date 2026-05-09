<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario = $_SESSION['rol']; // Para usarlo después si queremos ocultar botones
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Formularios</title>
    <link rel="stylesheet" href="\formularios\css\main.css">
    <link rel="stylesheet" href="\formularios\css\logout.css">


        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>
<body>

<div class="main-container">
    <header>
        <h1>Bienvenido, <span><?php echo htmlspecialchars($nombre_usuario); ?></span></h1>
        <p class="subtitle">¿Qué querés hacer hoy?</p>
    </header>

    <div class="menu-grid">
        <a href="crearformulario.php" class="menu-card">
            <div class="icon">➕</div>
            <h3>Crear nuevo formulario</h3>
            <p>Generá una nueva entrada en el sistema.</p>
        </a>

        <a href="dashboard.php" class="menu-card">
            <div class="icon">📂</div>
            <h3>Ver formularios anteriores</h3>
            <p>Revisá el historial de datos cargados.</p>
        </a>
    </div>
</div>

<div class="sidebar-footer">
       <a href="logout.php" class="btn-logout">
            <span class="material-icons">logout</span>
            Cerrar Sesión
         </a>
    </div>



</body>
</html>