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
    
    <!-- Estilos del Editor Quill -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
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
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- Saludo al publicador -->
                        <span class="saludo">🧪 Publicador: <?= htmlspecialchars($publicador_nombre) ?></span>
                        <a href="logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Barra lateral de navegación -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="index-publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="crear_nueva_publicacion.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-plus-circle me-2"></i>Nueva Publicación
                            </a>
                            <a href="mis-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Mis Publicaciones
                            </a>
                            <a href="perfil.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person me-2"></i>Mi Perfil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contenido principal -->
                <div class="col-md-9">
                    <div class="section-title" data-aos="fade-up">
                        <h2>Crear Nueva Publicación</h2>
                        <p class="text-muted">Comparte tu conocimiento con la comunidad científica</p>
                    </div>

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

                                <!-- Editor de Contenido (Quill) -->
                                <div class="mb-4">
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
            </div>
        </div>
    </main>

    <!-- Botón volver arriba -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Scripts Vendor -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    
    <!-- Script del Editor Quill -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

    <!-- Script Principal -->
    <script src="../../assets/js/main.js"></script>

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

        // Manejo del formulario antes de enviar
        document.getElementById('form-publicacion').onsubmit = function(e) {
            // Obtener contenido HTML del editor
            var contenido = document.querySelector('input[name=contenido]');
            contenido.value = quill.root.innerHTML;
            
            // Validar que no esté vacío (solo etiquetas vacías)
            if (quill.getText().trim().length === 0) {
                alert('El contenido de la publicación no puede estar vacío');
                e.preventDefault();
                return false;
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
            } else {
                container.classList.add('d-none');
            }
        });
    </script>
</body>
</html>
