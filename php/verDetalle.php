<?php
require_once '../config/conexion.php';

$envio_id = intval($_GET['envio_id'] ?? 0);
if (!$envio_id) die("ID de envío no válido.");

// 1. Traer datos del envío y del formulario relacionado
$stmt = $conexion->prepare("
    SELECT e.fecha_envio, f.titulo 
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
    <title>Detalle de Respuesta - Compromiso Urbano</title>
    <link rel="stylesheet" href="../css/resultado.css"> <!-- Reutilizamos tus estilos -->
    <style>
        .detalle-card {
            background: white;
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .item-respuesta {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .pregunta { font-weight: bold; color: #5f6368; display: block; margin-bottom: 5px; }
        .respuesta { font-size: 1.1rem; color: #202124; }
        .firma-grande { 
            max-width: 100%; 
            border: 1px solid #ddd; 
            margin-top: 10px;
            background: #fafafa;
        }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="detalle-card">
    <div class="no-print" style="margin-bottom: 20px;">
        <a href="javascript:history.back()">← Volver</a> | 
        <button onclick="window.print()">Imprimir a PDF</button>
    </div>

    <h2>Respuesta para: <?php echo htmlspecialchars($info_envio['titulo']); ?></h2>
    <p><strong>Fecha de envío:</strong> <?php echo $info_envio['fecha_envio']; ?></p>
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