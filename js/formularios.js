function addQuestion(data = null) {
    const container = document.getElementById('questions-container');
    const card = document.createElement('div');
    card.className = 'form-card';
    card.onclick = function() { setActive(this); };

    const preguntaTexto = data ? data.pregunta_texto : '';
    const tipo = data ? data.tipo_pregunta : 'radio';
    const esObligatoria = (data && data.es_obligatoria == 1) ? 'checked' : '';

card.innerHTML = `
    <div class="q-row">
        <div class="q-inputs-group">
            <input type="text" class="q-text" placeholder="Pregunta sin título" value="${preguntaTexto}">
        </div>
        <select class="q-type" onchange="renderOptions(this)">
            <option value="text" ${tipo === 'text' ? 'selected' : ''}>Respuesta corta</option>
            <option value="long_text" ${tipo === 'long_text' ? 'selected' : ''}>Respuesta larga</option>
            <option value="radio" ${tipo === 'radio' ? 'selected' : ''}>Opción múltiple</option>
            <option value="checkbox" ${tipo === 'checkbox' ? 'selected' : ''}>Casillas</option>
            <option value="archivo" ${tipo === 'archivo' ? 'selected' : ''}>Archivo</option>
            <option value="rating" ${tipo === 'rating' ? 'selected' : ''}>Calificación 1-5</option>
            <option value="date" ${tipo === 'date' ? 'selected' : ''}>Fecha</option>
            <option value="signature" ${tipo === 'signature' ? 'selected' : ''}>Firma Digital</option>
        </select>
    </div>
    
    <div class="options-area"></div>
    
    <div class="action-zone" style="display:${['radio', 'checkbox'].includes(tipo) ? 'block' : 'none'}">
        <span class="material-icons" style="color:#673ab7; font-size:18px; vertical-align:middle;">add</span>
        <span class="add-opt-btn" onclick="addOption(this)" style="cursor:pointer; color:#777; font-size:14px; vertical-align:middle;">
            Añadir opción
        </span>
    </div>

    <div class="card-footer">
        <div class="footer-actions">
            <span class="material-icons btn-move" onclick="moverPregunta(this, 'up')">arrow_upward</span>
            <span class="material-icons btn-move" onclick="moverPregunta(this, 'down')">arrow_downward</span>

            <div class="vertical-divider"></div>

            <div class="required-group">
                <span>Obligatoria</span>
                <label class="switch">
                    <input type="checkbox" class="required-check" ${esObligatoria ? 'checked' : ''}>
                    <span class="slider"></span>
                </label>
                
                <div class="vertical-divider"></div>
                <span class="material-icons btn-delete" onclick="eliminarTarjeta(this)">delete_outline</span>
            </div>
        </div>
    </div>
`;

    container.appendChild(card);

    if (data && data.opciones && Array.isArray(data.opciones) && data.opciones.length > 0) {
            data.opciones.forEach(texto => addOption(card.querySelector('.add-opt-btn'), texto));
        } else {
            renderOptions(card.querySelector('.q-type'));
        }
        setTimeout(() => {
            setActive(card);
        }, 10); 
    }

function triggerImgInput(btn) {
    btn.nextElementSibling.click();
}

