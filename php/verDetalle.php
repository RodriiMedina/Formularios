<?php
require_once '../config/conexion.php';

$envio_id = intval($_GET['envio_id'] ?? 0);
if (!$envio_id) die("ID de envío no válido.");

// 1. Traer datos del envío y del formulario relacionado
$stmt = $conexion->prepare("
    SELECT e.id, e.formulario_id, e.fecha_envio, f.titulo 
    FROM envios e 
    JOIN formularios f ON e.formulario_id = f.id 
    WHERE e.id = ?
");
$stmt->bind_param("i", $envio_id);
$stmt->execute();
$info_envio = $stmt->get_result()->fetch_assoc();

// 2. Traer todas las respuestas de este envío específico
$stmt_res = $conexion->prepare("
    SELECT p.pregunta_texto, r.respuesta_texto 
    FROM respuestas r 
    JOIN preguntas p ON r.pregunta_id = p.id 
    WHERE r.envio_id = ?
    ORDER BY p.id ASC
");
$stmt_res->bind_param("i", $envio_id);
$stmt_res->execute();
$respuestas = $stmt_res->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Respuesta</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/verDetalle.css"> 
    <script src="../js/formularios.js" defer></script>

</head>
<body>

<div class="detalle-card">
    <div class="acciones-top no-print">
        <a href="javascript:history.back()" class="btn-volver">
            <span class="material-icons">arrow_back</span> Volver
        </a>

        <div class="grupo-botones">
            <button onclick="window.print()" class="btn-accion btn-print">
                <span class="material-icons">print</span> Imprimir a PDF
            </button>
            
            <button onclick="confirmarEliminacion(<?php echo $info_envio['id']; ?>, <?php echo $info_envio['formulario_id']; ?>)" 
                    class="btn-accion btn-delete">
                <span class="material-icons">delete</span> Eliminar Registro
            </button>
        </div>
    </div>

    <h2>Respuesta para: <?php echo htmlspecialchars($info_envio['titulo']); ?></h2>
    <p class="fecha-envio"><strong>Fecha de envío:</strong> <?php echo $info_envio['fecha_envio']; ?></p>
    <hr>

    <?php while ($row = $respuestas->fetch_assoc()): ?>
        <div class="item-respuesta">
            <span class="pregunta"><?php echo htmlspecialchars($row['pregunta_texto']); ?></span>
            <div class="respuesta">
                <?php if (strpos($row['respuesta_texto'], 'data:image') === 0): ?>
                    <img src="<?php echo $row['respuesta_texto']; ?>" class="firma-grande">
                <?php else: ?>
                    <?php echo htmlspecialchars($row['row_texto'] ?? $row['respuesta_texto']); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

</body>
</html>