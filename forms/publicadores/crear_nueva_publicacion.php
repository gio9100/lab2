<?php
// Iniciar sesión para manejar variables de usuario
session_start();

// Incluir configuración y funciones de publicadores
require_once __DIR__ . '/config-publicadores.php';

// Verificar si el usuario ha iniciado sesión como publicador
if (!isset($_SESSION['publicador_id'])) {
    // Si no está logueado, redirigir al login
    header('Location: login.php');
    exit();
}

// Obtener ID y nombre del publicador de la sesión
$publicador_id = $_SESSION['publicador_id'];
$publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Publicador';

// Obtener las categorías disponibles para el formulario
$categorias = obtenerCategorias($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Publicación - Lab-Explora</title>
    
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Estilos Vendor -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Estilos Principales -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <!-- Driver.js para Onboarding -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    
    <!-- Estilos del Editor Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <!-- Estilos del Asistente de IA -->
    <link href="../../assets/css/ai-asistente.css" rel="stylesheet">
    
    <style>
        /* Estilos personalizados para la tarjeta */
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        /* Bordes redondeados para inputs */
        .form-control, .form-select {
            border-radius: 10px;
        }
        /* Estilo para el contador de caracteres */
        .character-count {
            font-size: 0.875rem;
            color: #6c757d;
        }
        /* Estilo para la previsualización de imagen */
        .preview-principal {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            border-radius: 8px;
            margin-top: 10px;
        }
        /* Altura del editor Quill */
        #editor-container {
            height: 500px;
            background: white;
        }
        /* Bordes redondeados para la barra de herramientas de Quill */
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            background: #f8f9fa;
            padding: 12px;
        }
        /* Bordes redondeados para el contenedor de Quill */
        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            font-size: 16px;
        }
    </style>
