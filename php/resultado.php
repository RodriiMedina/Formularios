<?php
require_once '../config/conexion.php'; 

if (!isset($_GET['id'])) {
    die("ID de formulario no especificado.");
}

$id_form = intval($_GET['id']); 

// 1. Obtener información del formulario y su CREADOR
$stmt = $conexion->prepare("
    SELECT f.titulo, u.usuario as creador 
    FROM formularios f 
    JOIN usuarios u ON f.usuario_id = u.id 
    WHERE f.id = ?
");
$stmt->bind_param("i", $id_form);
$stmt->execute();
$form = $stmt->get_result()->fetch_assoc();

if (!$form) die("El formulario no existe.");

// 2. Obtener las preguntas (nuestras columnas dinámicas)
$stmt_q = $conexion->prepare("SELECT id, pregunta_texto FROM preguntas WHERE formulario_id = ? ORDER BY id");
$stmt_q->bind_param("i", $id_form);
$stmt_q->execute();
$res_q = $stmt_q->get_result();
$preguntas = [];
while ($row = $res_q->fetch_assoc()) {
    $preguntas[] = $row;
}

// 3. Lógica de Filtro Dinámico
$pregunta_id = intval($_GET['pregunta_filtro'] ?? 0);
$valor_buscado = trim($_GET['valor_filtro'] ?? ''); 

$sql = "SELECT id, fecha_envio, nombre, dni, tel FROM envios WHERE formulario_id = ?";

if ($pregunta_id > 0 && $valor_buscado !== '') {
    $sql .= " AND id IN (
        SELECT envio_id FROM respuestas 
        WHERE pregunta_id = ? 
        AND LOWER(respuesta_texto) LIKE LOWER(?)
    )";
}
$sql .= " ORDER BY fecha_envio DESC";

$stmt_e = $conexion->prepare($sql);

if ($pregunta_id > 0 && $valor_buscado !== '') {
    $like_val = "%$valor_buscado%";
    $stmt_e->bind_param("iis", $id_form, $pregunta_id, $like_val);
} else {
    $stmt_e->bind_param("i", $id_form);
}
$stmt_e->execute();
$envios = $stmt_e->get_result();

