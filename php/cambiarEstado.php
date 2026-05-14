<?php
require_once '../config/conexion.php';
session_start();

$id = intval($_GET['id']);
$nuevo_estado = intval($_GET['estado']);

// Verificamos que el formulario pertenezca al usuario (o sea admin)
$stmt = $conexion->prepare("UPDATE formularios SET estado = ? WHERE id = ?");
$stmt->bind_param("ii", $nuevo_estado, $id);

if ($stmt->execute()) {
    header("Location: dashboard.php?msj=estado_actualizado");
}
?>