</head>
<body class="publicador-page">

    <!-- Encabezado -->
    <header id="header" class="header position-relative">
    
    <?php
    // Verificar si el asistente de escritura está habilitado
    $assistant_enabled = false;
    $check_assist = $conn->query("SELECT enable_writing_assistant FROM configuracion_sistema LIMIT 1");
    if ($check_assist && $check_assist->num_rows > 0) {
        $assistant_enabled = ($check_assist->fetch_assoc()['enable_writing_assistant'] == 1);
    }
    ?>

        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Hamburger Button -->
                    <button class="btn btn-outline-primary d-md-none me-2" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <!-- Logo -->
                    <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">Lab-Explora</h1><span></span>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- Saludo al publicador -->
                        <span class="saludo d-none d-md-inline">🧪 Publicador: <?= htmlspecialchars($publicador_nombre) ?></span>
                        <a href="logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Barra lateral de navegación (Desktop & Mobile Overlay) -->
                <div class="col-md-3 sidebar-wrapper" id="sidebarWrapper">
                    <!-- Mobile Close Button -->
                    <div class="d-flex justify-content-end d-md-none p-2">
                        <button class="btn-close" id="sidebarClose"></button>
                    </div>
                    <?php include 'sidebar-publicador.php'; ?>
                </div>

                <!-- Contenido principal -->
                <div class="col-md-9">
                    <div class="section-title" data-aos="fade-up">
                        <h2>Crear Nueva Publicación</h2>
                        <p class="text-muted">Comparte tu conocimiento con la comunidad científica</p>
                    </div>

                    <!-- Contenedor flex para editor + sidebar de IA -->
                    <div class="editor-ai-container">
                        
                        <!-- Parte izquierda: Editor Principal -->
                        <div class="editor-main">
                            <!-- Formulario de creación -->
                            <div class="admin-card" data-aos="fade-up" data-aos-delay="100">
                                <div class="card-body p-4">
                                    <!-- Formulario con soporte para subida de archivos (multipart/form-data) -->
                                    <form id="form-publicacion" action="guardar_publicacion.php" method="POST" enctype="multipart/form-data">
                                
                                <!-- Título -->
                                <div class="mb-4">
                                    <label for="titulo" class="form-label fw-bold">Título de la Publicación *</label>
                                    <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" required 
                                           placeholder="Escribe un título descriptivo e interesante">
                                    <div class="character-count text-end mt-1">
                                        <span id="titulo-count">0</span>/150 caracteres
                                    </div>
                                </div>

                                <!-- Resumen -->
                                <div class="mb-4">
                                    <label for="resumen" class="form-label fw-bold">Resumen Corto *</label>
                                    <textarea class="form-control" id="resumen" name="resumen" rows="3" required
                                              placeholder="Breve descripción que aparecerá en las tarjetas de vista previa"></textarea>
                                    <div class="character-count text-end mt-1">
                                        <span id="resumen-count">0</span>/300 caracteres
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <!-- Categoría -->
                                    <div class="col-md-6">
                                        <label for="categoria_id" class="form-label fw-bold">Categoría *</label>
                                        <select class="form-select" id="categoria_id" name="categoria_id" required>
                                            <option value="">Selecciona una categoría</option>
                                            <!-- Iterar sobre las categorías obtenidas -->
                                            <?php foreach ($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Imagen Principal -->
                                    <div class="col-md-6">
                                        <label for="imagen_principal" class="form-label fw-bold">Imagen Principal *</label>
                                        <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                               accept="image/jpeg,image/png,image/webp" required>
                                        <div class="form-text">Formatos: JPG, PNG, WEBP. Máx: 5MB</div>
                                        <!-- Contenedor para previsualización -->
                                        <div id="preview-container" class="d-none text-center bg-light p-2 rounded mt-2">
                                            <img id="image-preview" src="#" alt="Vista previa" class="preview-principal">
                                        </div>
                                    </div>
                                </div>

                                <!-- Selección de Tipo de Contenido -->
                                <div class="mb-4 p-3 bg-light rounded border">
                                    <label class="form-label fw-bold mb-3">¿Cómo quieres publicar tu contenido?</label>
                                    <div class="d-flex gap-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipo_entrada" id="tipo_texto" value="texto" checked onchange="toggleContentInput()">
                                            <label class="form-check-label" for="tipo_texto">
                                                <i class="bi bi-pencil-square"></i> Escribir artículo
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="tipo_entrada" id="tipo_archivo" value="archivo" onchange="toggleContentInput()">
                                            <label class="form-check-label" for="tipo_archivo">
                                                <i class="bi bi-file-earmark-arrow-up"></i> Subir archivo (PDF, Word, Imagen)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Input para Subir Archivo (Oculto por defecto) -->
                                <div id="archivo-upload-container" class="mb-4 d-none">
                                    <label for="archivo_contenido" class="form-label fw-bold">Subir Archivo de Contenido *</label>
                                    <input type="file" class="form-control" id="archivo_contenido" name="archivo_contenido" 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.webp">
                                    <div class="form-text">
                                        <i class="bi bi-info-circle"></i> Sube tu investigación completa. Formatos permitidos: PDF, Word, o Imágenes. Máx: 10MB.
                                    </div>
                                </div>

                                <!-- Editor de Contenido (Quill) -->
                                <div id="editor-wrapper" class="mb-4">
                                    <label class="form-label fw-bold">Contenido de la Publicación *</label>
                                    <!-- Contenedor del editor -->
                                    <div id="editor-container"></div>
                                    <!-- Input oculto para enviar el contenido HTML -->
                                    <input type="hidden" name="contenido" id="contenido">
                                    <!-- Estado por defecto al enviar: revisión -->
                                    <input type="hidden" name="estado" value="revision">
                                </div>

                                <!-- Botones de acción -->
                                <div class="d-flex justify-content-end gap-3 mt-5">
                                    <a href="index-publicadores.php" class="btn btn-secondary px-4">Cancelar</a>
                                    <button type="submit" class="btn btn-primary px-5" id="btn-publicar">
                                        <i class="bi bi-send me-2"></i>Enviar Para Revision
                                    </button>
                                </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- Fin editor-main -->
                        
                        <!-- Parte derecha: Sidebar de IA -->
                        <?php if ($assistant_enabled): ?>
                        <div class="ai-sidebar" id="aiSidebar" data-aos="fade-left" data-aos-delay="200">
                            <!-- Botón de cerrar (solo visible en móvil) -->
                            <button class="ai-close-btn" id="aiCloseBtn" style="display: none;">
                                <i class="bi bi-x"></i>
                            </button>
                            
                            <h4><i class="bi bi-stars"></i> Asistente IA</h4>
                            <p class="text-muted small mb-4">Potenciado por Gemini 2.5</p>
                            
                            <!-- BOTÓN 1: Generar Resumen -->
                            <div class="ai-action">
                                <button type="button" onclick="generarResumenIA()" class="ai-btn">
                                    <i class="bi bi-file-text"></i> Generar Resumen
                                </button>
                            </div>
                            <!-- Panel de resultado del resumen -->
                            <div id="resumen-ia-resultado"></div>
                            

                            
                            <!-- BOTÓN 3: Formatear Texto (Limpieza) -->
                            <div class="ai-action">
                                <button type="button" onclick="formatearTextoIA()" class="ai-btn">
                                    <i class="bi bi-magic"></i> Formatear Texto (Limpieza)
                                </button>
                            </div>
                            <!-- Panel de resultado del formato -->
                            <div id="formato-ia-resultado"></div>
                            
                            <!-- Info adicional -->
                            <div class="alert alert-info mt-4" style="font-size: 0.85rem;">
                                <i class="bi bi-info-circle"></i>
                                <strong>Tip:</strong> La IA analizará el texto en tu editor.
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- Fin ai-sidebar -->
                        <!-- Fin ai-sidebar -->
                        
                    </div>
                    <!-- Fin editor-ai-container -->
                </div>
            </div>
        </div>
    </main>

    <!-- Botón flotante para abrir asistente IA (solo móvil) -->
    <?php if ($assistant_enabled): ?>
    <button class="ai-toggle-btn" id="aiToggleBtn" title="Abrir Asistente IA">
        <i class="bi bi-stars"></i>
    </button>
    <?php endif; ?>

    <!-- Overlay oscuro (solo móvil) -->
    <div class="ai-overlay" id="aiOverlay"></div>

    <!-- Botón volver arriba -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Scripts Vendor -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    
    <!-- Script del Editor Quill -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Script Principal -->
    <script src="../../assets/js/main.js"></script>
    
    <!-- Script del Asistente de IA (cargar después de Quill) -->
    <script src="../../assets/js/ai-asistente.js"></script>

    <script>
        // Inicializar animaciones AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Configuración del Editor Quill
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        [{ 'align': [] }],
                        ['link', 'image', 'video'],
                        ['clean']
                    ],
                    handlers: {
                        // Manejador personalizado para subida de imágenes
                        'image': imageHandler
                    }
                }
            },
            placeholder: 'Escribe aquí tu artículo científico...'
        });

        // Función para manejar subida de imágenes en el editor
        function imageHandler() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = async () => {
                const file = input.files[0];
                if (file) {
                    // Validar tamaño (máx 5MB)
                    if (file.size > 5 * 1024 * 1024) {
                        alert('La imagen es demasiado grande. Máximo 5MB.');
                        return;
                    }

                    const formData = new FormData();
                    formData.append('image', file);

                    try {
                        // Subir imagen al servidor
                        const response = await fetch('subir_imagen_contenido.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            // Insertar imagen en el editor
                            const range = quill.getSelection();
                            quill.insertEmbed(range.index, 'image', result.url);
                        } else {
                            alert('Error al subir imagen: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error al subir la imagen');
                    }
                }
            };
        }

        // Función para alternar entre escribir texto o subir archivo
        function toggleContentInput() {
            const tipo = document.querySelector('input[name="tipo_entrada"]:checked').value;
            const editorWrapper = document.getElementById('editor-wrapper');
            const archivoContainer = document.getElementById('archivo-upload-container');
            
            if (tipo === 'archivo') {
                editorWrapper.classList.add('d-none');
                archivoContainer.classList.remove('d-none');
                // Requerir archivo, quitar requerimiento de texto (aunque validaremos manualmente)
                document.getElementById('archivo_contenido').required = true;
            } else {
                editorWrapper.classList.remove('d-none');
                archivoContainer.classList.add('d-none');
                document.getElementById('archivo_contenido').required = false;
                document.getElementById('archivo_contenido').value = ''; // Limpiar input
            }
        }

        // Manejo del formulario antes de enviar
        document.getElementById('form-publicacion').onsubmit = function(e) {
            const tipoEntrada = document.querySelector('input[name="tipo_entrada"]:checked').value;
            
            // Si es tipo TEXTO, validamos el editor Quill
            if (tipoEntrada === 'texto') {
                // Obtener contenido HTML del editor
                var contenido = document.querySelector('input[name=contenido]');
                contenido.value = quill.root.innerHTML;
                
                // Validar que no esté vacío (solo etiquetas vacías)
                if (quill.getText().trim().length === 0) {
                    alert('El contenido de la publicación no puede estar vacío');
                    e.preventDefault();
                    return false;
                }
            } else {
                // Si es tipo ARCHIVO, validamos que haya uno seleccionado
                const archivo = document.getElementById('archivo_contenido').files[0];
                if (!archivo) {
                    alert('Debes seleccionar un archivo para subir.');
                    e.preventDefault();
                    return false;
                }
                // Validar tamaño (máx 10MB)
                if (archivo.size > 10 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. Máximo 10MB.');
                    e.preventDefault();
                    return false;
                }
                
                // Limpiar contenido de texto para evitar conflictos si se cambia de opinión
                document.querySelector('input[name=contenido]').value = '';
            }
            
            // Validar longitud del título
            const titulo = document.getElementById('titulo').value;
            if (titulo.length > 150) {
                alert('El título es demasiado largo (máximo 150 caracteres)');
                e.preventDefault();
                return false;
            }

            // Validar longitud del resumen
            const resumen = document.getElementById('resumen').value;
            if (resumen.length > 300) {
                alert('El resumen es demasiado largo (máximo 300 caracteres)');
                e.preventDefault();
                return false;
            }
            
            // Deshabilitar botón para evitar doble envío
            const btn = document.getElementById('btn-publicar');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Publicando...';
        };

        // Contadores de caracteres
        document.getElementById('titulo').addEventListener('input', function() {
            document.getElementById('titulo-count').textContent = this.value.length;
            if(this.value.length > 150) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('resumen').addEventListener('input', function() {
            document.getElementById('resumen-count').textContent = this.value.length;
            if(this.value.length > 300) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });

        // Previsualización de imagen principal
        document.getElementById('imagen_principal').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('image-preview');
            const container = document.getElementById('preview-container');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    container.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
                container.classList.add('d-none');
            }
        });

        // Sidebar Toggle Logic
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarWrapper = document.getElementById('sidebarWrapper');
        const sidebarClose = document.getElementById('sidebarClose');

        if(sidebarToggle && sidebarWrapper) {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            function toggleSidebar() {
                sidebarWrapper.classList.toggle('active');
                overlay.classList.toggle('active');
                document.body.classList.toggle('sidebar-open');
            }

            sidebarToggle.addEventListener('click', toggleSidebar);
            if(sidebarClose) sidebarClose.addEventListener('click', toggleSidebar);
            overlay.addEventListener('click', toggleSidebar);
        }

        // ========================================
        // ASISTENTE DE IA RESPONSIVE
        // ========================================
        const aiToggleBtn = document.getElementById('aiToggleBtn');
        const aiSidebar = document.getElementById('aiSidebar');
        const aiOverlay = document.getElementById('aiOverlay');
        const aiCloseBtn = document.getElementById('aiCloseBtn');

        function toggleAISidebar() {
            aiSidebar.classList.toggle('active');
            aiOverlay.classList.toggle('active');
            document.body.style.overflow = aiSidebar.classList.contains('active') ? 'hidden' : '';
        }

        if (aiToggleBtn) {
            aiToggleBtn.addEventListener('click', toggleAISidebar);
        }

        if (aiCloseBtn) {
            aiCloseBtn.addEventListener('click', toggleAISidebar);
        }

        if (aiOverlay) {
            aiOverlay.addEventListener('click', toggleAISidebar);
        }
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('tour_crear_publicacion_visto')) {
            const driver = window.driver.js.driver;
            
            const driverObj = driver({
                showProgress: true,
                animate: true,
                doneBtnText: '¡Entendido!',
                nextBtnText: 'Siguiente',
                prevBtnText: 'Anterior',
                steps: [
                    { 
                        element: '#titulo', 
                        popover: { 
                            title: '📝 Título Atractivo', 
                            description: 'Comienza con un título claro y conciso que capture la esencia de tu investigación.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '#imagen_principal', 
                        popover: { 
                            title: '🖼️ Imagen de Portada', 
                            description: 'Sube una imagen de alta calidad. Esta será la primera impresión visual de tu publicación.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '#editor-container', 
                        popover: { 
                            title: '✍️ Editor de Contenido Rico', 
                            description: 'Aquí es donde ocurre la magia. Escribe tu artículo completo con formato profesional.', 
                            side: "top", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '.ql-toolbar', 
                        popover: { 
                            title: '🧰 Herramientas de Edición', 
                            description: 'Usa esta barra para dar formato: negritas, listas, enlaces y más.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '.ql-image', 
                        popover: { 
                            title: '📷 Imágenes en el Texto', 
                            description: '¡Importante! Usa este botón para insertar imágenes o gráficos directamente entre tus párrafos.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '#btn-publicar', 
                        popover: { 
                            title: '🚀 Enviar a Revisión', 
                            description: 'Cuando termines, envía tu trabajo. Un administrador lo revisará antes de hacerlo público.', 
                            side: "top", 
                            align: 'start' 
                        } 
                    }
                ]
            });

            // Pequeño retraso para asegurar que Quill renderizó
            setTimeout(() => {
                driverObj.drive();
                localStorage.setItem('tour_crear_publicacion_visto', 'true');
            }, 1000);
        }
    });
    </script>
</body>
</html>
