<?php
require_once '../config/conexion.php'; 

if (!isset($_GET['id'])) {
    die("ID de formulario no especificado.");
}

// Asegurate de que termine en ; y no en :
$id_form = intval($_GET['id']); 

// 1. Obtener información del formulario
$stmt = $conexion->prepare("SELECT titulo FROM formularios WHERE id = ?");
$stmt->bind_param("i", $id_form);
$stmt->execute();
$form = $stmt->get_result()->fetch_assoc();

// 2. Obtener las preguntas (serán nuestras columnas)
$stmt_q = $conexion->prepare("SELECT id, pregunta_texto FROM preguntas WHERE formulario_id = ? ORDER BY id");
$stmt_q->bind_param("i", $id_form);
$stmt_q->execute();
$res_q = $stmt_q->get_result();
$preguntas = [];
while ($row = $res_q->fetch_assoc()) {
    $preguntas[] = $row;
}

// 3. Obtener todos los envíos (serán nuestras filas)
$stmt_e = $conexion->prepare("SELECT id, fecha_envio, nombre, dni, tel FROM envios WHERE formulario_id = ? ORDER BY fecha_envio DESC");
$stmt_e->bind_param("i", $id_form);
$stmt_e->execute();
$res_e = $stmt_e->get_result();
$envios = [];
while ($row = $res_e->fetch_assoc()) {
    $envios[] = $row;
}

// 4. Obtener TODAS las respuestas de este formulario
$stmt_r = $conexion->prepare("SELECT envio_id, pregunta_id, respuesta_texto FROM respuestas WHERE formulario_id = ?");
$stmt_r->bind_param("i", $id_form);
$stmt_r->execute();
$res_r = $stmt_r->get_result();

// Mapeamos las respuestas en un array bidimensional [envio_id][pregunta_id]
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
    <link rel="stylesheet" href="../css/exportarExcel.css">
    <link rel="stylesheet" href="../css/resultado.css">
        <link rel="stylesheet" href="../css/logout.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

<div class="results-container">
    <header>
        <div class="back-link">
            <a href="dashboard.php"><span class="material-icons">arrow_back</span> Volver</a>
        </div>
        <h1>Resultados de: <?php echo htmlspecialchars($form['titulo']); ?></h1>
        <p>Total de respuestas: <strong><?php echo count($envios); ?></strong></p>
    </header>



    <div class="table-wrapper">

        <div class="header-actions" style="margin-bottom: 20px;">
                        <a href="../php/exportarExcel.php?id=<?php echo $id_form; ?>" class="btn-excel">
                            <span class="material-icons">download</span> Exportar a Excel
                        </a>
        </div>

        <a href="verReporteTotal.php?id=<?php echo $id_form; ?>" class="btn-reporte-pdf" target="_blank">
        Generar Reporte de Firmas (PDF)
        </a>

        <table>
    <thead>
        <tr>
            <th>Fecha de Envío</th>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Teléfono</th>
            <?php foreach ($preguntas as $p): ?>
                <!-- Solo mostramos el encabezado si la pregunta tiene texto -->
                <th><?php echo htmlspecialchars($p['pregunta_texto']); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php if (count($envios) > 0): ?>
                <?php foreach ($envios as $envio): ?>
                    <tr>
                        <td class="date-cell">
                            <a href="verDetalle.php?envio_id=<?php echo $envio['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo date("d/m/Y H:i", strtotime($envio['fecha_envio'])); ?>
                                <span class="material-icons" style="font-size: 14px;">visibility</span>
                            </a>
                        </td>
                        
                        <td><?php echo htmlspecialchars($envio['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($envio['dni']); ?></td>
                        <td><?php echo htmlspecialchars($envio['tel']); ?></td>
                        
                        <?php foreach ($preguntas as $p): ?>
                            <td>
                                <?php 
                                if (isset($mapa_respuestas[$envio['id']][$p['id']])) {
                                    $valor = $mapa_respuestas[$envio['id']][$p['id']];
                                    if (strpos($valor, 'data:image') === 0) {
                                        echo '<img src="' . $valor . '" class="img-firma-tabla" alt="Firma">';
                                    } else {
                                        echo htmlspecialchars($valor);
                                    }
                                } else {
                                    echo '<span class="empty">-</span>';
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="<?php echo count($preguntas) + 1; ?>" style="text-align: center; padding: 40px;">
                    Aún no hay respuestas para este formulario.
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
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