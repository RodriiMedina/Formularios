<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formulario_id = intval($_POST['formulario_id']);
    
    // Capturamos los campos fijos
    $nombre = $_POST['nombre'] ?? '';
    $dni = $_POST['dni'] ?? '';
    $tel = $_POST['telefono'] ?? '';

    // --- CONFIGURACIÓN DE SUBIDAS ---
    $directorio_subida = "../uploads/";
    if (!file_exists($directorio_subida)) {
        mkdir($directorio_subida, 0777, true);
    }

    $conexion->begin_transaction();

    try {
        // 1. Insertar el envío principal
        $stmtEnvio = $conexion->prepare("INSERT INTO envios (formulario_id, nombre, dni, tel) VALUES (?, ?, ?, ?)");
        $stmtEnvio->bind_param("isss", $formulario_id, $nombre, $dni, $tel);
        $stmtEnvio->execute();
        $envio_id = $conexion->insert_id;

        // 2. PROCESAR RESPUESTAS DE TEXTO ($_POST)
        foreach ($_POST as $key => $valor) {
            if (strpos($key, 'p_') === 0) {
                $pregunta_id = intval(substr($key, 2));
                $respuesta_final = is_array($valor) ? implode(", ", $valor) : $valor;
                
                $stmt = $conexion->prepare("INSERT INTO respuestas (envio_id, formulario_id, pregunta_id, respuesta_texto) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $envio_id, $formulario_id, $pregunta_id, $respuesta_final);
                $stmt->execute();
            }
        }

        // 3. PROCESAR RESPUESTAS DE ARCHIVOS ($_FILES) - ¡ESTO ES LO NUEVO!
        foreach ($_FILES as $key => $archivo) {
            if (strpos($key, 'p_') === 0 && $archivo['error'] === UPLOAD_ERR_OK) {
                $pregunta_id = intval(substr($key, 2));
                
                // Generamos un nombre seguro y único
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $nombre_seguro = time() . "_" . uniqid() . "." . $ext;
                $ruta_final = $directorio_subida . $nombre_seguro;

                if (move_uploaded_file($archivo['tmp_name'], $ruta_final)) {
                    // Guardamos el nombre del archivo en la base de datos
                    $stmt = $conexion->prepare("INSERT INTO respuestas (envio_id, formulario_id, pregunta_id, respuesta_texto) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("iiis", $envio_id, $formulario_id, $pregunta_id, $nombre_seguro);
                    $stmt->execute();
                }
            }
        }

        $conexion->commit();
        
        // --- HTML de éxito ---
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>¡Gracias!</title>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <style>
                body { background-color: #f0ebf8; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
                .card { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; max-width: 450px; border-top: 10px solid #673ab7; }
                .icon { font-size: 60px; color: #2e7d32; margin-bottom: 20px; }
                h1 { color: #202124; }
                p { color: #5f6368; }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="material-icons icon">check_circle</span>
                <h1>¡Gracias!</h1>
                <p>Tu respuesta y archivos han sido enviados correctamente.</p>
                <p>Ya podés cerrar esta página.</p>
            </div>
        </body>
        </html>
        <?php

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error al guardar la respuesta: " . $e->getMessage();
    }
}
$conexion->close();
?>