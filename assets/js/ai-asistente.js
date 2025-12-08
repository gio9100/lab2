// ====================================================================
// ASISTENTE DE IA PARA EDITOR QUILL - LAB-EXPLORA
// ====================================================================
// Este archivo contiene 3 funcionalidades principales de inteligencia artificial
// para ayudar a los publicadores mientras escriben sus artículos científicos:
// 1. Generador automático de resúmenes
// 2. Sugerencias inteligentes de etiquetas/categorías
// 3. Verificador gramatical en tiempo real
// ====================================================================

// ====================================================================
// FUNCIÓN 1: GENERADOR AUTOMÁTICO DE RESÚMENES
// ====================================================================
// Esta función toma el texto completo del artículo y genera un resumen
// corto de máximo 3 oraciones usando un algoritmo extractivo
// (selecciona las oraciones más importantes del texto original)
// ====================================================================

function generarResumenIA() {
    // Mostrar que está procesando (spinner de carga)
    mostrarCargando('resumen');

    // Obtener el texto plano del editor Quill
    // quill.getText() devuelve el contenido sin etiquetas HTML
    const textoCompleto = quill.getText();

    // Verificar que haya suficiente contenido para generar un resumen
    // Si hay menos de 200 caracteres, no tiene sentido hacer un resumen
    if (textoCompleto.length < 200) {
        // Ocultar el spinner de carga
        ocultarCargando('resumen');

        // Mostrar mensaje de error al usuario en el panel de resultados
        document.getElementById('resumen-ia-resultado').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Escribe al menos 200 caracteres para generar un resumen significativo
            </div>
        `;

        // Salir de la función sin hacer nada más
        return;
    }

    // Llamar a la función que extrae las oraciones más importantes
    const resumenGenerado = extraerResumenInteligente(textoCompleto);

    // Ocultar el spinner ya que terminó el procesamiento
    ocultarCargando('resumen');

    // Mostrar el resumen generado en el panel con un diseño bonito
    document.getElementById('resumen-ia-resultado').innerHTML = `
        <div class="ai-result-card">
            <div class="ai-badge-header">
                <span class="ai-badge">✨ Generado con IA</span>
            </div>
            <p class="resultado-texto">${resumenGenerado}</p>
            <div class="ai-actions">
                <button onclick="copiarAlResumen('${escapeHTML(resumenGenerado)}')" class="btn btn-sm btn-primary">
                    <i class="bi bi-clipboard"></i> Usar este resumen
                </button>
                <button onclick="regenerarResumen()" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Regenerar
                </button>
            </div>
        </div>
    `;
}

// --------------------------------------------------------------------
// Función auxiliar que hace el trabajo pesado de extraer el resumen
// Usa un algoritmo extractivo simple pero efectivo
// --------------------------------------------------------------------
function extraerResumenInteligente(texto) {
    // PASO 1: Dividir el texto en oraciones individuales
    // Esta expresión regular busca puntos, signos de exclamación o interrogación
    // seguidos de un espacio (fin de oración)
    const oraciones = texto.match(/[^\.!\?]+[\.!\?]+/g) || [];

    // Si no hay suficientes oraciones, devolver el texto tal cual
    if (oraciones.length < 3) {
        return texto.substring(0, 280) + '...';
    }

    // PASO 2: Calcular un "peso" o "importancia" para cada oración
    // Las oraciones con más palabras "importantes" tendrán mayor peso
    const oracionesPonderadas = oraciones.map((oracion, indice) => {
        // Limpiar la oración y convertir a minúsculas para análisis
        const oracionLimpia = oracion.toLowerCase().trim();

        // Dividir la oración en palabras individuales
        const palabras = oracionLimpia.split(/\s+/);

        // Contar cuántas palabras "importantes" tiene (más de 5 letras)
        // Las palabras largas suelen ser más significativas (no artículos, preposiciones, etc.)
        const palabrasImportantes = palabras.filter(palabra => palabra.length > 5).length;

        // La primera oración del texto suele ser más importante (introducción)
        // Le damos un multiplicador de 2 para darle más peso
        const bonusPosicion = indice === 0 ? 2 : 1;

        // Si la oración contiene números, probablemente tenga datos importantes
        const tieneNumeros = /\d/.test(oracion) ? 1.5 : 1;

        // Calcular el peso final de la oración
        const peso = palabrasImportantes * bonusPosicion * tieneNumeros;

        // Devolver un objeto con la oración y su peso calculado
        return {
            texto: oracion.trim(),  // El texto de la oración sin espacios extras
            peso: peso,              // Su puntuación de importancia
            indiceOriginal: indice   // Su posición original (para mantener orden)
        };
    });

    // PASO 3: Ordenar las oraciones de mayor a menor peso
    // y seleccionar las 3 más importantes
    const mejoresOraciones = oracionesPonderadas
        .sort((a, b) => b.peso - a.peso)  // Ordenar de mayor a menor peso
        .slice(0, 3);  // Tomar solo las 3 primeras

    // PASO 4: Re-ordenar las oraciones seleccionadas según su posición original
    // para que el resumen tenga sentido cronológico
    mejoresOraciones.sort((a, b) => a.indiceOriginal - b.indiceOriginal);

    // PASO 5: Unir las oraciones seleccionadas con espacios
    let resumenFinal = mejoresOraciones.map(o => o.texto).join(' ');

    // PASO 6: Si el resumen es muy largo, cortarlo a 280 caracteres
    if (resumenFinal.length > 280) {
        resumenFinal = resumenFinal.substring(0, 277) + '...';
    }

    // Devolver el resumen generado
    return resumenFinal;
}

// --------------------------------------------------------------------
// Función para copiar el resumen generado al campo de resumen del formulario
// --------------------------------------------------------------------
function copiarAlResumen(texto) {
    // Obtener el elemento textarea del resumen en el formulario
    const campoResumen = document.getElementById('resumen');

    // Establecer el valor del textarea con el resumen generado
    campoResumen.value = texto;

    // Disparar el evento 'input' para que se actualice el contador de caracteres
    campoResumen.dispatchEvent(new Event('input'));

    // Hacer scroll suave hasta el campo de resumen para que el usuario lo vea
    campoResumen.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Resaltar el campo brevemente con un efecto visual
    campoResumen.classList.add('campo-actualizado');

    // Quitar el efecto después de 2 segundos
    setTimeout(() => {
        campoResumen.classList.remove('campo-actualizado');
    }, 2000);

    // Mostrar mensaje de éxito
    mostrarToast('✅ Resumen copiado al formulario', 'success');
}

// --------------------------------------------------------------------
// Función para regenerar el resumen (volver a ejecutar la IA)
// --------------------------------------------------------------------
function regenerarResumen() {
    // Simplemente volver a llamar a la función principal
    generarResumenIA();
}


// ====================================================================
// FUNCIÓN 2: SUGERENCIAS INTELIGENTES DE ETIQUETAS/CATEGORÍAS
// ====================================================================
// Esta función analiza el contenido del artículo y sugiere automáticamente
// las categorías científicas más apropiadas basándose en palabras clave
// ====================================================================

function sugerirEtiquetasIA() {
    // Mostrar indicador de carga
    mostrarCargando('etiquetas');

    // Obtener el título y el contenido del artículo
    const titulo = document.getElementById('titulo').value;
    const contenido = quill.getText();

    // Combinar título y contenido para analizar todo junto
    // El título tiene mucho peso para determinar la categoría
    const textoCompleto = titulo + ' ' + contenido;

    // Verificar que haya suficiente texto para analizar
    if (textoCompleto.trim().length < 50) {
        ocultarCargando('etiquetas');
        document.getElementById('etiquetas-ia-resultado').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Escribe al menos un título y algo de contenido para analizar las categorías
            </div>
        `;
        return;
    }

    // Llamar a la función que analiza el texto y detecta categorías
    const categoriasDetectadas = detectarCategoriasPorPalabrasClave(textoCompleto);

    // Ocultar el indicador de carga
    ocultarCargando('etiquetas');

    // Si no se detectaron categorías, mostrar mensaje
    if (categoriasDetectadas.length === 0) {
        document.getElementById('etiquetas-ia-resultado').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                No se detectaron categorías específicas. Intenta usar términos más técnicos.
            </div>
        `;
        return;
    }

    // Mostrar las categorías detectadas como badges clicables
    const badgesHTML = categoriasDetectadas.map(cat => `
        <span class="tag-sugerencia" onclick="seleccionarCategoria('${cat.id}')" 
              title="Relevancia: ${cat.peso} palabras clave encontradas">
            <i class="bi bi-stars"></i> ${cat.nombre}
            <small class="relevancia">(${cat.peso} match${cat.peso > 1 ? 'es' : ''})</small>
        </span>
    `).join('');

    // Renderizar el result ado en el panel
    document.getElementById('etiquetas-ia-resultado').innerHTML = `
        <div class="ai-result-card">
            <div class="ai-badge-header">
                <span class="ai-badge">🤖 Categorías detectadas por IA</span>
            </div>
            <p class="mb-3">Haz clic en una categoría para seleccionarla automáticamente:</p>
            <div class="tags-container">
                ${badgesHTML}
            </div>
        </div>
    `;
}

// --------------------------------------------------------------------
// Función que analiza el texto y detecta qué categorías científicas
// son más relevantes basándose en un diccionario de palabras clave
// --------------------------------------------------------------------
function detectarCategoriasPorPalabrasClave(texto) {
    // Convertir todo el texto a minúsculas para hacer comparaciones sin importar mayúsculas
    const textoMinusculas = texto.toLowerCase();

    // Diccionario de palabras clave para cada categoría científica
    // Cada categoría tiene: id (para el select), nombre (para mostrar), y palabras clave
    const diccionarioCientifico = [
        {
            id: '1',  // Este ID debe coincidir con el ID en la base de datos
            nombre: 'Química',
            palabrasClave: [
                'átomo', 'molécula', 'elemento', 'compuesto', 'reacción',
                'ácido', 'base', 'pH', 'enlace', 'químico', 'química',
                'valencia', 'ion', 'catión', 'anión', 'oxidación',
                'reducción', 'soluto', 'solvente', 'concentración',
                'molaridad', 'estequiometría', 'tabla periódica'
            ]
        },
        {
            id: '2',
            nombre: 'Física',
            palabrasClave: [
                'energía', 'fuerza', 'movimiento', 'velocidad', 'aceleración',
                'masa', 'peso', 'gravedad', 'fricción', 'inercia',
                'newton', 'joule', 'física', 'mecánica', 'termodinámica',
                'óptica', 'luz', 'onda', 'frecuencia', 'longitud de onda',
                'electricidad', 'magnetismo', 'campo', 'cuántico'
            ]
        },
        {
            id: '3',
            nombre: 'Biología',
            palabrasClave: [
                'célula', 'ADN', 'ARN', 'gen', 'cromosoma', 'genética',
                'evolución', 'darwin', 'organismo', 'ecosistema', 'biodiversidad',
                'fotosíntesis', 'respiración', 'mitocondria', 'cloroplasto',
                'membrana', 'núcleo', 'proteína', 'enzima', 'metabolismo',
                'especie', 'población', 'hábitat', 'ecología'
            ]
        },
        {
            id: '4',
            nombre: 'Matemáticas',
            palabrasClave: [
                'ecuación', 'función', 'variable', 'constante', 'derivada',
                'integral', 'límite', 'probabilidad', 'estadística',
                'álgebra', 'geometría', 'trigonometría', 'cálculo',
                'matriz', 'vector', 'teorema', 'demostración', 'axioma',
                'conjunto', 'infinito', 'logaritmo', 'exponencial'
            ]
        },
        {
            id: '5',
            nombre: 'Astronomía',
            palabrasClave: [
                'estrella', 'planeta', 'galaxia', 'universo', 'cosmos',
                'nebulosa', 'agujero negro', 'telescopio', 'órbita',
                'satélite', 'cometa', 'asteroide', 'sistema solar',
                'luna', 'sol', 'venus', 'marte', 'júpiter', 'saturno',
                'constelación', 'año luz', 'parsec', 'astronomía'
            ]
        },
        {
            id: '6',
            nombre: 'Geología',
            palabrasClave: [
                'roca', 'mineral', 'fósil', 'sedimento', 'erosión',
                'placa tectónica', 'volcán', 'terremoto', 'sismo',
                'corteza', 'manto', 'núcleo', 'magma', 'lava',
                'estrato', 'geología', 'tierra', 'suelo', 'cristal'
            ]
        }
    ];

    // Array para almacenar las categorías detectadas con su peso
    const categoriasEncontradas = [];

    // Recorrer cada categoría del diccionario
    for (const categoria of diccionarioCientifico) {
        // Contador de cuántas palabras clave de esta categoría están en el texto
        let coincidencias = 0;

        // Recorrer cada palabra clave de esta categoría
        for (const palabraClave of categoria.palabrasClave) {
            // Verificar si la palabra clave aparece en el texto
            // Usar expresión regular para buscar la palabra completa, no como parte de otra
            const regex = new RegExp('\\b' + palabraClave + '\\b', 'gi');

            // Si se encuentra la palabra, incrementar el contador
            if (regex.test(textoMinusculas)) {
                coincidencias++;
            }
        }

        // Si se encontraron al menos 2 palabras clave de esta categoría
        // considerarla como una categoría relevante
        if (coincidencias >= 2) {
            categoriasEncontradas.push({
                id: categoria.id,           // ID de la categoría
                nombre: categoria.nombre,   // Nombre para mostrar
                peso: coincidencias         // Número de palabras clave encontradas
            });
        }
    }

    // Ordenar las categorías por peso (más palabras clave = más relevante)
    // de mayor a menor
    categoriasEncontradas.sort((a, b) => b.peso - a.peso);

    // Devolver las categorías detectadas (máximo 3 para no abrumar al usuario)
    return categoriasEncontradas.slice(0, 3);
}

// --------------------------------------------------------------------
// Función para seleccionar automáticamente una categoría en el formulario
// cuando el usuario hace clic en una sugerencia
// --------------------------------------------------------------------
function seleccionarCategoria(categoriaId) {
    // Obtener el elemento select de categorías del formulario
    const selectCategoria = document.getElementById('categoria_id');

    // Establecer el valor del select con el ID de la categoría
    selectCategoria.value = categoriaId;

    // Disparar evento 'change' para que se actualice visualmente
    selectCategoria.dispatchEvent(new Event('change'));

    // Resaltar visualmente el select para que el usuario note el cambio
    selectCategoria.classList.add('campo-actualizado');

    // Hacer scroll suave hasta el select
    selectCategoria.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Quitar el resaltado después de 2 segundos
    setTimeout(() => {
        selectCategoria.classList.remove('campo-actualizado');
    }, 2000);

    // Mostrar mensaje de confirmación
    mostrarToast('✅ Categoría seleccionada', 'success');
}


// ====================================================================
// FUNCIÓN 3: VERIFICADOR GRAMATICAL
// ====================================================================
// Esta función revisa el texto en busca de errores gramaticales comunes
// en español y muestra sugerencias para corregirlos
// ====================================================================

function verificarGramaticaIA() {
    // Mostrar indicador de carga
    mostrarCargando('gramatica');

    // Obtener el texto completo del editor
    const texto = quill.getText();

    // Verificar que haya texto para analizar
    if (texto.trim().length < 20) {
        ocultarCargando('gramatica');
        document.getElementById('gramatica-ia-resultado').innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                Escribe al menos 20 caracteres para verificar gramática
            </div>
        `;
        return;
    }

    // Buscar errores gramaticales comunes
    const erroresEncontrados = buscarErroresGramaticales(texto);

    // Ocultar indicador de carga
    ocultarCargando('gramatica');

    // Si no hay errores, mostrar mensaje de éxito
    if (erroresEncontrados.length === 0) {
        document.getElementById('gramatica-ia-resultado').innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                ¡Excelente! No se detectaron errores gramaticales comunes.
            </div>
        `;
        return;
    }

    // Generar HTML para mostrar todos los errores encontrados
    const erroresHTML = erroresEncontrados.map((error, index) => `
        <div class="error-gramatical">
            <div class="error-header">
                <span class="error-tipo tipo-${error.tipo}">
                    <i class="bi bi-exclamation-circle"></i> ${error.tipoNombre}
                </span>
            </div>
            <div class="error-body">
                <p class="error-texto">
                    "${error.textoError}"
                </p>
                <p class="error-mensaje">
                    <i class="bi bi-lightbulb"></i> ${error.mensaje}
                </p>
                ${error.sugerencia ? `
                    <p class="error-sugerencia">
                        <strong>Sugerencia:</strong> "${error.sugerencia}"
                    </p>
                ` : ''}
            </div>
        </div>
    `).join('');

    // Mostrar los errores en el panel
    document.getElementById('gramatica-ia-resultado').innerHTML = `
        <div class="ai-result-card">
            <div class="ai-badge-header">
                <span class="ai-badge">📝 Análisis gramatical</span>
                <span class="badge bg-warning">${erroresEncontrados.length} sugerencia${erroresEncontrados.length > 1 ? 's' : ''}</span>
            </div>
            <div class="errores-lista">
                ${erroresHTML}
            </div>
        </div>
    `;
}

// --------------------------------------------------------------------
// Función que busca errores gramaticales comunes en el texto
// Usa expresiones regulares para detectar patrones incorrectos
// --------------------------------------------------------------------
function buscarErroresGramaticales(texto) {
    // Array para almacenar todos los errores encontrados
    const errores = [];

    // REGLA 1: Uso incorrecto de "por que" vs "porque" vs "por qué" vs "porqué"
    // Patrón: buscar "por que" (separado sin tilde) en contextos donde debería ser "porque" o "por qué"
    const regla1 = /\bpor que\b/gi;
    let match;
    while ((match = regla1.exec(texto)) !== null) {
        errores.push({
            tipo: 'ortografia',              // Tipo de error
            tipoNombre: 'Ortografía',        // Nombre para mostrar
            textoError: match[0],            // El texto que tiene el error
            posicion: match.index,           // Posición en el texto
            mensaje: 'Posible uso incorrecto de "por que". Verifica si debería ser "porque" (causal), "por qué" (interrogativo) o "el porqué" (sustantivo).',
            sugerencia: 'porque / por qué / el porqué'
        });
    }

    // REGLA 2: Falta de tilde en interrogativos
    // Buscar "que" sin tilde en contextos interrogativos
    const regla2 = /\bque es\b|\bque son\b|\bque significa\b/gi;
    while ((match = regla2.exec(texto)) !== null) {
        errores.push({
            tipo: 'ortografia',
            tipoNombre: 'Ortografía',
            textoError: match[0],
            posicion: match.index,
            mensaje: 'En preguntas, "que" debe llevar tilde: "qué"',
            sugerencia: match[0].replace('que', 'qué')
        });
    }

    // REGLA 3: "Habia" sin tilde (debe ser "había")
    const regla3 = /\bhabian?\b/gi;
    while ((match = regla3.exec(texto)) !== null) {
        errores.push({
            tipo: 'ortografia',
            tipoNombre: 'Ortografía',
            textoError: match[0],
            posicion: match.index,
            mensaje: 'Falta tilde en verbo imperfecto',
            sugerencia: match[0].slice(0, 3) + 'í' + match[0].slice(4)  // hab + í + a(n)
        });
    }

    // REGLA 4: Repetición de palabras (error de dedo)
    // Buscar palabras duplicadas seguidas, ejemplo: "el el artículo"
    const regla4 = /\b(\w+)\s+\1\b/gi;
    while ((match = regla4.exec(texto)) !== null) {
        errores.push({
            tipo: 'repeticion',
            tipoNombre: 'Repetición',
            textoError: match[0],
            posicion: match.index,
            mensaje: `La palabra "${match[1]}" está duplicada`,
            sugerencia: match[1]  // Mostrar la palabra una sola vez
        });
    }

    // REGLA 5: Uso de primera persona en texto científico
    // Los textos científicos deben ser impersonales
    const regla5 = /\b(yo|mi|nosotros|nuestro)\s+(creo|pienso|considero|opino|creemos|pensamos)/gi;
    while ((match = regla5.exec(texto)) !== null) {
        errores.push({
            tipo: 'estilo',
            tipoNombre: 'Estilo Académico',
            textoError: match[0],
            posicion: match.index,
            mensaje: 'En textos científicos se recomienda evitar la primera persona. Usa voz pasiva o impersonal.',
            sugerencia: 'se observa / se puede afirmar / los datos sugieren'
        });
    }

    // REGLA 6: Falta de concordancia de género
    // Ejemplo: "el problema afectada" (el es masculino, afectada es femenino)
    const regla6 = /\bel\s+\w+ad[ao]s?\b/gi;
    while ((match = regla6.exec(texto)) !== null) {
        const palabraCompleta = match[0];
        // Verificar si termina en "a" o "as" (femenino) después de "el" (masculino)
        if (/ad[a]s?$/.test(palabraCompleta)) {
            errores.push({
                tipo: 'concordancia',
                tipoNombre: 'Concordancia',
                textoError: match[0],
                posicion: match.index,
                mensaje: 'Posible falta de concordancia de género entre artículo y adjetivo',
                sugerencia: null
            });
        }
    }

    // REGLA 7: Doble espacio (error de formato)
    const regla7 = /\s{2,}/g;
    while ((match = regla7.exec(texto)) !== null) {
        errores.push({
            tipo: 'formato',
            tipoNombre: 'Formato',
            textoError: '(espacios múltiples)',
            posicion: match.index,
            mensaje: 'Se detectaron múltiples espacios seguidos',
            sugerencia: 'Usar un solo espacio'
        });
    }

    // REGLA 8: Falta de espacio después de punto, coma o dos puntos
    const regla8 = /[.,:](\w)/g;
    while ((match = regla8.exec(texto)) !== null) {
        errores.push({
            tipo: 'formato',
            tipoNombre: 'Formato',
            textoError: match[0],
            posicion: match.index,
            mensaje: 'Falta espacio después del signo de puntuación',
            sugerencia: match[0][0] + ' ' + match[0][1]
        });
    }

    // Devolver array con todos los errores encontrados
    // Limitado a máximo 10 errores para no abrumar al usuario
    return errores.slice(0, 10);
}


// ====================================================================
// FUNCIÓN 4: FORMATEADOR PROFESIONAL DE CONTENIDO
// ====================================================================
// Esta función toma el contenido del editor y lo formatea automáticamente
// aplicando reglas de estilo profesional para artículos científicos
// ====================================================================

function formatearContenidoProfesional() {
    // Mostrar indicador de carga
    mostrarCargando('formato');

    // Obtener el contenido HTML del editor Quill
    // Usamos .root.innerHTML porque necesitamos mantener el formato HTML
    const contenidoHTML = quill.root.innerHTML;

    // Verificar que haya contenido para formatear
    if (quill.getText().trim().length < 10) {
        ocultarCargando('formato');
        mostrarToast('⚠️ Escribe algo de contenido primero', 'warning');
        return;
    }

    // Crear un elemento div temporal para manipular el HTML
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = contenidoHTML;

    // PASO 1: Procesar cada párrafo (elementos <p>)
    const parrafos = tempDiv.querySelectorAll('p');

    parrafos.forEach((parrafo, indice) => {
        // Obtener el texto del párrafo
        let texto = parrafo.textContent;

        // Si el párrafo está vacío, saltar
        if (!texto.trim()) return;

        // --- CORRECCIÓN 1: Capitalizar primera letra ---
        // La primera letra de cada párrafo debe ser mayúscula
        texto = texto.charAt(0).toUpperCase() + texto.slice(1);

        // --- CORRECCIÓN 2: Espacios después de puntuación ---
        // Agregar espacio después de punto, coma, punto y coma si no existe
        texto = texto.replace(/\./g, '. ');      // Punto seguido de espacio
        texto = texto.replace(/,/g, ', ');       // Coma seguida de espacio
        texto = texto.replace(/;/g, '; ');       // Punto y coma seguido de espacio
        texto = texto.replace(/:/g, ': ');       // Dos puntos seguidos de espacio

        // --- CORRECCIÓN 3: Eliminar espacios múltiples ---
        // Reemplazar 2 o más espacios por uno solo
        texto = texto.replace(/\s+/g, ' ');

        // --- CORRECCIÓN 4: Eliminar espacios antes de puntuación ---
        // "Hola ." → "Hola."
        texto = texto.replace(/\s+\./g, '.');
        texto = texto.replace(/\s+,/g, ',');
        texto = texto.replace(/\s+;/g, ';');
        texto = texto.replace(/\s+:/g, ':');

        // --- CORRECCIÓN 5: Corregir espacios dobles después de corrección ---
        // Después de agregar espacios, pueden quedar dobles (ejemplo: ".  ")
        texto = texto.replace(/\.\s{2,}/g, '. ');
        texto = texto.replace(/,\s{2,}/g, ', ');

        // --- MEJORA 1: Detectar y resaltar términos científicos clave ---
        // Lista de palabras que suelen ser importantes en textos científicos
        const terminosClave = [
            'hipótesis', 'resultado', 'conclusión', 'metodología',
            'experimento', 'análisis', 'investigación', 'estudio',
            'observación', 'dato', 'evidencia', 'teoría'
        ];

        // Por cada término clave, si está al inicio de una oración, resaltarlo
        terminosClave.forEach(termino => {
            // Crear expresión regular para buscar el término al inicio de oración
            // \b = borde de palabra, para no encontrar en medio de otras palabras
            const regex = new RegExp('(^|\\. )(' + termino + ')', 'gi');

            // Reemplazar con el término en negrita
            texto = texto.replace(regex, '$1<strong>$2</strong>');
        });

        // Act ualizar el contenido del párrafo con el texto corregido
        parrafo.innerHTML = texto;

        // --- MEJORA 2: Agregar margen entre párrafos para mejor legibilidad ---
        // Solo si no es el último párrafo
        if (indice < parrafos.length - 1) {
            parrafo.style.marginBottom = '12px';
        }
    });

    // PASO 2: Detectar y formatear posibles títulos/subtítulos
    // Si un párrafo es muy corto (< 60 caracteres) y term ina sin punto,
    // probablemente sea un título
    parrafos.forEach(parrafo => {
        const texto = parrafo.textContent.trim();

        // Características de un título:
        // - Menos de 60 caracteres
        // - No termina en punto
        // - No está vacío
        if (texto.length > 0 && texto.length < 60 && !texto.endsWith('.')) {
            // Convertir a header nivel 3 (subtítulo)
            const h3 = document.createElement('h3');
            h3.textContent = texto;
            h3.style.marginTop = '20px';
            h3.style.marginBottom = '10px';

            // Reemplazar el párrafo con el h3
            parrafo.replaceWith(h3);
        }
    });

    // PASO 3: Procesar listas (si existen)
    const listas = tempDiv.querySelectorAll('ul, ol');

    listas.forEach(lista => {
        // Agregar espaciado a cada elemento de la lista
        const items = lista.querySelectorAll('li');

        items.forEach(item => {
            let texto = item.textContent.trim();

            // Capitalizar primera letra de cada elemento
            texto = texto.charAt(0).toUpperCase() + texto.slice(1);

            // Si no termina en punto, agregarlo (buena práctica)
            if (!texto.endsWith('.') && !texto.endsWith(':')) {
                texto += '.';
            }

            item.textContent = texto;
        });

        // Agregar margen a la lista completa
        lista.style.marginTop = '10px';
        lista.style.marginBottom = '10px';
    });

    // PASO 4: Procesar negritas existentes (limpiarlas si son excesivas)
    const negritas = tempDiv.querySelectorAll('strong, b');

    // Si hay demasiadas negritas (más de 20% del texto), es contraproducente
    const totalPalabras = quill.getText().split(/\s+/).length;
    let palabrasEnNegrita = 0;

    negritas.forEach(negrita => {
        palabrasEnNegrita += negrita.textContent.split(/\s+/).length;
    });

    // Si más del 30% está en negrita, advertir
    const porcentajeNegrita = (palabrasEnNegrita / totalPalabras) * 100;

    // PASO 5: Aplicar el contenido formateado al editor
    quill.root.innerHTML = tempDiv.innerHTML;

    // Ocultar indicador de carga
    ocultarCargando('formato');

    // Preparar mensaje de resumen
    let mensajeResumen = '<ul style="margin: 0; padding-left: 20px;">';
    mensajeResumen += '<li>✅ Espaciado corregido</li>';
    mensajeResumen += '<li>✅ Puntuación normalizada</li>';
    mensajeResumen += '<li>✅ Capitalización ajustada</li>';
    mensajeResumen += '<li>✅ Términos clave resaltados</li>';

    if (porcentajeNegrita > 30) {
        mensajeResumen += '<li>⚠️ Demasiadas negritas (' + porcentajeNegrita.toFixed(0) + '%), considera reducirlas</li>';
    }

    mensajeResumen += '</ul>';

    // Mostrar resultado
    document.getElementById('formato-ia-resultado').innerHTML = `
        <div class="ai-result-card">
            <div class="ai-badge-header">
                <span class="ai-badge">✨ Contenido formateado</span>
            </div>
            <p class="mb-2"><strong>Cambios aplicados:</strong></p>
            ${mensajeResumen}
            <div class="alert alert-success mt-3 mb-0" style="font-size: 0.9rem;">
                <i class="bi bi-check-circle"></i>
                El contenido ha sido formateado siguiendo estándares profesionales para artículos científicos.
            </div>
        </div>
    `;

    // Mostrar toast de confirmación
    mostrarToast('✅ Contenido formateado profesionalmente', 'success');
}


// ====================================================================
// FUNCIONES AUXILIARES COMPARTIDAS
// ====================================================================
// Estas funciones son utilizadas por las 3 funcionalidades principales
// ====================================================================

// --------------------------------------------------------------------
// Función para mostrar un indicador de carga (spinner) mientras procesa
// --------------------------------------------------------------------
function mostrarCargando(tipo) {
    // Determinar en cuál panel mostrar el spinner según el tipo
    let contenedorId;

    if (tipo === 'resumen') {
        contenedorId = 'resumen-ia-resultado';
    } else if (tipo === 'etiquetas') {
        contenedorId = 'etiquetas-ia-resultado';
    } else if (tipo === 'gramatica') {
        contenedorId = 'gramatica-ia-resultado';
    } else if (tipo === 'formato') {
        contenedorId = 'formato-ia-resultado';
    }

    // Mostrar spinner con mensaje de carga
    document.getElementById(contenedorId).innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Procesando...</span>
            </div>
            <p class="mt-2 text-muted">Analizando con IA...</p>
        </div>
    `;
}

// --------------------------------------------------------------------
// Función para ocultar el indicador de carga
// --------------------------------------------------------------------
function ocultarCargando(tipo) {
    // Esta función podría limpiar el contenedor, pero como siempre
    // reemplazamos el contenido inmediatamente después, no es necesario
    // La dejamos aquí por si en el futuro queremos agregar lógica adicional
}

// --------------------------------------------------------------------
// Función para escapar caracteres HTML y evitar problemas de seguridad (XSS)
// --------------------------------------------------------------------
function escapeHTML(texto) {
    // Crear un elemento div temporal
    const div = document.createElement('div');

    // Asignar el texto como textContent (esto escapa automáticamente HTML)
    div.textContent = texto;

    // Devolver el HTML escapado
    return div.innerHTML;
}

// --------------------------------------------------------------------
// Función para mostrar notificaciones toast (mensajes pequeños y temporales)
// --------------------------------------------------------------------
function mostrarToast(mensaje, tipo = 'info') {
    // Crear elemento div para el toast
    const toast = document.createElement('div');

    // Determinar la clase CSS según el tipo de mensaje
    let claseColor;
    if (tipo === 'success') {
        claseColor = 'bg-success';
    } else if (tipo === 'error') {
        claseColor = 'bg-danger';
    } else if (tipo === 'warning') {
        claseColor = 'bg-warning';
    } else {
        claseColor = 'bg-info';
    }

    // Asignar clases CSS al toast
    toast.className = `toast align-items-center text-white ${claseColor} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');

    // Contenido del toast
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${mensaje}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    // Agregar el toast al cuerpo del documento
    document.body.appendChild(toast);

    // Inicializar el toast de Bootstrap
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,   // Se oculta automáticamente
        delay: 3000       // Después de 3 segundos
    });

    // Mostrar el toast
    bsToast.show();

    // Eliminar el elemento del DOM cuando se oculte
    toast.addEventListener('hidden.bs.toast', function () {
        toast.remove();
    });
}

// ====================================================================
// INICIALIZACIÓN AL CARGAR LA PÁGINA
// ====================================================================
// Este código se ejecuta cuando la página termina de cargar
// ====================================================================

document.addEventListener('DOMContentLoaded', function () {
    // Verificar que exista el editor Quill antes de activar los asistentes
    if (typeof quill === 'undefined') {
        console.warn('El editor Quill no está disponible. Los asistentes de IA no se activarán.');
        return;
    }

    // Mensaje en consola para desarrollo (debug)
    console.log('✨ Asistente de IA para Quill inicializado correctamente');
});
