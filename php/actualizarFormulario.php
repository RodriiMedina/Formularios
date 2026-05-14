<?php
require_once '../config/conexion.php';


$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $id = intval($data['id']);
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'];

    $conexion->begin_transaction();

    try {

        $stmt = $conexion->prepare("UPDATE formularios SET titulo = ?, descripcion = ? WHERE id = ?");
        $stmt->bind_param("ssi", $titulo, $descripcion, $id);
        $stmt->execute();

        $del = $conexion->prepare("DELETE FROM preguntas WHERE formulario_id = ?");
        $del->bind_param("i", $id);
        $del->execute();


        foreach ($data['preguntas'] as $p) {
            $texto = $p['pregunta'];
            $tipo = $p['tipo'];
            $obligatoria = $p['obligatoria'] ? 1 : 0;
            

            $opciones_json = json_encode($p['opciones'], JSON_UNESCAPED_UNICODE);

            $ins = $conexion->prepare("INSERT INTO preguntas (formulario_id, pregunta_texto, tipo_pregunta, es_obligatoria, opciones_json) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("issis", $id, $texto, $tipo, $obligatoria, $opciones_json);
            $ins->execute();
        }

        $conexion->commit();

        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos válidos']);
}