<?php

require_once '../config/conexion.php'; 

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $stmt = $conexion->prepare("DELETE FROM formularios WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?msg=eliminado");
        exit(); 
    } else {
        echo "Error al eliminar: " . $conexion->error;
    }
}
?>