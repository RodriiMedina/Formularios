<?php
session_start();
// Headers para evitar el cacheo (excelente para la seguridad al cerrar sesión)
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 

require_once '../config/conexion.php';

// Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$id_sesion = $_SESSION['usuario_id'];
$rol_sesion = $_SESSION['rol'];

// Definimos la consulta según el rol
if ($rol_sesion == 1) {
    // ADMIN: Ve todos los formularios de todos los usuarios
    $sql = "SELECT f.*, u.usuario AS autor, 
            (SELECT COUNT(*) FROM envios e WHERE e.formulario_id = f.id) as total_respuestas 
            FROM formularios f 
            JOIN usuarios u ON f.usuario_id = u.id 
            ORDER BY f.fecha_creacion DESC";
} else {
    // USUARIO: Solo ve sus propios formularios
    // También hacemos el JOIN para que la variable 'autor' exista y no te tire error el HTML
    $sql = "SELECT f.*, u.usuario AS autor,
            (SELECT COUNT(*) FROM envios e WHERE e.formulario_id = f.id) as total_respuestas 
            FROM formularios f 
            JOIN usuarios u ON f.usuario_id = u.id
            WHERE f.usuario_id = $id_sesion 
            ORDER BY f.fecha_creacion DESC";
}

$busqueda = $conexion->real_escape_string($_GET['busqueda'] ?? '');
$autor_id = intval($_GET['autor'] ?? 0);
$orden = $_GET['orden'] ?? 'recientes';

// Base de la consulta
$condiciones = [];
if ($rol_sesion == 1) {
    if ($autor_id > 0) $condiciones[] = "f.usuario_id = $autor_id";
} else {
    $condiciones[] = "f.usuario_id = $id_sesion";
}

if (!empty($busqueda)) {
    $condiciones[] = "f.titulo LIKE '%$busqueda%'";
}

// Unimos las condiciones
$where_sql = count($condiciones) > 0 ? "WHERE " . implode(" AND ", $condiciones) : "";

// Definimos el orden
$order_sql = "ORDER BY f.fecha_creacion DESC"; // Por defecto
if ($orden == 'mas_respuestas') $order_sql = "ORDER BY total_respuestas DESC";
if ($orden == 'menos_respuestas') $order_sql = "ORDER BY total_respuestas ASC";

$sql = "SELECT f.*, u.usuario AS autor, 
        (SELECT COUNT(*) FROM envios e WHERE e.formulario_id = f.id) as total_respuestas 
        FROM formularios f 
        JOIN usuarios u ON f.usuario_id = u.id 
        $where_sql 
        $order_sql";

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
    </header>

    <section class="filtros-dashboard no-print">
        <form method="GET" action="dashboard.php" class="filter-grid">
            <div class="filter-group">
                <label><span class="material-icons">search</span> Título</label>
                <input type="text" name="busqueda" value="<?php echo htmlspecialchars($_GET['busqueda'] ?? ''); ?>" placeholder="Buscar...">
            </div>

            <?php if ($rol_sesion == 1): ?>
            <div class="filter-group">
                <label><span class="material-icons">person</span> Creador</label>
                <select name="autor">
                    <option value="">Todos los usuarios</option>
                    <?php
                    $u_query = $conexion->query("SELECT id, usuario FROM usuarios");
                    while($u = $u_query->fetch_assoc()): ?>
                        <option value="<?php echo $u['id']; ?>" <?php echo (isset($_GET['autor']) && $_GET['autor'] == $u['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($u['usuario']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="filter-group">
                <label><span class="material-icons">analytics</span> Ordenar por</label>
                <select name="orden">
                    <option value="recientes" <?php echo ($_GET['orden'] ?? '') == 'recientes' ? 'selected' : ''; ?>>Más recientes</option>
                    <option value="mas_respuestas" <?php echo ($_GET['orden'] ?? '') == 'mas_respuestas' ? 'selected' : ''; ?>>Más respuestas</option>
                    <option value="menos_respuestas" <?php echo ($_GET['orden'] ?? '') == 'menos_respuestas' ? 'selected' : ''; ?>>Menos respuestas</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filtrar">Filtrar</button>
                <a href="dashboard.php" class="btn-limpiar">Limpiar</a>
            </div>
        </form>
    </section>

    <div class="forms-grid">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while($f = $resultado->fetch_assoc()): 
                $url_formulario = "http://localhost/formularios/php/verFormulario.php?id=" . $f['id']; 
            ?>
                <div class="form-card">


    <?php 
    $icono_pausa = ($f['estado'] == 1) ? 'pause_circle' : 'play_circle';
    $texto_pausa = ($f['estado'] == 1) ? 'Pausar' : 'Activar';
    $color_pausa = ($f['estado'] == 1) ? '#f57c00' : '#2e7d32';
    $nuevo_estado = ($f['estado'] == 1) ? 0 : 1;
?>



                    <div class="card-content">
                <h3><?php echo htmlspecialchars($f['titulo']); ?></h3>
                <p class="descripcion">
                    <?php echo $f['descripcion'] ? htmlspecialchars(substr($f['descripcion'], 0, 100)) . '...' : 'Sin descripción.'; ?>
                </p>
                
                <div class="card-meta">
                    <span class="badge-respuestas">
                        <span class="material-icons">assignment</span> 
                        <strong><?php echo $f['total_respuestas']; ?></strong> respuestas
                    </span>
                    <div class="autor-tag">
                        <span class="material-icons">person_outline</span>
                        <span>Por: <?php echo htmlspecialchars($f['autor']); ?></span>
                    </div>
                </div>
            </div>

            <hr class="card-divider">

            <div class="card-actions">
                <a href="verFormulario.php?id=<?php echo $f['id']; ?>" class="action-btn view" title="Ver Formulario">
                    <span class="material-icons">visibility</span>
                </a>

                <a href="resultado.php?id=<?php echo $f['id']; ?>" class="action-btn results" title="Ver Resultados">
                    <span class="material-icons">analytics</span>
                </a>

                <a href="cambiarEstado.php?id=<?php echo $f['id']; ?>&estado=<?php echo $nuevo_estado; ?>" 
                class="action-btn" 
                title="<?php echo $texto_pausa; ?>"
                style="color: <?php echo $color_pausa; ?>;">
                    <span class="material-icons"><?php echo $icono_pausa; ?></span>
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
                <span class="material-icons">search_off</span>
                <p>No se encontraron formularios.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>