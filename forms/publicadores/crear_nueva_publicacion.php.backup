<?php
// 1. INICIO DE SESIÓN
// session_start() es fundamental. Arranca la sesión de PHP para que podamos acceder a $_SESSION.
// Sin esto, no sabríamos quién está logueado.
session_start();

// 2. INCLUIR CONFIGURACIÓN
// require_once trae el archivo 'config-publicadores.php'.
// __DIR__ es una "constante mágica" que nos da la ruta del directorio actual.
// Este archivo config tiene la conexión a la BD y funciones útiles.
require_once __DIR__ . '/config-publicadores.php';

// 3. VERIFICACIÓN DE SEGURIDAD (LOGIN)
// Aquí preguntamos: "¿Existe la variable 'publicador_id' en la sesión?"
// Si NO existe (!isset), significa que el usuario no se ha logueado.
if (!isset($_SESSION['publicador_id'])) {
    // Lo mandamos a la página de login.
    header('Location: login.php');
    // exit() es CRUCIAL. Detiene la ejecución del script inmediatamente.
    // Si no lo pones, el resto del código se seguiría ejecutando aunque redirijas.
    exit();
}

// 4. VARIABLES DE SESIÓN
// Guardamos el ID y el nombre en variables locales para usarlas más fácil en el HTML.
// El '??' es el operador de fusión de null. Si $_SESSION['publicador_nombre'] no existe, usa 'Publicador'.
$publicador_id = $_SESSION['publicador_id'];
$publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Publicador';

// 5. OBTENER CATEGORÍAS
// Llamamos a una función (que está en config-publicadores.php) para traer las categorías de la BD.
// Esto nos devuelve un array con las categorías para llenar el <select> más abajo.
$categorias = obtenerCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Publicación - Lab-Explorer</title>
    
    <!-- CARGA DE FUENTES Y ESTILOS (CSS) -->
    <!-- Fuentes de Google (Roboto, Poppins, Nunito) -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Bootstrap (Framework de diseño) -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Font Awesome (Iconos extra como el lápiz o el avión de papel) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Quill Editor CSS (Estilos para el editor de texto enriquecido) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <!-- Estilos propios del proyecto -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <!-- ESTILOS ESPECÍFICOS PARA ESTA PÁGINA -->
    <style>
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1); /* Sombra suave */
        }
        .form-control, .form-select {
            border-radius: 10px; /* Bordes redondeados en inputs */
        }
        .character-count {
            font-size: 0.875rem;
            color: #6c757d; /* Color gris para el contador de caracteres */
        }
        .preview-principal {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain; /* Para que la imagen no se deforme */
            border-radius: 8px;
            margin-top: 10px;
        }
        /* Estilos para el editor Quill (el cuadro de texto grande) */
        #editor-container {
            height: 500px; /* Altura fija del editor */
            background: white;
        }
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background: #f8f9fa; /* Fondo gris claro para la barra de herramientas */
            padding: 12px;
        }
        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            font-size: 16px;
        }
    </style>
