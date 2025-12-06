<?php
session_start();
require_once __DIR__ . '/config-publicadores.php';

// Verificar sesión
if (!isset($_SESSION['publicador_id'])) {
    header('Location: login.php');
    exit();
}

$publicador_id = $_SESSION['publicador_id'];

// Obtener ID de la publicación a editar
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mis-publicaciones.php');
    exit();
}

$publicacion_id = intval($_GET['id']);

// Verificar que la publicación pertenece al publicador
$query = "SELECT p.*, c.id as categoria_id 
          FROM publicaciones p 
          LEFT JOIN categorias c ON p.categoria_id = c.id 
          WHERE p.id = ? AND p.publicador_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $publicacion_id, $publicador_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: mis-publicaciones.php');
    exit();
}

$publicacion = $result->fetch_assoc();
$stmt->close();

// Obtener categorías
$categorias = obtenerCategorias($conn);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - Lab-Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
<body>
    <!-- Header -->
    <header class="header" style="background: #ffffff; padding: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.08); border-bottom: 1px solid #e9ecef;">
        <div class="container-fluid" style="max-width: 1400px; margin: 0 auto; padding: 0 20px;">
            <div class="top-row" style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 10px;">
                <!-- Logo -->
                <a href="../../index.php" class="logo" style="display: flex; align-items: center; text-decoration: none; gap: 10px;">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab" style="max-height: 40px;">
                    <span class="sitename" style="font-family: 'Nunito', sans-serif; font-size: 28px; font-weight: 600; color: #7390A0; margin: 0;">Lab-Explorer</span>
                </a>

                <!-- Social Links & User Actions -->
                <div class="social-links" style="display: flex; align-items: center; gap: 15px;">
                    <a href="#" title="Facebook" style="color: #6c757d; font-size: 16px; transition: all 0.3s ease; text-decoration: none;"><i class="bi bi-facebook"></i></a>
                    <a href="#" title="Twitter" style="color: #6c757d; font-size: 16px; transition: all 0.3s ease; text-decoration: none;"><i class="bi bi-twitter"></i></a>
                    <a href="#" title="Instagram" style="color: #6c757d; font-size: 16px; transition: all 0.3s ease; text-decoration: none;"><i class="bi bi-instagram"></i></a>
                    
                    <?php if (isset($_SESSION['publicador_id'])): ?>
                        <span class="saludo" style="font-weight: 500; color: #212529;">Hola, <?= htmlspecialchars($_SESSION['publicador_nombre'] ?? 'Publicador') ?></span>
                        <a href="index-publicadores.php" style="color: #6c757d; text-decoration: none; transition: all 0.3s ease;">
                            <i class="bi bi-house-door"></i>
                            Panel
                        </a>
                        <a href="../logout.php" style="color: #6c757d; text-decoration: none; transition: all 0.3s ease;">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar Sesión
                        </a>
                    <?php else: ?>
                        <a href="inicio-sesion-publicadores.php" style="color: #6c757d; text-decoration: none; transition: all 0.3s ease;">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Inicia sesión
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">
                    <i class="fas fa-edit"></i> Editar Publicación
                </h2>
                
                <div class="alert alert-info">
                    <small>Editando: <strong><?= htmlspecialchars($publicacion['titulo']) ?></strong></small>
                </div>
                
                <form method="POST" action="actualizar_publicacion.php" enctype="multipart/form-data" id="formPublicacion">
                    <input type="hidden" name="publicacion_id" value="<?= $publicacion_id ?>">
                    <input type="hidden" name="publicador_id" value="<?php echo $publicador_id; ?>">
                    
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="titulo" class="form-label fw-bold">Título *</label>
                                <input type="text" class="form-control form-control-lg" id="titulo" name="titulo" 
                                       placeholder="Título de la publicación" 
                                       value="<?= htmlspecialchars($publicacion['titulo']) ?>" required>
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
                                                    $selected = ($categoria['id'] == $publicacion['categoria_id']) ? 'selected' : '';
                                                    echo '<option value="' . $categoria['id'] . '" ' . $selected . '>';
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
                                            <option value="articulo" <?= $publicacion['tipo'] == 'articulo' ? 'selected' : '' ?>>Artículo</option>
                                            <option value="caso_clinico" <?= $publicacion['tipo'] == 'caso_clinico' ? 'selected' : '' ?>>Caso Clínico</option>
                                            <option value="estudio" <?= $publicacion['tipo'] == 'estudio' ? 'selected' : '' ?>>Estudio</option>
                                            <option value="revision" <?= $publicacion['tipo'] == 'revision' ? 'selected' : '' ?>>Revisión</option>
                                            <option value="noticia" <?= $publicacion['tipo'] == 'noticia' ? 'selected' : '' ?>>Noticia</option>
                                            <option value="guia" <?= $publicacion['tipo'] == 'guia' ? 'selected' : '' ?>>Guía</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="imagen_principal" class="form-label fw-bold">
                                    <i class="fas fa-image"></i> Imagen Principal
                                </label>
                                <?php if (!empty($publicacion['imagen_principal'])): ?>
                                    <div class="mb-2">
                                        <img src="../../uploads/<?= htmlspecialchars($publicacion['imagen_principal']) ?>" 
                                             class="preview-principal" alt="Imagen actual">
                                        <p class="text-muted small mt-1">Imagen actual. Sube una nueva si deseas cambiarla.</p>
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="imagen_principal" name="imagen_principal" 
                                       accept="image/*" onchange="previewImagenPrincipal(this)">
                                <div id="preview_principal" class="mt-2"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="resumen" class="form-label fw-bold">Resumen</label>
                                <textarea class="form-control" id="resumen" name="resumen" rows="3" 
                                          placeholder="Breve descripción de la publicación" 
                                          maxlength="300"><?= htmlspecialchars($publicacion['resumen'] ?? '') ?></textarea>
                                <div class="character-count mt-1">
                                    <span id="resumen-count">0</span>/300 caracteres
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="meta_descripcion" class="form-label fw-bold">Meta Descripción (SEO)</label>
                                <textarea class="form-control" id="meta_descripcion" name="meta_descripcion" rows="2" 
                                          placeholder="Descripción para motores de búsqueda (máx 160 caracteres)"
                                          maxlength="160"><?= htmlspecialchars($publicacion['meta_descripcion'] ?? '') ?></textarea>
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
                                <textarea class="d-none" id="contenido" name="contenido"><?= htmlspecialchars($publicacion['contenido']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> Etiquetas y Estado</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="tags" class="form-label fw-bold">Etiquetas</label>
                                <input type="text" class="form-control" id="tags" name="tags" 
                                       placeholder="etiqueta1, etiqueta2, etiqueta3"
                                       value="<?= htmlspecialchars($publicacion['tags'] ?? '') ?>">
                                <div class="form-text">Separa las etiquetas con comas</div>
                            </div>

                            <div class="mb-3">
                                <label for="estado" class="form-label fw-bold">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="borrador" <?= $publicacion['estado'] == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                    <option value="publicado" <?= $publicacion['estado'] == 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                    <option value="revision" <?= $publicacion['estado'] == 'revision' ? 'selected' : '' ?>>En Revisión</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="fecha_publicacion" class="form-label fw-bold">Fecha de Publicación</label>
                                <input type="datetime-local" class="form-control" id="fecha_publicacion" name="fecha_publicacion"
                                       value="<?= !empty($publicacion['fecha_publicacion']) ? date('Y-m-d\TH:i', strtotime($publicacion['fecha_publicacion'])) : '' ?>">
                                <div class="form-text">Dejar vacío para publicar inmediatamente</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="mis-publicaciones.php" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quill Editor JS -->
    <!-- Quill JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <!-- Image Resize Module -->
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    
    <script>
        // ========================================
        // INICIALIZAR QUILL EDITOR CON COLORES
        // ========================================
        // Registramos el módulo de Image Resize
        
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

        // Cargar el contenido existente en el editor
        const contenidoExistente = document.getElementById('contenido').value;
        if (contenidoExistente) {
            quill.root.innerHTML = contenidoExistente;
        }

        // Sincronizar el contenido del editor con el textarea oculto antes de enviar
        document.getElementById('formPublicacion').addEventListener('submit', function(e) {
            console.log('🔵 Formulario enviándose...');
            
            const contenidoHTML = quill.root.innerHTML;
            const contenidoTexto = quill.getText().trim();
            
            console.log('📝 Contenido HTML:', contenidoHTML);
            console.log('📝 Contenido texto:', contenidoTexto);
            
            // Validar que haya contenido real (no solo <p><br></p>)
            if (contenidoTexto.length === 0 || contenidoHTML === '<p><br></p>') {
                e.preventDefault();
                alert('⚠️ Por favor escribe el contenido de la publicación');
                console.log('❌ Formulario detenido - sin contenido');
                return false;
            }
            
            // Guardar el HTML en el campo oculto
            document.getElementById('contenido').value = contenidoHTML;
            
            console.log('✅ Contenido guardado en campo oculto');
            console.log('✅ Formulario se enviará normalmente');
        });

        // Preview de imagen principal
        function previewImagenPrincipal(input) {
            const preview = document.getElementById('preview_principal');
            preview.innerHTML = '';
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="preview-principal" alt="Preview"><p class="text-muted small mt-1">Nueva imagen seleccionada</p>`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Contador de caracteres para el resumen
        document.getElementById('resumen').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('resumen-count').textContent = count;
        });

        // Contador de caracteres para meta descripción
        document.getElementById('meta_descripcion').addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('meta-count').textContent = count;
        });

        // Inicializar contadores
        document.getElementById('resumen-count').textContent = 
            document.getElementById('resumen').value.length;
        document.getElementById('meta-count').textContent = 
            document.getElementById('meta_descripcion').value.length;
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
