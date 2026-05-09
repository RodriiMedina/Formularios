<?php
session_start();
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

require_once '../config/conexion.php';


if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}
    

$id_sesion = $_SESSION['usuario_id'];
$rol_sesion = $_SESSION['rol'];

if ($rol_sesion == 1) {
    $sql = "SELECT f.*, u.usuario AS autor, 
            (SELECT COUNT(*) FROM envios e WHERE e.formulario_id = f.id) as total_respuestas 
            FROM formularios f 
            JOIN usuarios u ON f.usuario_id = u.id 
            ORDER BY f.fecha_creacion DESC";
} else {
    $sql = "SELECT f.*, 
            (SELECT COUNT(*) FROM envios e WHERE e.formulario_id = f.id) as total_respuestas 
            FROM formularios f 
            WHERE f.usuario_id = $id_sesion 
            ORDER BY f.fecha_creacion DESC";
}


$resultado = $conexion->query($sql);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Compromiso Urbano</title>
    <link rel="stylesheet" href="../css/dashboard.css">
        <link rel="stylesheet" href="../css/logout.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<script src="../js/formularios.js"></script>
<body>

<div class="dashboard-container">
    <header class="main-header">
        <div class="header-text">
            <h1>Mis Formularios</h1>
            <p>Gestioná tus encuestas y revisá los resultados.</p>
        </div>

        
        <a href="crearFormulario.php" class="btn-nuevo">
            <span class="material-icons">add</span>
            <span>Nuevo Formulario</span>
        </a>

        <div class="footer-actions">
            <a href="logout.php" class="btn-logout">
                <span class="material-icons">logout</span>
                Cerrar Sesión
            </a>
        </div>
    </header>

    <div class="forms-grid">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while($f = $resultado->fetch_assoc()): 
                // Construimos la URL para compartir
                $url_formulario = "http://localhost/formularios/php/verFormulario.php?id=" . $f['id']; 
            ?>
                <div class="form-card">
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($f['titulo']); ?></h3>
                        <p class="description">
                            <?php echo $f['descripcion'] ? htmlspecialchars($f['descripcion']) : 'Sin descripción.'; ?>
                        </p>
                        <div class="badge">
                            <span class="material-icons">assignment</span> 
                            <strong><?php echo $f['total_respuestas']; ?></strong> &nbsp;respuestas
                        </div>
                    </div>

                    <div class="card-actions">
                        <a href="verFormulario.php?id=<?php echo $f['id']; ?>" class="action-btn view" title="Ver Formulario">
                            <span class="material-icons">visibility</span>
                        </a>
                        
                        <a href="resultado.php?id=<?php echo $f['id']; ?>" class="action-btn results" title="Ver Resultados">
                            <span class="material-icons">analytics</span>
                        </a>

                        <a href="editarFormulario.php?id=<?php echo $f['id']; ?>" class="action-btn edit" title="Editar">
                         <span class="material-icons">edit</span>
                        </a>

                        <button class="action-btn share" onclick="copiarLink('<?php echo $url_formulario; ?>', this)" title="Copiar Enlace">
                            <span class="material-icons">share</span>
                        </button>
                        
                        <button class="action-btn delete" onclick="confirmarBorrado(<?php echo $f['id']; ?>)" title="Eliminar">
                            <span class="material-icons">delete</span>
                        </button>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <span class="material-icons">post_add</span>
                <p>No hay formularios creados todavía.</p>
                <a href="crearFormulario.php">Crear el primero</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>