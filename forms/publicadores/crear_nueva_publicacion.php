<?php
// ============================================================================
// ARCHIVO: crear_nueva_publicacion.php
// PROPÓSITO: Formulario para que los publicadores creen nuevas publicaciones
// ============================================================================

// PASO 1: Iniciar sesión PHP
// session_start() DEBE ser lo primero. Activa el sistema de sesiones.
session_start();

// PASO 2: Incluir archivo de configuración
// Trae la conexión a la BD ($conn) y funciones útiles
require_once __DIR__ . '/config-publicadores.php';

// PASO 3: Verificar que el usuario esté logueado
// Si no tiene sesión de publicador, lo enviamos al login
if (!isset($_SESSION['publicador_id'])) {
    header('Location: login.php');
    exit(); // IMPORTANTE: Detener ejecución después de redirigir
}

// PASO 4: Obtener datos de la sesión
$publicador_id = $_SESSION['publicador_id'];
$publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Publicador';

// PASO 5: Obtener categorías de la base de datos
// Esto llena el <select> de categorías en el formulario
$categorias = obtenerCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Publicación - Lab-Explorer</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .form-control, .form-select {
            border-radius: 10px;
        }
        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .preview-principal {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 10px;
        }
        /* Quill Editor Styles */
        #editor-container {
            height: 500px;
            background: white;
        }
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background: #f8f9fa;
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

    <!-- HEADER (Barra superior con logo y menú) -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <!-- Logo de Lab-Explorer -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <!-- Información del usuario y botón de cerrar sesión -->
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-pen-fancy"></i> Crear Nueva Publicación
                </h2>
                
                <div class="alert alert-info">
                    <small>Categorías disponibles: <strong><?php echo count($categorias); ?></strong></small>
                </div>
                
                <form method="POST" action="guardar_publicacion.php" enctype="multipart/form-data" id="formPublicacion">
                    <input type="hidden" name="publicador_id" value="<?php echo $publicador_id; ?>">
                    <input type="hidden" name="estado" value="revision">
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="titulo" class="form-label fw-bold">Título *</label>
                                <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                                       placeholder="Título de la publicación" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="categoria_id" class="form-label fw-bold">Categoría *</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Selecciona una categoría</option>
                                            <?php
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

                            <div class="mb-3">
                                <label for="imagen_principal" class="form-label fw-bold">
                                    <i class="fas fa-image"></i> Imagen Principal
                                </label>
                                <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                       accept="image/*" onchange="previewImagenPrincipal(this)">
                                <div id="preview_principal" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="resumen" class="form-label fw-bold">Resumen</label>
                                <textarea class="form-control" id="resumen" name="resumen" rows="3" 
                                          placeholder="Breve descripción de la publicación" 
                                          maxlength="300"></textarea>
                                <div class="character-count mt-1">
                                    <span id="resumen-count">0</span>/300 caracteres
                                </div>
                            </div>

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
                                <!-- Editor Quill -->
                                <div id="editor-container"></div>
                                <!-- Campo oculto para enviar el contenido HTML -->
                                <textarea class="d-none" id="contenido" name="contenido"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> Etiquetas</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="tags" class="form-label fw-bold">Etiquetas</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       placeholder="etiqueta1, etiqueta2, etiqueta3">
                                <div class="form-text">Separa las etiquetas con comas</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index-publicadores.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane"></i> Enviar para Revisión
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- QUILL EDITOR JS (Librería para el editor de texto enriquecido) -->
    <!-- ================================================================ -->
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <!-- Image Resize Module -->
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    
    <script>
        // ========================================================================
        // SECCIÓN 1: INICIALIZACIÓN DEL EDITOR QUILL
        // ========================================================================
        // Quill es una librería que convierte un <div> en un editor de texto rico
        // (como Microsoft Word pero en el navegador)
        
        // Registramos el módulo de Image Resize
        // (Aunque al usar el script CDN suele registrarse solo, esto asegura que funcione)
        
        // Creamos una nueva instancia de Quill
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                // Configuración del módulo de redimensionado de imágenes
                imageResize: {
                    displaySize: true, // Muestra el tamaño en pixeles
                    modules: [ 'Resize', 'DisplaySize', 'Toolbar' ] // Habilita redimensionar, ver tamaño y barra de herramientas de imagen
                },
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'font': [] }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    ['bold', 'italic', 'underline', 'strike'],
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
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            },
            placeholder: 'Escribe aquí el contenido de tu publicación...'
        });

        // ========================================================================
        // SECCIÓN 2: SINCRONIZACIÓN CON EL FORMULARIO
        // ========================================================================
        // PROBLEMA: Los formularios HTML NO envían el contenido de un <div>
        // SOLUCIÓN: Antes de enviar, copiamos el HTML del editor a un <textarea> oculto
        
        // Escuchamos el evento 'submit' (cuando el usuario da clic en "Enviar")
        document.getElementById('formPublicacion').addEventListener('submit', function(e) {
            // Obtenemos el HTML completo del editor
            // quill.root.innerHTML contiene todo el HTML con estilos, negritas, etc.
            const contenidoHTML = quill.root.innerHTML;
            
            // Obtenemos solo el texto plano (sin HTML) para validar
            // trim() quita espacios en blanco al inicio y final
            const contenidoTexto = quill.getText().trim();
            
            // VALIDACIÓN: Verificar que el usuario escribió algo
            // Si está vacío o solo tiene un párrafo vacío (<p><br></p>)
            if (contenidoTexto.length === 0 || contenidoHTML === '<p><br></p>') {
                e.preventDefault(); // Detenemos el envío del formulario
                alert('⚠️ Por favor escribe el contenido de la publicación');
                return false; // Salimos de la función
            }
            
            // CRUCIAL: Copiamos el HTML al textarea oculto
            // Este textarea SÍ se envía en el formulario como $_POST['contenido']
            document.getElementById('contenido').value = contenidoHTML;
            
            // Si llegamos aquí, el formulario se enviará normalmente
        });

        // ========================================================================
        // SECCIÓN 3: PREVISUALIZACIÓN DE IMAGEN
        // ========================================================================
        // Esta función se ejecuta cuando el usuario selecciona una imagen
        // Muestra una vista previa antes de subir el archivo
        
        function previewImagenPrincipal(input) {
            // Obtenemos el div donde mostraremos la preview
            const preview = document.getElementById('preview_principal');
            preview.innerHTML = ''; // Limpiamos cualquier preview anterior
            
            // Verificamos que haya un archivo seleccionado
            // input.files[0] es el primer archivo (solo permitimos uno)
            if (input.files && input.files[0]) {
                // FileReader es un objeto de JavaScript para leer archivos
                const reader = new FileReader();
                
                // onload se ejecuta cuando termina de leer el archivo
                reader.onload = function(e) {
                    // e.target.result contiene la imagen en formato base64
                    // La insertamos como <img> en el div de preview
                    preview.innerHTML = `<img src="${e.target.result}" class="preview-principal" alt="Preview">`;
                };
                
                // Iniciamos la lectura del archivo como Data URL (base64)
                reader.readAsDataURL(input.files[0]);
            }
        }

        // ========================================================================
        // SECCIÓN 4: CONTADORES DE CARACTERES
        // ========================================================================
        // Estos contadores muestran cuántos caracteres ha escrito el usuario
        // en tiempo real (mientras escribe)
        
        // Contador para el campo "Resumen" (máximo 300 caracteres)
        document.getElementById('resumen').addEventListener('input', function() {
            // 'this' se refiere al textarea de resumen
            // this.value.length cuenta cuántos caracteres tiene
            const count = this.value.length;
            
            // Actualizamos el <span> que muestra el número
            document.getElementById('resumen-count').textContent = count;
        });

        // Contador para el campo "Meta Descripción" (máximo 160 caracteres)
        document.getElementById('meta_descripcion').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('meta-count').textContent = count;
        });

        // INICIALIZACIÓN: Establecer los contadores al cargar la página
        // Esto es útil si el navegador recuerda valores anteriores
        document.getElementById('resumen-count').textContent = 
            document.getElementById('resumen').value.length;
        document.getElementById('meta-count').textContent = 
            document.getElementById('meta_descripcion').value.length;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
