<?php
require_once '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formulario_id = intval($_POST['formulario_id']);
    $conexion->begin_transaction();

    try {
        $stmtEnvio = $conexion->prepare("INSERT INTO envios (formulario_id) VALUES (?)");
        $stmtEnvio->bind_param("i", $formulario_id);
        $stmtEnvio->execute();
        $envio_id = $conexion->insert_id;

        foreach ($_POST as $key => $valor) {
            if (strpos($key, 'p_') === 0) {
                $pregunta_id = intval(substr($key, 2));
                $respuesta_final = is_array($valor) ? implode(", ", $valor) : $valor;
                $stmt = $conexion->prepare("INSERT INTO respuestas (envio_id, formulario_id, pregunta_id, respuesta_texto) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $envio_id, $formulario_id, $pregunta_id, $respuesta_final);
                $stmt->execute();
            }
        }

        $conexion->commit();
        
        // --- AQUÍ EMPIEZA EL HTML LINDO ---
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>¡Gracias!</title>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <style>
                body {
                    background-color: #f0ebf8;
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    margin: 0;
                }
                .card {
                    background: white;
                    padding: 40px;
                    border-radius: 10px;
                    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                    text-align: center;
                    max-width: 450px;
                    border-top: 10px solid #673ab7; /* El violeta pro */
                }
                .icon { font-size: 60px; color: #2e7d32; margin-bottom: 20px; }
                h1 { color: #202124; margin-bottom: 10px; }
                p { color: #5f6368; margin-bottom: 25px; }
                .btn {
                    text-decoration: none;
                    color: #673ab7;
                    border: 1px solid #dadce0;
                    padding: 10px 20px;
                    border-radius: 5px;
                    font-weight: 500;
                    transition: 0.3s;
                }
                .btn:hover { background: #f8f9fa; border-color: #673ab7; }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="material-icons icon">check_circle</span>
                <h1>¡Gracias!</h1>
                <p>Tu respuesta ha sido enviada correctamente.</p>
            </div>
        </body>
        </html>
        <?php
        // --- AQUÍ TERMINA EL HTML LINDO ---

    } catch (Exception $e) {
        $conexion->rollback();
        echo "Error al guardar la respuesta: " . $e->getMessage();
    }
}
$conexion->close();
?>