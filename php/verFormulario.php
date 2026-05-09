<?php
require_once '../config/conexion.php'; 


if (!isset($_GET['id'])) {
    die("ID de formulario no especificado.");
}

$id_formulario = intval($_GET['id']);

// 2. Traemos los datos generales del formulario
$query_form = $conexion->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$query_form->bind_param("i", $id_formulario);
$query_form->execute();
$form = $query_form->get_result()->fetch_assoc();

if (!$form) {
    die("Formulario no encontrado.");
}

// 3. Traemos todas las preguntas de ese formulario
$query_preguntas = $conexion->prepare("SELECT * FROM preguntas WHERE formulario_id = ?");
$query_preguntas->bind_param("i", $id_formulario);
$query_preguntas->execute();
$preguntas = $query_preguntas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($form['titulo']); ?></title>
    <script src="../js/formularios.js" defer></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/verFormulario.css"> 
</head>

<body>

   <div class="header-card">
    <div class="form-header">
        <!-- 1. Unificamos a $form (o $form, según tu consulta) -->
        <h1><?php echo htmlspecialchars($form['titulo']); ?></h1>
        
        <?php if (!empty($form['descripcion'])): ?>
            <div class="form-description">
                <!-- 2. nl2br es perfecto aquí para los párrafos -->
                <?php echo nl2br(htmlspecialchars($form['descripcion'])); ?>
            </div>
        <?php endif; ?>
    </div> <!-- Aquí cerramos .form-header -->
        </div> <!-- Aquí cerramos .header-card -->

    

    <form action="guardarRespuesta.php" method="POST">
        <input type="hidden" name="formulario_id" value="<?php echo $id_formulario; ?>">

        <?php while ($q = $preguntas->fetch_assoc()): 
            $opciones = json_decode($q['opciones_json'], true);
            $required = $q['es_obligatoria'] ? 'required' : '';
        ?>
            <div class="question-card">
                <label class="question-text">
                    <?php echo htmlspecialchars($q['pregunta_texto']); ?>
                    <?php if($q['es_obligatoria']) echo '<span class="required-star">*</span>'; ?>
                </label>

                <div class="answer-container">
                    <?php 
                    // 4. Decidimos qué HTML mostrar según el tipo
                    switch ($q['tipo_pregunta']) {
                        case 'text':
                            echo '<input type="text" name="p_'.$q['id'].'" class="input-text" '.$required.' placeholder="Tu respuesta">';
                            break;

                        case 'long_text':
                            echo '<textarea name="p_'.$q['id'].'" class="input-textarea" '.$required.' placeholder="Tu respuesta"></textarea>';
                            break;

                        case 'date':
                            echo '<input type="date" name="p_'.$q['id'].'" class="input-date" '.$required.'>';
                            break;

                        case 'radio':
                            foreach ($opciones as $index => $opt) {
                                echo "<div class='option-row'>
                                        <input type='radio' name='p_{$q['id']}' value='{$opt}' id='p_{$q['id']}_{$index}' {$required}>
                                        <label for='p_{$q['id']}_{$index}'>".htmlspecialchars($opt)."</label>
                                      </div>";
                            }
                            break;

                        case 'checkbox':
                            foreach ($opciones as $index => $opt) {
                                // En checkbox usamos [] en el name para que PHP reciba un array
                                echo "<div class='option-row'>
                                        <input type='checkbox' name='p_{$q['id']}[]' value='{$opt}' id='p_{$q['id']}_{$index}'>
                                        <label for='p_{$q['id']}_{$index}'>".htmlspecialchars($opt)."</label>
                                      </div>";
                            }
                            break;

                        case 'select':
                            echo "<select name='p_{$q['id']}' class='input-select' {$required}>";
                            echo "<option value=''>Elegir</option>";
                            foreach ($opciones as $opt) {
                                echo "<option value='".htmlspecialchars($opt)."'>".htmlspecialchars($opt)."</option>";
                            }
                            echo "</select>";
                            break;

                        case 'rating':
                            echo '<div class="star-rating">';
                            for ($i = 5; $i >= 1; $i--) { // Invertimos para usar el selector CSS ~
                                echo "<input type='radio' name='p_{$q['id']}' value='{$i}' id='star_{$q['id']}_{$i}' {$required}>";
                                echo "<label for='star_{$q['id']}_{$i}' class='material-icons'>star_border</label>";
                            }
                            echo '</div>';
                            break;

                        case 'archivo':
                            echo '<input type="file" name="p_'.$q['id'].'" class="input-file" '.$required.'>';
                            break;

                        case 'signature':
                            echo "
                            <div class='signature-wrapper'>
                                <canvas class='signature-pad' data-id='{$q['id']}' width='400' height='200'></canvas>
                                <input type='hidden' name='p_{$q['id']}' id='signature_input_{$q['id']}' {$required}>
                                <button type='button' class='btn-clear' onclick='clearSignature({$q['id']})'>Limpiar Firma</button>
                            </div>";
                            break;
                    }
                            
                    ?>

                </div>
            </div>
        <?php endwhile; ?>

        <button type="submit" class="submit-btn">Enviar</button>
    
    </form>

</div>

</body>
</html>