function previewImage(input) {
    const card = input.closest('.form-card');
    const container = card.querySelector('.image-preview-container');
    const img = container.querySelector('.img-preview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            container.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(btn) {
    const container = btn.parentElement;
    container.style.display = 'none';
    container.querySelector('.img-preview').src = '';
    container.previousElementSibling.querySelector('.hidden-img-input').value = '';
}


// 2. Función para cambiar qué se ve según lo que elijas en el select
function renderOptions(select) {
    const card = select.closest('.form-card');
    const area = card.querySelector('.options-area');
    const actionZone = card.querySelector('.action-zone');
    const type = select.value;

    // Limpiamos el área antes de dibujar lo nuevo
    area.innerHTML = '';

    if (type === 'text') {
        area.innerHTML = '<input type="text" class="preview-text-input" placeholder="Texto de respuesta corta" disabled>';
        actionZone.style.display = 'none';
    } 
    else if (type === 'long_text') {
        area.innerHTML = '<textarea class="preview-text-input" placeholder="Texto de respuesta larga" disabled></textarea>';
        actionZone.style.display = 'none';
    } 
    else if (type === 'date') {
        // Mostramos un input de tipo fecha desactivado como vista previa
        area.innerHTML = '<input type="date" class="preview-text-input" style="width:auto" disabled>';
        actionZone.style.display = 'none';
    } 
    else if (type === 'archivo') {
        // Vista previa para subida de archivos
        area.innerHTML = '<div class="preview-file-upload"><span class="material-icons">cloud_upload</span> Subir archivo</div>';
        actionZone.style.display = 'none';
    } 
    else if (type === 'rating') {
        // Dibujamos las 5 estrellas para la calificación
        area.innerHTML = `
            <div class="star-rating-preview">
                <span class="material-icons" style="color:#ccc">star_border</span>
                <span class="material-icons" style="color:#ccc">star_border</span>
                <span class="material-icons" style="color:#ccc">star_border</span>
                <span class="material-icons" style="color:#ccc">star_border</span>
                <span class="material-icons" style="color:#ccc">star_border</span>
            </div>`;
        actionZone.style.display = 'none';
    } 
    else if (type === 'signature') {
    area.innerHTML = `
        <div class="signature-preview">
            <span class="material-icons">edit</span>
            <p>Espacio para firma digital</p>
        </div>`;
    actionZone.style.display = 'none';
    }
    else {
        // Para Radio (Opción múltiple) y Checkbox (Casillas)[cite: 3]
        actionZone.style.display = 'block';
        // Si el área está vacía (pregunta nueva), ponemos una opción por defecto
        if (area.children.length === 0) {
            addOption(card.querySelector('.add-opt-btn'), "Opción 1");
        }
    }
}


function addOption(btn) {
    const card = btn.closest('.form-card');
    const area = card.querySelector('.options-area');
    const tipo = card.querySelector('.q-type').value;
    const numero = area.querySelectorAll('.option-row').length + 1;

    let icono = (tipo === 'checkbox') ? 'check_box_outline_blank' : 'radio_button_unchecked';

    const div = document.createElement('div');
    div.className = 'option-row';
    div.innerHTML = `
        <span class="material-icons" style="color:#ccc">${icono}</span>
        <input type="text" class="opt-input" placeholder="Opción ${numero}" value="">
        <span class="material-icons" onclick="this.parentElement.remove()" style="cursor:pointer; color:#777; font-size:18px;">close</span>
    `;
    area.appendChild(div);
}


function setActive(card) {
    if (!card) return; // Seguridad extra
    document.querySelectorAll('.form-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');
}


const headerCard = document.querySelector('.header-card');

// Solo si existe, le asignamos el evento
if (headerCard) {
    headerCard.onclick = function() {
        setActive(this);
    };
}

function copiarLink(url, boton) {
    // Copiamos el texto al portapapeles
    navigator.clipboard.writeText(url).then(() => {
        // Feedback visual: cambiamos el texto del botón temporalmente
        const originalText = boton.innerHTML;
        boton.style.backgroundColor = "#4caf50"; // Verde éxito
        boton.style.color = "white";
        // Después de 2 segundos, vuelve a la normalidad
        setTimeout(() => {
            boton.style.backgroundColor = "";
            boton.style.color = "";
            boton.innerHTML = originalText;
        }, 2000);
    }).catch(err => {
        console.error('Error al copiar: ', err);
    });
}

function capturarFormulario() {
    const inputTitulo = document.querySelector('.title-input');
    const inputDesc = document.querySelector('.header-card textarea[placeholder="Descripción del formulario"]');

    // Reset de estilos
    inputTitulo.style.borderBottom = "1px solid #ddd";
    inputDesc.style.borderBottom = "1px solid #ddd";

    const formulario = {
        titulo: inputTitulo.value.trim(),
        descripcion: inputDesc.value.trim(),
        preguntas: []
    };

    // Validación de obligatorios
    if (formulario.titulo === "" || formulario.descripcion === "") {
        alert("El título y la descripción son obligatorios para poder crear el formulario.");
        if (formulario.titulo === "") inputTitulo.style.borderBottom = "2px solid #d93025";
        if (formulario.descripcion === "") inputDesc.style.borderBottom = "2px solid #d93025";
        return;
    }

    // Captura de preguntas
    const tarjetas = document.querySelectorAll('.form-card:not(.header-card)');
    tarjetas.forEach((tarjeta) => {
        const preguntaText = tarjeta.querySelector('.q-text').value;
        const tipoPregunta = tarjeta.querySelector('.q-type').value;
        const checkElement = tarjeta.querySelector('.required-check');
        const esObligatoria = checkElement ? checkElement.checked : false;

        const infoPregunta = {
            pregunta: preguntaText,
            tipo: tipoPregunta,
            obligatoria: esObligatoria,
            opciones: []
        };

        if (['radio', 'checkbox', 'select'].includes(tipoPregunta)) {
            const inputsOpciones = tarjeta.querySelectorAll('.opt-input');
            inputsOpciones.forEach(input => {
                if (input.value.trim() !== "") {
                    infoPregunta.opciones.push(input.value);
                }
            });
        }
        formulario.preguntas.push(infoPregunta);
    });

    fetch('../php/guardarFormulario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formulario)
    })
.then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // 1. Creamos el elemento del Toast
            const toast = document.createElement('div');
            toast.className = 'toast-success';
            toast.innerHTML = `
                <span class="material-icons toast-icon">check_circle</span>
                <span>¡El formulario se guardó correctamente!</span>
            `;

            // 2. Lo agregamos al cuerpo de la página
            document.body.appendChild(toast);

            // 3. Pequeño delay para que el navegador registre el elemento y dispare la animación
            setTimeout(() => toast.classList.add('show'), 100);

            // 4. Cooldown de 2 segundos antes de ir al dashboard
            setTimeout(() => {
                window.location.href = 'dashboard.php';
            }, 2000);

        } else {
            alert("Error al guardar: " + data.message);
        }
    });
}

