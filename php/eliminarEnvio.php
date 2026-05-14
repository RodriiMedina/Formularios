<?php
require_once '../config/conexion.php';

$envio_id = intval($_GET['id'] ?? 0);
$form_id = intval($_GET['form_id'] ?? 0);

if ($envio_id > 0) {
    $conexion->begin_transaction();

    try {

        $stmtRes = $conexion->prepare("DELETE FROM respuestas WHERE envio_id = ?");
        $stmtRes->bind_param("i", $envio_id);
        $stmtRes->execute();


        $stmtEnv = $conexion->prepare("DELETE FROM envios WHERE id = ?");
        $stmtEnv->bind_param("i", $envio_id);
        $stmtEnv->execute();

        $conexion->commit();
        

        header("Location: resultado.php?id=" . $form_id . "&msg=eliminado");
        exit;
    } catch (Exception $e) {
        $conexion->rollback();
        die("Error al eliminar: " . $e->getMessage());
    }
} else {
    die("ID de envío no válido.");
}
?>