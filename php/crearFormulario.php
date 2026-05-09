<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario</title>
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


    <div id="questions-container">
        </div>

    <div class="sidebar" id="floating-sidebar">
        <button class="side-btn" onclick="addQuestion()">
            <span class="material-icons">add_circle_outline</span>
        </button>
    </div>

 </div>

 <button class="side-btn save-btn" onclick="capturarFormulario()" title="Guardar Formulario">
  <span class="material-icons" style="color: #0b57d0;">save</span>
 </button>

 <script src="../js/formularios.js"></script>
</body>
</html>