function copiarLink(url, boton) {
    navigator.clipboard.writeText(url).then(() => {
        const originalHTML = boton.innerHTML;
        boton.style.backgroundColor = "#4caf50";
        boton.style.color = "white";
        boton.innerHTML = '<span class="material-icons">check</span>';
        
        setTimeout(() => {
            boton.style.backgroundColor = "";
            boton.style.color = "";
            boton.innerHTML = originalHTML;
        }, 2000);
    }).catch(err => {
        alert("Error al copiar el enlace");
    });
}

function confirmarBorrado(id) {
    if(confirm('¿Estás seguro de que querés borrar este formulario? Se perderán todas las respuestas guardadas.')) {
        window.location.href = 'eliminarFormulario.php?id=' + id;

    }
}

function actualizarFormulario() {
    console.log("Iniciando guardado..."); // Esto es para ver en la consola si entra aquí

    // 1. Obtener el ID de la URL de forma segura
    const urlParams = new URLSearchParams(window.location.search);
    const formId = urlParams.get('id');

    if (!formId) {
        alert("Error: No se encontró el ID del formulario en la URL.");
        return;
    }

    // 2. Armar el objeto con los datos
    const formulario = {
        id: formId,
        titulo: document.querySelector('.title-input').value,
        descripcion: document.querySelector('.header-description').value,
        preguntas: []
    };

    // 3. Capturar las preguntas
    const tarjetas = document.querySelectorAll('.form-card:not(.header-card)');
    tarjetas.forEach((tarjeta) => {
        const tipoPregunta = tarjeta.querySelector('.q-type').value;
        const infoPregunta = {
            pregunta: tarjeta.querySelector('.q-text').value,
            tipo: tipoPregunta,
            obligatoria: tarjeta.querySelector('.required-check')?.checked ? 1 : 0,
            opciones: []
        };

        // Si es de selección, guardamos los textos de las opciones[cite: 1, 3]
        if (['radio', 'checkbox'].includes(tipoPregunta)) {
            tarjeta.querySelectorAll('.opt-input').forEach(input => {
                if (input.value.trim() !== "") {
                    infoPregunta.opciones.push(input.value);
                }
            });
        }
        formulario.preguntas.push(infoPregunta);
    });

    console.log("Datos a enviar:", formulario);

    // 4. Enviar al PHP[cite: 3]
    fetch('../php/actualizarFormulario.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formulario)
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            alert("¡Formulario actualizado con éxito!");
            window.location.href = 'dashboard.php';
        } else {
            alert("Error del servidor: " + data.message);
        }
    })
    .catch(err => {
        console.error("Error en el fetch:", err);
        alert("Hubo un error al conectar con el servidor.");
    });
}


