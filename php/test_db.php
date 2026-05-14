<?php
include("../config/conexion.php");

echo "<style>
    body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 800px; margin: 20px auto; padding: 20px; }
    .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
    .ok { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    code { background: #eee; padding: 2px 4px; border-radius: 3px; }
</style>";

echo "<h1>🩺 Diagnóstico del Sistema</h1>";

// 1. Verificación de Conexión
if ($conexion->connect_error) {
    echo "<div class='status error'><b>❌ Error de Conexión:</b> " . $conexion->connect_error . "</div>";
    die();
} else {
    echo "<div class='status ok'><b>✅ Conexión establecida:</b> Conectado a la base de datos.</div>";
}

// 2. Verificación de Sesión
session_start();
echo "<h3>1. Estado de Sesión</h3>";
if (isset($_SESSION['usuario_id'])) {
    echo "✅ Sesión activa. ID de usuario: <code>" . $_SESSION['usuario_id'] . "</code><br>";
    echo "👤 Nombre de usuario en sesión: <code>" . $_SESSION['usuario'] . "</code><br>";
} else {
    echo "⚠️ <b>Sesión no detectada:</b> Si ya te logueaste, esto es un problema. Revisá el <code>session_start()</code>.<br>";
}

// 3. Verificación de Tablas y Columnas (Función Única y Corregida)
function chequear_tabla(mysqli $conn, string $tabla, array $columnas_necesarias) {
    echo "<h3>2. Verificando tabla: <code>$tabla</code></h3>";
    $res = mysqli_query($conn, "SHOW TABLES LIKE '$tabla'");
    
    if (mysqli_num_rows($res) == 0) {
        echo "<div class='status error'>❌ La tabla <b>$tabla</b> NO existe en la base de datos.</div>";
        return;
    }

    echo "✅ Tabla encontrada.<br>";
    $columnas_reales = [];
    $info = mysqli_query($conn, "DESCRIBE $tabla");
    while($col = mysqli_fetch_assoc($info)) {
        $columnas_reales[] = $col['Field'];
    }

    foreach ($columnas_necesarias as $col) {
        if (in_array($col, $columnas_reales)) {
            echo "✔️ Columna <code>$col</code>: <b>OK</b><br>";
        } else {
            echo "<div class='status error'>❌ FALTA COLUMNA: <code>$col</code>. Tu código fallará si intenta usarla.</div>";
        }
    }
}

// Ejecución de chequeos (Fuera de la función)
chequear_tabla($conexion, 'usuarios', ['id', 'usuario', 'password', 'rol']);
chequear_tabla($conexion, 'formularios', ['id', 'titulo', 'usuario_id']);

// 4. Verificación de integridad de datos
echo "<h3>3. Integridad de Datos</h3>";
$res_user = mysqli_query($conexion, "SELECT * FROM usuarios LIMIT 1");
if ($user = mysqli_fetch_assoc($res_user)) {
    $pass_len = strlen($user['password']);
    if ($pass_len < 60) {
        echo "<div class='status warning'>⚠️ <b>Password sospechoso:</b> Tiene $pass_len caracteres. 
              Si usás <code>password_hash</code> debería tener 60.</div>";
    } else {
        echo "✅ El hash de contraseña parece tener el largo correcto.<br>";
    }
} else {
    echo "⚠️ La tabla de usuarios está vacía. No vas a poder loguearte.";
}
?>