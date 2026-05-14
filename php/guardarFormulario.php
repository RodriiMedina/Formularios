<?php
session_start();
require_once '../config/conexion.php'; 

header('Content-Type: application/json');
    

// 1. Verificamos que el usuario tenga sesión activa
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión expirada. Por favor, volvé a entrar.']);
    exit();
}

// 2. Leemos el JSON que manda el formularios.js
$json = file_get_contents('php://input');
$datos = json_decode($json, true);

if ($datos) {
    // Usamos transacciones: se guarda todo o no se guarda nada
    $conexion->begin_transaction();

    try {
        $titulo = $datos['titulo'];
        $descripcion = $datos['descripcion'];
        $usuario_id = $_SESSION['usuario_id']; // Recuperamos el ID de la sesión

        // 3. Insertar el encabezado incluyendo el usuario_id
        $stmt = $conexion->prepare("INSERT INTO formularios (titulo, descripcion, usuario_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $titulo, $descripcion, $usuario_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al crear el encabezado: " . $stmt->error);
        }
        
        $formularioId = $conexion->insert_id;

        // 4. Insertar las preguntas (si es que vienen en el JSON)
        if (isset($datos['preguntas']) && is_array($datos['preguntas'])) {
            $stmtPregunta = $conexion->prepare("INSERT INTO preguntas (formulario_id, pregunta_texto, tipo_pregunta, es_obligatoria, opciones_json) VALUES (?, ?, ?, ?, ?)");

            foreach ($datos['preguntas'] as $pregunta) {
                $texto = $pregunta['pregunta'];
                $tipo = $pregunta['tipo'];
                $obligatoria = !empty($pregunta['obligatoria']) ? 1 : 0;
                $opciones = json_encode($pregunta['opciones']);

                $stmtPregunta->bind_param("issis", $formularioId, $texto, $tipo, $obligatoria, $opciones);
                $stmtPregunta->execute();
            }
        }

        $conexion->commit();
        echo json_encode(['status' => 'success', 'message' => '¡Formulario guardado con éxito!']);

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Fallo en la base de datos: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se recibieron datos válidos o el JSON está mal formado.']);
}

$conexion->close();
?>