</head>
<body class="publicador-page">

    <!-- HEADER (Barra superior) -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- Mostramos el nombre del publicador logueado -->
                        <span class="saludo">🧪 Publicador: <?= htmlspecialchars($publicador_nombre) ?></span>
                        <a href="logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-pen-fancy"></i> Crear Nueva Publicación
                </h2>
                
                <!-- Alerta informativa simple -->
                <div class="alert alert-info">
                    <small>Categorías: <?php echo count($categorias); ?></small>
                </div>
                
                <!-- 
                    FORMULARIO PRINCIPAL 
                    method="POST": Los datos se envían de forma oculta (no en la URL).
                    action="guardar_publicacion.php": AQUÍ ES DONDE SE ENVÍAN LOS DATOS.
                        Este archivo 'guardar_publicacion.php' es el que recibe todo, guarda en la BD
                        y es el que se encarga de ENVIAR EL CORREO a los admins.
                    enctype="multipart/form-data": Necesario para poder subir archivos (imágenes).
                -->
                        </div>
                        <div class="card-body">
                            <!-- Título -->
                            <div class="mb-3">
                                <label for="titulo" class="form-label fw-bold">Título *</label>
                                <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                                       placeholder="Título de la publicación" required>
                            </div>
                            
                            <div class="row">
                                <!-- Selector de Categoría -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoria_id" class="form-label fw-bold">Categoría *</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Selecciona una categoría</option>
                                            <?php
                                            // Recorremos el array de categorías para crear las opciones del select
                                            if (count($categorias) > 0) {
                                                foreach ($categorias as $categoria) {
                                                    echo '<option value="' . $categoria['id'] . '">';
                                                    echo htmlspecialchars($categoria['nombre']);
                                                    echo '</option>';
                                                }
                                            } else {
                                                echo '<option value="">No hay categorías disponibles</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <!-- Selector de Tipo de Contenido -->
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo" class="form-label fw-bold">Tipo de Contenido</label>
                                        <select class="form-select" id="tipo" name="tipo">
                                            <option value="articulo" selected>Artículo</option>
                                            <option value="caso_clinico">Caso Clínico</option>
                                            <option value="estudio">Estudio</option>
                                            <option value="revision">Revisión</option>
                                            <option value="noticia">Noticia</option>
                                            <option value="guia">Guía</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Subida de Imagen Principal -->
                            <div class="mb-3">
                                <label for="imagen_principal" class="form-label fw-bold">
                                    <i class="fas fa-image"></i> Imagen Principal
                                </label>
                                <!-- accept="image/*" restringe el selector de archivos solo a imágenes -->
                                <!-- onchange="previewImagenPrincipal(this)" llama a la función JS para mostrar la previsualización -->
                                <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                       accept="image/*" onchange="previewImagenPrincipal(this)">
                                <div id="preview_principal" class="mt-2"></div>
                            </div>
                            
                            <!-- Resumen -->
                            <div class="mb-3">
                                <label for="resumen" class="form-label fw-bold">Resumen</label>
                                <textarea class="form-control" id="resumen" name="resumen" rows="3" 
                                          placeholder="Breve descripción de la publicación" 
                                          maxlength="300"></textarea>
                                <div class="character-count mt-1">
                                    <span id="resumen-count">0</span>/300 caracteres
                                </div>
                            </div>

                            <!-- Meta Descripción (SEO) -->
                            <div class="mb-3">
                                <label for="meta_descripcion" class="form-label fw-bold">Meta Descripción (SEO)</label>
                                <textarea class="form-control" id="meta_descripcion" name="meta_descripcion" rows="2" 
                                          placeholder="Descripción para motores de búsqueda (máx 160 caracteres)"
                                          maxlength="160"></textarea>
                                <div class="character-count mt-1">
                                    <span id="meta-count">0</span>/160 caracteres
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 2: CONTENIDO (EDITOR QUILL) -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-file-alt"></i> Contenido</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Contenido * 
                                    <small class="text-muted">(Usa el botón de imagen en la barra de herramientas para insertar imágenes)</small>
                                </label>
                                <!-- Este div es donde Quill renderiza el editor visual -->
                                <div id="editor-container"></div>
                                
                                <!-- 
                                    TRUCO DEL EDITOR:
                                    Los formularios HTML no envían el contenido de un div (como #editor-container).
                                    Solo envían inputs, textareas, selects, etc.
                                    Por eso tenemos este textarea oculto (class="d-none").
                                    Antes de enviar el formulario, usamos Javascript para copiar el HTML del editor
                                    dentro de este textarea. Así se envía al servidor.
                                -->
                                <textarea class="d-none" id="contenido" name="contenido"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 3: ETIQUETAS -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> Etiquetas y Estado</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="tags" class="form-label fw-bold">Etiquetas</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       placeholder="etiqueta1, etiqueta2, etiqueta3">
                                <div class="form-text">Dejar vacío para publicar inmediatamente</div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES DE ACCIÓN -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index-publicadores.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Publicar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- CARGA DE SCRIPTS -->
    <!-- Librería Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    
    <script>
        // 1. CONFIGURACIÓN DEL EDITOR QUILL
        // Aquí inicializamos el editor rico en texto.
        var quill = new Quill('#editor-container', {
            theme: 'snow', // Tema estándar de Quill
            modules: {
                toolbar: [
                    // Definimos qué botones aparecen en la barra de herramientas
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }], // Títulos
                    [{ 'font': [] }], // Tipografías
                    [{ 'size': ['small', false, 'large', 'huge'] }], // Tamaños
                    ['bold', 'italic', 'underline', 'strike'], // Estilos básicos
                    // Selector de colores (texto y fondo) con una paleta personalizada
                    [{ 'color': ['#000000', '#e60000', '#ff9900', '#ffff00', '#008a00', '#0066cc', '#9933ff', 
                                 '#ffffff', '#facccc', '#ffebcc', '#ffffcc', '#cce8cc', '#cce0f5', '#ebd6ff', 
                                 '#bbbbbb', '#f06666', '#ffc266', '#ffff66', '#66b966', '#66a3e0', '#c285ff', 
                                 '#888888', '#a10000', '#b26b00', '#b2b200', '#006100', '#0047b2', '#6b24b2', 
                                 '#444444', '#5c0000', '#663d00', '#666600', '#003700', '#002966', '#3d1466'] }, 
                     { 'background': ['#000000', '#e60000', '#ff9900', '#ffff00', '#008a00', '#0066cc', '#9933ff', 
                                      '#ffffff', '#facccc', '#ffebcc', '#ffffcc', '#cce8cc', '#cce0f5', '#ebd6ff', 
                                      '#bbbbbb', '#f06666', '#ffc266', '#ffff66', '#66b966', '#66a3e0', '#c285ff', 
                                      '#888888', '#a10000', '#b26b00', '#b2b200', '#006100', '#0047b2', '#6b24b2', 
                                      '#444444', '#5c0000', '#663d00', '#666600', '#003700', '#002966', '#3d1466'] }],
                    [{ 'script': 'sub'}, { 'script': 'super' }], // Superíndice y subíndice
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }], // Listas
                    [{ 'indent': '-1'}, { 'indent': '+1' }], // Indentación
                    [{ 'align': [] }], // Alineación
                    ['blockquote', 'code-block'], // Citas y código
                    ['link', 'image', 'video'], // Multimedia
                    ['clean'] // Limpiar formato
                ]
            },
            placeholder: 'Escribe aquí el contenido de tu publicación...'
        });

        // 2. MANEJO DEL ENVÍO DEL FORMULARIO
        // Escuchamos el evento 'submit' del formulario
        document.getElementById('formPublicacion').addEventListener('submit', function(e) {
            // Obtenemos el HTML generado por Quill
            const contenidoHTML = quill.root.innerHTML;
            // Obtenemos solo el texto plano para validar si está vacío
            const contenidoTexto = quill.getText().trim();
            
            // Validación: Si no hay texto o solo hay un párrafo vacío
            if (contenidoTexto.length === 0 || contenidoHTML === '<p><br></p>') {
                e.preventDefault(); // Detenemos el envío
                alert('⚠️ Por favor escribe el contenido de la publicación');
                return false;
            }
            
            // CRUCIAL: Copiamos el HTML del editor al textarea oculto 'contenido'
            // Esto es lo que realmente se envía al servidor en $_POST['contenido']
            document.getElementById('contenido').value = contenidoHTML;
        });

        // 3. PREVISUALIZACIÓN DE IMAGEN
        // Función que se llama cuando el usuario selecciona un archivo
        function previewImagenPrincipal(input) {
            const preview = document.getElementById('preview_principal');
            preview.innerHTML = ''; // Limpiamos preview anterior
            
            // Si hay un archivo seleccionado
            if (input.files && input.files[0]) {
                const reader = new FileReader(); // Objeto para leer archivos
                
                // Cuando termine de leer el archivo...
                reader.onload = function(e) {
                    // Insertamos una etiqueta img con los datos del archivo (base64)
                    preview.innerHTML = `<img src="${e.target.result}" class="preview-principal" alt="Preview">`;
                };
                
                // Leemos el archivo como una URL de datos
                reader.readAsDataURL(input.files[0]);
            }
        }

        // 4. CONTADORES DE CARACTERES
        // Simples listeners que actualizan el número cuando el usuario escribe
        
        // Para el resumen
        document.getElementById('resumen').addEventListener('input', function() {
            document.getElementById('resumen-count').textContent = this.value.length;
        });

        // Para la meta descripción
        document.getElementById('meta_descripcion').addEventListener('input', function() {
            document.getElementById('meta-count').textContent = this.value.length;
        });

        // Inicializamos los contadores al cargar la página (por si el navegador recuerda los valores)
        document.getElementById('resumen-count').textContent = document.getElementById('resumen').value.length;
        document.getElementById('meta-count').textContent = document.getElementById('meta_descripcion').value.length;
    </script>

    <!-- Bootstrap JS -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>