function initSignatures() {
    const pads = document.querySelectorAll('.signature-pad');
    pads.forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const questionId = canvas.getAttribute('data-id');
        const hiddenInput = document.getElementById('signature_input_' + questionId);
        let drawing = false;

        // Configuración del trazo para que sea suave
        ctx.strokeStyle = "#000000";
        ctx.lineWidth = 2;
        ctx.lineJoin = "round";
        ctx.lineCap = "round";

        
    const ajustarResolucion = () => {
    const containerWidth = canvas.parentElement.offsetWidth;
    
        // Si la pantalla es chica, lo ajustamos al ancho del celu
        if (containerWidth < 600 && containerWidth > 0) {
            canvas.width = containerWidth - 40; 
            canvas.height = canvas.width / 2; // Mantenemos proporción
        } else {
            // En PC volvemos al tamaño grande
            canvas.width = 600;
            canvas.height = 300;
        }
    };

ajustarResolucion();
window.addEventListener('resize', ajustarResolucion);
        const getPos = (e) => {
    const rect = canvas.getBoundingClientRect();
    const clientX = e.touches ? e.touches[0].clientX : e.clientX;
    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    return {
        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY
    };
};

        const startDrawing = (e) => {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            // Evitamos que el mouse seleccione texto mientras firmás
            if (!e.touches) e.preventDefault(); 
            console.log("Empezando a dibujar en pregunta: " + questionId); 
        };

        const draw = (e) => {
            if (!drawing) return;
            e.preventDefault(); 
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        };

        const stopDrawing = () => {
            if (!drawing) return;
            drawing = false;
            console.log("Firma guardada en input invisible."); //[cite: 3]
            // Guardamos la imagen en el input oculto[cite: 1]
            hiddenInput.value = canvas.toDataURL(); 
        };

        // Eventos de Mouse (PC)
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDrawing);
        // Si el mouse sale del cuadro, dejamos de dibujar
        canvas.addEventListener('mouseleave', stopDrawing);

        // Eventos de Touch (Celular)
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);
    });
}

// Función para el botón "Limpiar"[cite: 3]
function clearSignature(id) {
    const canvas = document.querySelector(`.signature-pad[data-id="${id}"]`);
    const input = document.getElementById('signature_input_' + id);
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        input.value = ""; // Vaciamos el input
    }
}

function autoExpand(field) {
    // Reseteamos la altura para calcular el nuevo scrollHeight
    field.style.height = 'inherit';

    // Calculamos la nueva altura basada en el contenido
    const computed = window.getComputedStyle(field);
    const height = parseInt(computed.getPropertyValue('border-top-width'), 10)
                 + field.scrollHeight
                 + parseInt(computed.getPropertyValue('border-bottom-width'), 10);

    field.style.height = height + 'px';
}

function moverSidebarALaUltima(tarjeta) {
    const sidebar = document.getElementById('floating-sidebar');
    if (sidebar && tarjeta) {
        // Calculamos la distancia desde el tope de la tarjeta hasta el tope del contenedor
        const offsetTop = tarjeta.offsetTop;
        
        // Movemos el sidebar a esa misma altura
        sidebar.style.top = offsetTop + "px";
    }
}

function setActive(card) {
    // 1. Marcamos la tarjeta como activa visualmente
    document.querySelectorAll('.form-card').forEach(c => c.classList.remove('active'));
    card.classList.add('active');

    // 2. MOVE EL SIDEBAR
    const sidebar = document.getElementById('floating-sidebar');
    if (sidebar) {
        // El offsetTop es la distancia de la tarjeta al tope del contenedor
        sidebar.style.top = card.offsetTop + "px";
    }
}

