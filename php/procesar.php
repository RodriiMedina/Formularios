<?php
session_start();
include("../config/conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['usuario'];
    $pass = $_POST['password'];

    // 1. CAMBIO: Agregamos 'id' a la consulta SQL
    $sql = "SELECT id, usuario, password, rol FROM usuarios WHERE usuario = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario_db = $resultado->fetch_assoc();

        if (password_verify($pass, $usuario_db['password'])) {
            
            // 2. CAMBIO: Ahora sí guardamos el ID en la sesión
            $_SESSION['usuario_id'] = $usuario_db['id']; // Asegurate que en tu DB se llame 'id'
            $_SESSION['usuario'] = $usuario_db['usuario'];
            $_SESSION['rol'] = $usuario_db['rol']; 
            
            header("Location: main.php");
            exit();
            
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
?>