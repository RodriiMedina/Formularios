<?php
// Solo para probar: esto te va a dar el código exacto para tu base de datos
$password_plano = "meny";
echo "Copiá este hash e insertalo en la DB:<br>";
echo password_hash($password_plano, PASSWORD_DEFAULT);
?>