function moverPregunta(btn, direccion) {
    const tarjeta = btn.closest('.form-card');
    const contenedor = document.getElementById('questions-container');

    if (direccion === 'up') {
        const anterior = tarjeta.previousElementSibling;
        if (anterior) {
            contenedor.insertBefore(tarjeta, anterior);
        }
    } else {
        const siguiente = tarjeta.nextElementSibling;
        if (siguiente) {
            // insertAfter no existe nativamente, pero esto hace lo mismo:
            contenedor.insertBefore(siguiente, tarjeta);
        }
    }

    actualizarNumerosDeSeccion();
    setActive(tarjeta);
}

function addSection(data = null) {
    const container = document.getElementById('questions-container');
    const card = document.createElement('div');
    
    card.className = 'form-card section-card';
    card.onclick = function() { setActive(this); };

    const titulo = data ? data.pregunta_texto : 'Sección sin título';

    card.innerHTML = `
        <div class="section-badge">SECCIÓN</div>
        <div class="q-row">
            <div class="q-inputs-group" style="width: 100%;">
                <input type="text" class="q-text section-title-input" 
                       placeholder="Título de la sección" value="${titulo}" 
                       style="font-size: 20px; font-weight: 500; border-bottom: 2px solid #673ab7;">
            </div>
            <input type="hidden" class="q-type" value="seccion">
        </div>
        
        <div class="card-footer">
            <div class="footer-actions">
                <span class="material-icons btn-move" onclick="moverPregunta(this, 'up')">arrow_upward</span>
                <span class="material-icons btn-move" onclick="moverPregunta(this, 'down')">arrow_downward</span>
                <div class="vertical-divider"></div>
                <span class="material-icons btn-delete" onclick="eliminarTarjeta(this)">delete_outline</span>
            </div>
        </div>
    `;

    container.appendChild(card);

    actualizarNumerosDeSeccion();

    setTimeout(() => {
        setActive(card);
    }, 10);
}

function eliminarTarjeta(btn) {
    const card = btn.closest('.form-card');
    const esSeccion = card.classList.contains('section-card');
    
    // Borramos la tarjeta del mapa
    card.remove();

    // Si era una sección, re-numeramos todas las que quedaron
    if (esSeccion) {
        actualizarNumerosDeSeccion();
    }
}


function actualizarNumerosDeSeccion() {
    const secciones = document.querySelectorAll('.section-card');
    secciones.forEach((card, index) => {
        const badge = card.querySelector('.section-badge');
        if (badge) {
            badge.innerText = `SECCIÓN ${index + 1}`;
        }
    });
}


function validarCamposPasoActual() {
    // Buscamos todos los inputs requeridos en la página visible
    const inputs = pages[currentPage].flatMap(card => Array.from(card.querySelectorAll('input[required], textarea[required], select[required]')));
    
    for (let input of inputs) {
        // 1. Verificamos si está vacío
        if (!input.value.trim()) {
            alert("Por favor, completá todos los campos obligatorios.");
            input.focus();
            return false;
        }

        // 2. Verificamos si cumple con el 'pattern' (RegEx)
        if (input.pattern) {
            const regex = new RegExp(input.pattern);
            if (!regex.test(input.value)) {
                // Si falla, mostramos el mensaje que pusimos en el atributo 'title'
                alert(input.title || "El formato de este campo no es válido.");
                input.focus();
                return false;
            }
        }
    }
    return true; 
}

function confirmarEliminacion(envioId, formId) {
    if (confirm('¿Estás seguro de que querés eliminar este registro de Compromiso Urbano? Esta acción no se puede deshacer.')) {
        window.location.href = 'eliminarEnvio.php?id=' + envioId + '&form_id=' + formId;
    }
}

// Ejecutar al cargar la página
document.addEventListener('DOMContentLoaded', initSignatures);
window.addEventListener('load', initSignatures);