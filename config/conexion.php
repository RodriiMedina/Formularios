<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = "localhost";
$bd = "formularios_db";
$usuario = "root";
$password = "";


$conexion = new mysqli($host, $usuario, $password, $bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

?>