// 4. Obtener respuestas mapeadas [envio_id][pregunta_id]
$stmt_r = $conexion->prepare("
    SELECT r.envio_id, r.pregunta_id, r.respuesta_texto 
    FROM respuestas r
    JOIN envios e ON r.envio_id = e.id
    WHERE e.formulario_id = ?
");
$stmt_r->bind_param("i", $id_form);
$stmt_r->execute();
$res_r = $stmt_r->get_result();

$mapa_respuestas = [];
while ($row = $res_r->fetch_assoc()) {
    $mapa_respuestas[$row['envio_id']][$row['pregunta_id']] = $row['respuesta_texto'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultados: <?php echo htmlspecialchars($form['titulo']); ?></title>
    <link rel="stylesheet" href="../css/resultado.css">
    <link rel="stylesheet" href="../css/logout.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Estilos rápidos para archivos subidos */
        .img-preview-tabla { max-width: 60px; border-radius: 4px; border: 1px solid #ddd; cursor: pointer; }
        .file-link { display: flex; align-items: center; gap: 5px; text-decoration: none; color: #1a73e8; font-size: 13px; font-weight: 500; }
        .file-link:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="results-container">
    <header>
        <div class="back-link">
            <a href="dashboard.php"><span class="material-icons">arrow_back</span> Volver al Panel</a>
        </div>
        
        <div class="header-main">
            <div>
                <h1>Resultados de: <?php echo htmlspecialchars($form['titulo']); ?></h1>
                <p style="color: var(--violeta-pro); font-weight: bold; margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">
                    <span class="material-icons" style="font-size: 18px;">person</span> 
                    Creado por: <?php echo htmlspecialchars($form['creador']); ?>
                </p>
                <p>Mostrando <strong><?php echo $envios->num_rows; ?></strong> registros.</p> 
            </div>
            
            <div class="group-btns">
                <a href="verReporteTotal.php?id=<?php echo $id_form; ?>&pregunta_filtro=<?php echo $pregunta_id; ?>&valor_filtro=<?php echo urlencode($valor_buscado); ?>" 
                    class="btn-header" 
                    style="background: #a31b1b;" 
                    target="_blank">
                        <span class="material-icons">visibility</span> Reporte Completo (PDF)
                </a>

                <a href="../php/exportarExcel.php?id=<?php echo $id_form; ?>" class="btn-header" style="background: #217346;">
                    <span class="material-icons">download</span> Descargar excel
                </a>
            </div>
        </div>
    </header>

    <div class="filter-section no-print">
        <form method="GET" action="resultado.php" style="display: flex; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="id" value="<?php echo $id_form; ?>">
            
            <div style="flex: 1;">
                <label style="display: block; font-size: 11px; font-weight: bold; color: #5f6368; margin-bottom:5px; text-transform: uppercase;">Pregunta para analizar</label>
                <select name="pregunta_filtro" style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd; background: #fafafa;">
                    <option value="">-- Seleccionar pregunta --</option>
                    <?php foreach ($preguntas as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($pregunta_id == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['pregunta_texto']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1;">
                <label style="display: block; font-size: 11px; font-weight: bold; color: #5f6368; margin-bottom:5px; text-transform: uppercase;">Valor buscado (ej: "Si")</label>
                <input type="text" name="valor_filtro" value="<?php echo htmlspecialchars($valor_buscado); ?>" 
                       placeholder="Escribí aquí el filtro..." 
                       style="width: 100%; padding: 10px; border-radius: 4px; border: 1px solid #ddd;">
            </div>

            <button type="submit" class="btn-accion-form">
                <span class="material-icons">search</span> Aplicar Filtro
            </button>
            
            <?php if($pregunta_id > 0 || $valor_buscado !== ''): ?>
                <a href="resultado.php?id=<?php echo $id_form; ?>" style="color: #d93025; font-size: 12px; text-decoration: none; margin-bottom:12px; font-weight: bold;">[ Limpiar Filtros ]</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Fecha de Envío</th>
                    <th>Nombre completo</th>
                    <th>DNI</th>
                    <th>Teléfono</th>
                    <?php foreach ($preguntas as $p): ?>
                        <th><?php echo htmlspecialchars($p['pregunta_texto']); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($envios->num_rows > 0): ?>
                    <?php while ($envio = $envios->fetch_assoc()): ?>
                        <tr>
                            <td class="date-cell">
                                <a href="verDetalle.php?envio_id=<?php echo $envio['id']; ?>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 8px;">
                                    <?php echo date("d/m/Y H:i", strtotime($envio['fecha_envio'])); ?>
                                    <span class="material-icons" style="font-size: 16px; color: var(--violeta-pro);">open_in_new</span>
                                </a>
                            </td>
                            <td style="font-weight: 500;"><?php echo htmlspecialchars($envio['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($envio['dni']); ?></td>
                            <td><?php echo htmlspecialchars($envio['tel']); ?></td>
                            
                            <?php foreach ($preguntas as $p): ?>
                                <td>
    <?php 
    if (isset($mapa_respuestas[$envio['id']][$p['id']])) {
        $valor = $mapa_respuestas[$envio['id']][$p['id']];
        $extensiones_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($valor, PATHINFO_EXTENSION));
        
        // IMPORTANTE: Definir bien la ruta relativa al archivo uploads
        $ruta_archivo = "../uploads/" . $valor;

        if (strpos($valor, 'data:image') === 0) {
            echo '<img src="' . $valor . '" class="img-firma-tabla" alt="Firma">';
        } elseif (in_array($ext, $extensiones_img)) {
            // Verificamos si el archivo físico existe en la carpeta
            if (file_exists($ruta_archivo)) {
                echo '<a href="' . $ruta_archivo . '" target="_blank">
                        <img src="' . $ruta_archivo . '" class="img-preview-tabla" title="Click para ampliar">
                      </a>';
            } else {
                echo '<span style="color:#d93025; font-size:11px;">
                        <span class="material-icons" style="font-size:12px;">error_outline</span> 
                        No encontrado en /uploads
                      </span>';
            }
        } elseif ($ext !== '') {
            echo '<a href="' . $ruta_archivo . '" target="_blank" class="file-link">
                    <span class="material-icons">description</span> Ver archivo
                  </a>';
        } else {
            echo htmlspecialchars($valor);
        }
    } else {
        echo '<span class="empty">sin datos</span>';
    }
    ?>
</td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo count($preguntas) + 4; ?>" style="text-align: center; padding: 80px; color: #777;">
                            <span class="material-icons" style="font-size: 48px; display: block; margin-bottom: 15px; color: #ccc;">sentiment_dissatisfied</span>
                            No se encontraron resultados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="sidebar-footer" style="text-align:center; margin-top: 50px; padding-bottom: 20px;">
    <a href="logout.php" class="btn-logout" style="text-decoration:none; color:#d93025; font-weight: bold; font-size: 14px;">
        <span class="material-icons" style="vertical-align: middle; font-size: 18px;">logout</span> Cerrar Sesión
    </a>
</div>

</body>
</html>