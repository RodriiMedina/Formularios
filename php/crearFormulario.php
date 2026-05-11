<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$nombre_usuario = $_SESSION['usuario'];
$rol_usuario = $_SESSION['rol']; // Para usarlo después si queremos ocultar botones
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/crearFormulario.css">
        <link rel="stylesheet" href="../css/capturarFormulario.css">

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

</head>



<body>
    <div class="back-link">
        <a href="dashboard.php"><span class="material-icons">arrow_back</span> Volver</a>
    </div>

 <div class="container">

    <div class="header-card form-card active">
        <input type="text" class="title-input" placeholder="Formulario sin título">

        <div class="input-group">
                <textarea 
                    name="descripcion" 
                    id="descripcion" 
                    placeholder="Descripción del formulario" 
                    rows="1"
                    oninput="autoExpand(this)"
                ></textarea>
            </div>
    </div>


<div class="question-card">
    <label class="question-text">Nombre completo <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="text" name="nombre" class="input-text" required 
               pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" 
               title="Por favor, ingresá solo letras." 
               placeholder="Tu nombre y apellido">
    </div>
</div>

<div class="question-card">
    <label class="question-text">DNI <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="text" name="dni" class="input-text" required 
               pattern="^[0-9]{7,8}$" 
               title="El DNI debe tener 7 u 8 números, sin puntos." 
               placeholder="Ej: 35123456">
    </div>
</div>

<div class="question-card">
    <label class="question-text">Teléfono celular <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="tel" name="telefono" class="input-text" required 
               pattern="^[0-9]{10}$" 
               title="Ingresá 10 números (Cód. área sin 0 + número sin 15). Ej: 1123456789" 
               placeholder="Ej: 1123456789">
    </div>
</div>

    <div id="questions-container">
        </div>

    <div class="sidebar" id="floating-sidebar">
        <button class="side-btn" onclick="addQuestion()">
            <span class="material-icons">add_circle_outline</span>
        </button>

        <button class="side-btn" onclick="addSection()">
             <span class="material-icons">view_stream</span>
        </button>
    </div>

 </div>

 <button class="side-btn save-btn" onclick="capturarFormulario()" title="Guardar Formulario">
  <span class="material-icons" style="color: #0b57d0;">save</span>
 </button>





 <script src="../js/formularios.js"></script>
</body>
</html>