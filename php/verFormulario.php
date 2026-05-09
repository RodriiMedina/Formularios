<?php
require_once '../config/conexion.php'; 

if (!isset($_GET['id'])) {
    die("ID de formulario no especificado.");
}

$id_formulario = intval($_GET['id']);

// Traemos los datos generales del formulario
$query_form = $conexion->prepare("SELECT titulo, descripcion FROM formularios WHERE id = ?");
$query_form->bind_param("i", $id_formulario);
$query_form->execute();
$form = $query_form->get_result()->fetch_assoc();

if (!$form) {
    die("Formulario no encontrado.");
}

// Traemos todas las preguntas
$query_preguntas = $conexion->prepare("SELECT * FROM preguntas WHERE formulario_id = ?");
$query_preguntas->bind_param("i", $id_formulario);
$query_preguntas->execute();
$preguntas = $query_preguntas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['titulo']); ?></title>
    <script src="../js/formularios.js" defer></script>

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../css/verFormulario.css"> 

</head>

<body>

    <div class="header-card">
        <div class="form-header">
            <h1><?php echo htmlspecialchars($form['titulo']); ?></h1>
            <?php if (!empty($form['descripcion'])): ?>
                <div class="form-description">
                    <?php echo nl2br(htmlspecialchars($form['descripcion'])); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <form action="guardarRespuesta.php" method="POST">
        <input type="hidden" name="formulario_id" value="<?php echo $id_formulario; ?>">

        <div class="question-card">
    <label class="question-text">Nombre completo <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="text" name="nombre" class="input-text" required 
               pattern="^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$" 
               title="Por favor, ingresá solo letras." 
               placeholder="Tu respuesta">
    </div>
</div>

<div class="question-card">
    <label class="question-text">DNI <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="text" name="dni" class="input-text" required 
               pattern="^[0-9]{7,10}$" 
               title="El DNI debe tener 7 u 8 números, sin puntos." 
               placeholder="Tu respuesta">
    </div>
</div>

<div class="question-card">
    <label class="question-text">Telefono celular <span class="required-star">*</span></label>
    <div class="answer-container">
        <input type="tel" name="telefono" class="input-text" required 
               pattern="^[0-9]{10}$" 
               title="Ingresá 10 números (Cód. área sin 0 + número sin 15). Ej: 1123456789" 
               placeholder="Tu respuesta">
    </div>
</div>

        <?php while ($q = $preguntas->fetch_assoc()): 
            $opciones = json_decode($q['opciones_json'], true);
            $required = $q['es_obligatoria'] ? 'required' : '';
            
            if ($q['tipo_pregunta'] === 'seccion'): ?>
                <div class="corte-seccion"> 
                    <h2 class="titulo-nueva-pagina"><?php echo htmlspecialchars($q['pregunta_texto']); ?></h2>
                </div>
            <?php else: ?>
                <div class="question-card">
                    <label class="question-text">
                        <?php echo htmlspecialchars($q['pregunta_texto']); ?>
                        <?php if($q['es_obligatoria']) echo '<span class="required-star">*</span>'; ?>
                    </label>

                    <div class="answer-container">
                        <?php 
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
                                for ($i = 5; $i >= 1; $i--) {
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
            <?php endif; ?>
        <?php endwhile; ?>

        <button type="submit" class="submit-btn">Enviar</button>
    </form>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    // Incluimos el header-card y corregimos la clase de sección
    const cards = Array.from(document.querySelectorAll('.header-card, .question-card, .corte-seccion, .submit-btn'));
    const originalSubmit = document.querySelector('.submit-btn:not(#btnNext)');
    
    if (!form || cards.length === 0) return;

    let currentPage = 0;
    let pages = [[]];
    
    // 1. Agrupamos las tarjetas en "páginas"
    cards.forEach(card => {
        // Si es una sección, abrimos página nueva ANTES de meterla
        if (card.classList.contains('corte-seccion')) {
            if (pages[pages.length - 1].length > 0) {
                pages.push([]);
            }
        }
        pages[pages.length - 1].push(card);
    });

    // 2. Contenedor de navegación
    const navWrapper = document.createElement('div');
    navWrapper.id = "nav-wrapper";
    navWrapper.style = "text-align: center; margin-top: 30px; display: flex; flex-direction: column; align-items: center; gap: 15px;";
    
    const badge = document.createElement('div');
    badge.id = 'section-counter';
    badge.style = "color: #5f6368; font-size: 14px; font-weight: 500;";
    navWrapper.appendChild(badge);

    const btnContainer = document.createElement('div');
    btnContainer.className = "nav-btns";
    btnContainer.innerHTML = `
        <button type="button" id="btnPrev" class="btn-return" style="display:none">Atrás</button>
        <button type="button" id="btnNext" class="submit-btn">Siguiente</button>
    `;
    
    navWrapper.appendChild(btnContainer);
    form.appendChild(navWrapper);

    if (originalSubmit) {
        btnContainer.appendChild(originalSubmit);
        originalSubmit.style.margin = "0";
    }

    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');

    function updateView() {
        // Ocultamos todo y mostramos la página actual
        cards.forEach(c => c.classList.remove('visible'));
        pages[currentPage].forEach(c => c.classList.add('visible'));

        badge.innerText = `Página ${currentPage + 1} de ${pages.length}`;

        // Lógica de botones
        btnPrev.style.display = (currentPage === 0) ? 'none' : 'inline-block';
        
        const isLastPage = (currentPage === pages.length - 1);
        if (isLastPage) {
            btnNext.style.display = 'none';
            if (originalSubmit) originalSubmit.classList.add('visible');
        } else {
            btnNext.style.display = 'inline-block';
            if (originalSubmit) originalSubmit.classList.remove('visible');
        }
    }

    btnNext.onclick = () => { currentPage++; updateView(); window.scrollTo(0,0); };
    btnPrev.onclick = () => { currentPage--; updateView(); window.scrollTo(0,0); };

    updateView();
});
</script>

</body>
</html>