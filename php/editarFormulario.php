 <?php
require_once '../config/conexion.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
if (!$id) { header("Location: dashboard.php"); exit; }

$f_query = $conexion->prepare("SELECT * FROM formularios WHERE id = ?");
$f_query->bind_param("i", $id);
$f_query->execute();
$form = $f_query->get_result()->fetch_assoc();

$p_query = $conexion->prepare("SELECT * FROM preguntas WHERE formulario_id = ?");
$p_query->bind_param("i", $id);
$p_query->execute();
$res = $p_query->get_result();

$preguntas = []; 
while ($fila = $res->fetch_assoc()) { 
    $fila['opciones'] = json_decode($fila['opciones_json'], true) ?? [];
    $preguntas[] = $fila;
}
?>
<!--

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Formulario</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/crearFormulario.css">
    <link rel="stylesheet" href="../css/editarFormulario.css">
    <link rel="stylesheet" href="../css/logout.css">    
</head>
<body>
    <div class="back-link">
        <a href="dashboard.php"><span class="material-icons">arrow_back</span> Volver</a>
    </div>

    <div class="container">
        <div class="header-card form-card active">
            <input type="text" class="title-input" value="<?php echo htmlspecialchars($form['titulo']); ?>">
            <input type="text" class="header-description" value="<?php echo htmlspecialchars($form['descripcion']); ?>">
        </div>

        <div id="questions-container"></div>

        <div class="sidebar">
            <button class="side-btn" onclick="addQuestion()"><span class="material-icons">add_circle_outline</span></button>
        </div>
    </div>

   <button type="button" class="side-btn save-btn" onclick="actualizarFormulario()" title="Guardar Cambios">
    <span class="material-icons">save</span>
    </button>

    <script src="../js/formularios.js"></script>
    <script>
        window.onload = () => {
            const datos = <?php echo json_encode($preguntas); ?>;
            if (datos.length > 0) {
                datos.forEach(p => addQuestion(p));
            } else {
                addQuestion();
            }
        };
    </script>

    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout">
                <span class="material-icons">logout</span>
                Cerrar Sesión
            </a>
    </div>
</body>
</html> -->