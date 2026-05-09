<?php
require_once '../config/conexion.php';

$id_form = intval($_GET['id'] ?? 0);
if (!$id_form) die("ID no válido");

// 1. Obtener preguntas dinámicas
$q_preguntas = $conexion->prepare("SELECT id, pregunta_texto FROM preguntas WHERE formulario_id = ? ORDER BY id ASC");
$q_preguntas->bind_param("i", $id_form);
$q_preguntas->execute();
$res_preguntas = $q_preguntas->get_result();

// AGREGADO: Encabezados fijos del sistema
$headers = ['Fecha de Envío', 'Nombre', 'DNI', 'Teléfono'];
$preguntas_ids = [];
while ($p = $res_preguntas->fetch_assoc()) {
    $headers[] = $p['pregunta_texto'];
    $preguntas_ids[] = $p['id'];
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_compromiso_urbano.csv');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para acentos
fputcsv($output, $headers, ";");

// 2. ACTUALIZADO: Traemos los datos fijos desde la tabla envios
$q_envios = $conexion->prepare("SELECT id, fecha_envio, nombre, dni, tel FROM envios WHERE formulario_id = ? ORDER BY fecha_envio DESC");
$q_envios->bind_param("i", $id_form);
$q_envios->execute();
$res_envios = $q_envios->get_result();

$baseUrl = "http://localhost/formularios/php/verDetalle.php?envio_id=";

while ($envio = $res_envios->fetch_assoc()) {
    // AGREGADO: Llenamos la fila con los datos fijos
    $fila = [
        $envio['fecha_envio'],
        $envio['nombre'],
        $envio['dni'],
        $envio['tel']
    ];
    
    foreach ($preguntas_ids as $p_id) {
        $q_res = $conexion->prepare("SELECT respuesta_texto FROM respuestas WHERE envio_id = ? AND pregunta_id = ?");
        $q_res->bind_param("ii", $envio['id'], $p_id);
        $q_res->execute();
        $res = $q_res->get_result()->fetch_assoc();
        
        $valor = $res['respuesta_texto'] ?? '-';
        if (strpos($valor, 'data:image') === 0) {
            $valor = $baseUrl . $envio['id'];
        }
        $fila[] = $valor;
    }
    fputcsv($output, $fila, ";");
}
fclose($output);
exit;