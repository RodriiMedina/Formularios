<?php
require_once '../config/conexion.php';

$id_form = intval($_GET['id'] ?? 0);
if (!$id_form) die("ID de formulario no especificado.");

// 1. Obtener información del título y descripción
$stmt_f = $conexion->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$stmt_f->bind_param("i", $id_form);
$stmt_f->execute();
$form = $stmt_f->get_result()->fetch_assoc();

// 2. Lógica de Filtro (Recibimos desde la URL)
$pregunta_id = intval($_GET['pregunta_filtro'] ?? 0);
$valor_buscado = trim($_GET['valor_filtro'] ?? '');

// Consulta base: Aquí es donde aplicamos el filtro
$sql = "SELECT id, fecha_envio, nombre, dni, tel FROM envios WHERE formulario_id = ?";

if ($pregunta_id > 0 && $valor_buscado !== '') {
    $sql .= " AND id IN (
        SELECT envio_id FROM respuestas 
        WHERE pregunta_id = ? 
        AND LOWER(respuesta_texto) LIKE LOWER(?)
    )";
}
$sql .= " ORDER BY fecha_envio ASC"; // ASC para que el PDF salga en orden cronológico

$stmt = $conexion->prepare($sql);

if ($pregunta_id > 0 && $valor_buscado !== '') {
    $like_val = "%$valor_buscado%";
    $stmt->bind_param("iis", $id_form, $pregunta_id, $like_val);
} else {
    $stmt->bind_param("i", $id_form);
}

$stmt->execute();
$resultado = $stmt->get_result(); // Esta es la variable que vamos a usar abajo
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Completo - <?php echo htmlspecialchars($form['titulo']); ?></title>
    <link rel="stylesheet" href="../css/verReporteTotal.css">
</head>
<body>

<button class="btn-flotante no-print" onclick="window.print()">
    Descargar PDF de todas las respuestas
</button>

<div class="hoja-pdf">
    <header class="header-reporte">
        <h1>Reporte Completo</h1>
        <h2><?php echo htmlspecialchars($form['titulo']); ?></h2>
        <?php if ($pregunta_id > 0): ?>
            <p style="color: #666; font-style: italic;">Filtro aplicado: "<?php echo htmlspecialchars($valor_buscado); ?>"</p>
        <?php endif; ?>
    </header>

    <?php if (!empty($form['descripcion'])): ?>
        <div class="descripcion-reporte">
            <?php echo nl2br(htmlspecialchars($form['descripcion'])); ?>
        </div>
    <?php endif; ?>

    <p style="font-size: 14px; color: #666; margin-bottom: 30px;">
        Respuestas encontradas con este criterio: <strong><?php echo $resultado->num_rows; ?></strong>
    </p>

    <?php 
    $numero_orden = 1; 
    // USAMOS $resultado EN LUGAR DE $res_envios
    while ($envio = $resultado->fetch_assoc()): 
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
            // Traer las respuestas dinámicas de este envío
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
                        <?php 
                        $valor = $r['respuesta_texto'];
                        $extensiones_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $ext = strtolower(pathinfo($valor, PATHINFO_EXTENSION));
                        $ruta_archivo = "../uploads/" . $valor;

                        // 1. Si es una firma (Base64)
                        if (strpos($valor, 'data:image') === 0): ?>
                            <img src="<?php echo $valor; ?>" class="firma-img">

                        <?php 
                        // 2. Si es una imagen (DNI, Fotos, etc.)
                        elseif (in_array($ext, $extensiones_img)): ?>
                            <?php if (file_exists($ruta_archivo)): ?>
                                <img src="<?php echo $ruta_archivo; ?>" class="reporte-img-adjunta">
                            <?php else: ?>
                                <span style="color: #d93025; font-size: 11px;">[Archivo no encontrado]</span>
                            <?php endif; ?>

                        <?php 
                        // 3. Si es un archivo PDF/Documento
                        elseif ($ext !== ''): ?>
                            <div class="archivo-adjunto-reporte">
                                <span class="material-icons" style="font-size: 16px;">attachment</span>
                                Archivo: <?php echo htmlspecialchars($valor); ?>
                            </div>

                        <?php 
                        // 4. Si es texto normal
                        else: ?>
                            <?php echo nl2br(htmlspecialchars($valor)); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endwhile; ?>
</body>
</html>