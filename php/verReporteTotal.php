<?php
require_once '../config/conexion.php';

$id_form = intval($_GET['id'] ?? 0);
if (!$id_form) die("ID de formulario no especificado.");

$stmt = $conexion->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$stmt->bind_param("i", $id_form);
$stmt->execute();
$form = $stmt->get_result()->fetch_assoc();

// 1. ACTUALIZADO: Traemos nombre, dni y tel de envios
$stmt_e = $conexion->prepare("SELECT id, fecha_envio, nombre, dni, tel FROM envios WHERE formulario_id = ? ORDER BY fecha_envio ASC");
$stmt_e->bind_param("i", $id_form);
$stmt_e->execute();
$res_envios = $stmt_e->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Completo - <?php echo htmlspecialchars($form['titulo']); ?></title>
    <link rel="stylesheet" href="../css/verReporteTotal.css">
</head>
<body>

<button class="btn-flotante" onclick="window.print()">
    Descargar PDF de todas las respuestas
</button>

<div class="hoja-pdf">
    <header class="header-reporte">
        <h1>Reporte Completo</h1>
        <h2><?php echo htmlspecialchars($form['titulo']); ?></h2>
    </header>

    <?php if (!empty($form['descripcion'])): ?>
        <div class="descripcion-reporte">
            <?php echo nl2br(htmlspecialchars($form['descripcion'])); ?>
        </div>
    <?php endif; ?>

    <p style="font-size: 14px; color: #666; margin-bottom: 30px;">
        Respuestas totales recolectadas: <strong><?php echo $res_envios->num_rows; ?></strong>
    </p>

    <?php 
    $numero_orden = 1; 
    while ($envio = $res_envios->fetch_assoc()): 
    ?>
        <div class="ficha-vecino">
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px;">
                <span style="color: #1d6f42; font-weight: bold;">
                    Orden: #<?php echo $numero_orden; ?>
                </span>
                <span style="color: #999; font-size: 12px;">
                    <?php echo date("d/m/Y H:i", strtotime($envio['fecha_envio'])); ?>
                </span>
            </div>

            <div class="seccion-vecino">
                <div class="dato-fijo"><strong>Nombre:</strong> <?php echo htmlspecialchars($envio['nombre']); ?></div>
                <div class="dato-fijo"><strong>DNI:</strong> <?php echo htmlspecialchars($envio['dni']); ?></div>
                <div class="dato-fijo"><strong>Teléfono:</strong> <?php echo htmlspecialchars($envio['tel']); ?></div>
            </div>

            <?php
            // Traer las respuestas dinámicas
            $stmt_res = $conexion->prepare("
                SELECT p.pregunta_texto, r.respuesta_texto 
                FROM respuestas r 
                JOIN preguntas p ON r.pregunta_id = p.id 
                WHERE r.envio_id = ?
                ORDER BY p.id ASC
            ");
            $stmt_res->bind_param("i", $envio['id']);
            $stmt_res->execute();
            $respuestas = $stmt_res->get_result();

            while ($r = $respuestas->fetch_assoc()): ?>
                <div class="dato-linea">
                    <span class="label"><?php echo htmlspecialchars($r['pregunta_texto']); ?></span>
                    <div class="valor">
                        <?php if (strpos($r['respuesta_texto'], 'data:image') === 0): ?>
                            <img src="<?php echo $r['respuesta_texto']; ?>" class="firma-img">
                        <?php else: ?>
                            <?php echo htmlspecialchars($r['respuesta_texto']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>

    <?php 
        $numero_orden++; 
    endwhile; 
    ?>
</div>

</body>
</html>