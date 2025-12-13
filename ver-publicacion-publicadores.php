<?php
session_start();
require_once './forms/conexion.php';

// SEGURIDAD: Solo publicadores autenticados
if (!isset($_SESSION['publicador_id'])) {
    header('Location: forms/publicadores/inicio-sesion-publicadores.php');
    exit();
}

// Verificar que se envió un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: forms/publicadores/index-publicadores.php');
    exit();
}

// Función para procesar contenido (decodificar HTML entities)
function procesarContenido($contenido) {
    return html_entity_decode($contenido);
}

$publicacion_id = intval($_GET['id']);
$publicador_id = $_SESSION['publicador_id'];

// Consulta: traer la publicación SIN filtrar por estado, pero verificando que sea del publicador
$query = "SELECT p.*, c.nombre as categoria_nombre, pub.nombre as publicador_nombre, pub.especialidad " .
          "FROM publicaciones p " .
          "LEFT JOIN categorias c ON p.categoria_id = c.id " .
          "LEFT JOIN publicadores pub ON p.publicador_id = pub.id " .
          "WHERE p.id = ? AND p.publicador_id = ?";

$stmt = $conexion->prepare($query);
$stmt->bind_param("ii", $publicacion_id, $publicador_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No encontrada o no es del publicador
    header('Location: forms/publicadores/index-publicadores.php');
    exit();
}

$publicacion = $result->fetch_assoc();

// Determinar color y texto del estado
$estado_colores = [
    'borrador' => ['bg' => '#6c757d', 'text' => 'BORRADOR'],
    'revision' => ['bg' => '#ffc107', 'text' => 'EN REVISIÓN'],
    'rechazada' => ['bg' => '#dc3545', 'text' => 'RECHAZADA'],
    'publicado' => ['bg' => '#28a745', 'text' => 'PUBLICADA']
];

$estado_actual = $publicacion['estado'] ?? 'borrador';
$estado_info = $estado_colores[$estado_actual] ?? ['bg' => '#6c757d', 'text' => strtoupper($estado_actual)];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($publicacion['titulo']) ?> - Lab Explorer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #7390A0;
            --secondary: #5a7080;
            --background: #f4f6f8;
            --text: #212529;
            --text-light: #6c757d;
            --white: #ffffff;
            --border: #dee2e6;
            --shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }
        
        .header {
            background: var(--white);
            padding: 20px 0;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--primary);
        }
        
        .logo img {
            width: 40px;
            height: 40px;
        }
        
        .sitename {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .estado-banner {
            background: <?= $estado_info['bg'] ?>;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: var(--white);
            padding: 60px 20px;
            text-align: center;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-description {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .category-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .main-content {
            max-width: 1200px;
            margin: -40px auto 40px;
            padding: 0 20px;
        }
        
        .content-section {
            background: var(--white);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }
        
        .meta-info {
            background: var(--background);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .meta-item i {
            font-size: 1.3rem;
            color: var(--primary);
        }
        
        .meta-label {
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        .meta-value {
            font-weight: 600;
            color: var(--text);
        }
        
        .publication-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text);
        }
        
        .publication-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 25px auto;
            display: block;
            cursor: pointer;
        }
        
        .btn-edit {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: var(--secondary);
            transform: translateY(-2px);
        }
        
        .mensaje-rechazo {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
            justify-content: center;
            align-items: center;
        }
        
        .lightbox.active {
            display: flex;
        }
        
        .lightbox-content {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
            border-radius: 8px;
        }
        
        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 40px;
            font-size: 40px;
            color: white;
            cursor: pointer;
            background: rgba(0,0,0,0.5);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <a href="forms/publicadores/index-publicadores.php" class="logo">
                <img src="assets/img/logo/logobrayan2.ico" alt="Lab Explorer">
                <h1 class="sitename">Lab-Explora</h1>
            </a>
        </div>
    </header>

    <div class="container">
        <div class="estado-banner">
            <i class="bi bi-info-circle-fill"></i> ESTADO: <?= $estado_info['text'] ?>
        </div>
    </div>

    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title"><?= htmlspecialchars($publicacion['titulo']) ?></h1>
            <?php if(!empty($publicacion['resumen'])): ?>
            <p class="hero-description"><?= htmlspecialchars($publicacion['resumen']) ?></p>
            <?php endif; ?>
            <span class="category-badge">
                <i class="bi bi-folder-fill"></i> <?= htmlspecialchars($publicacion['categoria_nombre'] ?? 'General') ?>
            </span>
            <span class="category-badge" style="margin-left: 10px;">
                <i class="bi bi-file-text"></i> <?= htmlspecialchars(ucfirst($publicacion['tipo'] ?? 'Artículo')) ?>
            </span>
        </div>
    </section>

    <main class="main-content">
        <div class="meta-info">
            <div class="meta-item">
                <i class="bi bi-person-circle"></i>
                <div>
                    <div class="meta-label">Autor</div>
                    <div class="meta-value"><?= htmlspecialchars($publicacion['publicador_nombre']) ?></div>
                </div>
            </div>
            
            <div class="meta-item">
                <i class="bi bi-calendar-event"></i>
                <div>
                    <div class="meta-label">Fecha de creación</div>
                    <div class="meta-value"><?= date('d/m/Y', strtotime($publicacion['fecha_creacion'])) ?></div>
                </div>
            </div>
            
            <div class="meta-item">
                <i class="bi bi-file-earmark-text"></i>
                <div>
                    <div class="meta-label">Tipo de contenido</div>
                    <div class="meta-value"><?= htmlspecialchars(ucfirst($publicacion['tipo'] ?? 'Artículo')) ?></div>
                </div>
            </div>
        </div>

        <?php if ($estado_actual === 'rechazada' && !empty($publicacion['mensaje_rechazo'])): ?>
        <div class="mensaje-rechazo">
            <h4><i class="bi bi-exclamation-triangle-fill"></i> Motivo del rechazo:</h4>
            <p><?= htmlspecialchars($publicacion['mensaje_rechazo']) ?></p>
        </div>
        <?php endif; ?>

        <?php if (in_array($estado_actual, ['borrador', 'rechazada'])): ?>
        <div style="text-align: center; margin-bottom: 30px;">
            <a href="forms/publicadores/editar-publicacion.php?id=<?= $publicacion_id ?>" class="btn-edit">
                <i class="bi bi-pencil-square"></i> Editar Publicación
            </a>
        </div>
        <?php endif; ?>

        <section class="content-section">
            <article class="publication-content">
                <?php if (!empty($publicacion['archivo_url'])): ?>
                    <!-- Lógica para mostrar archivo adjunto -->
                    <div class="file-viewer-container" style="background: #f8f9fa; border-radius: 12px; overflow: hidden; border: 1px solid #dee2e6;">
                        
                        <?php 
                        // Determinar tipo de archivo si no está en BD (fallback)
                        $tipo = strtolower($publicacion['tipo_archivo'] ?? '');
                        if (empty($tipo) && !empty($publicacion['archivo_url'])) {
                            $ext = pathinfo($publicacion['archivo_url'], PATHINFO_EXTENSION);
                            $tipo = $ext;
                        }
                        
                        $archivo_path = 'uploads/' . htmlspecialchars($publicacion['archivo_url']);
                        ?>

                        <?php if ($tipo === 'pdf' || $tipo === 'application/pdf'): ?>
                            <!-- Visor PDF Nativo -->
                            <div style="position: relative; height: 800px; width: 100%;">
                                <iframe src="<?= $archivo_path ?>" style="width: 100%; height: 100%; border: none;">
                                    Tu navegador no soporta visualización de PDF. 
                                    <a href="<?= $archivo_path ?>">Descárgalo aquí</a>.
                                </iframe>
                            </div>

                        <?php elseif (in_array($tipo, ['jpg', 'jpeg', 'png', 'webp', 'imagen_contenido'])): ?>
                            <!-- Visor Imagen -->
                            <div class="text-center p-3">
                                <img src="<?= $archivo_path ?>" class="img-fluid rounded shadow-sm" alt="Contenido" style="max-height: 800px;">
                            </div>

                        <?php elseif ($tipo === 'docx' || $tipo === 'doc'): ?>
                            <!-- Visor DOCX con Mammoth.js -->
                            <div class="p-4" style="background: white; min-height: 500px;">
                                <div id="word-container" style="color: black;">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando documento...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Renderizando documento...</p>
                                    </div>
                                </div>
                                <!-- Fallback download link -->
                                <div class="text-center mt-4 pt-3 border-top">
                                    <small class="text-muted">¿No se ve bien?</small><br>
                                    <a href="<?= $archivo_path ?>" class="btn btn-sm btn-outline-primary mt-1" download>
                                        <i class="bi bi-download"></i> Descargar archivo original
                                    </a>
                                </div>
                            </div>

                            <!-- Cargar librería Mammoth.js -->
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const archivoUrl = "<?= $archivo_path ?>";
                                    
                                    fetch(archivoUrl)
                                        .then(response => response.arrayBuffer())
                                        .then(arrayBuffer => mammoth.convertToHtml({arrayBuffer: arrayBuffer}))
                                        .then(result => {
                                            document.getElementById('word-container').innerHTML = result.value;
                                            // Aplicar estilos básicos al contenido generado
                                            const container = document.getElementById('word-container');
                                            container.querySelectorAll('p').forEach(p => p.style.marginBottom = '1rem');
                                            container.querySelectorAll('table').forEach(t => t.classList.add('table', 'table-bordered'));
                                        })
                                        .catch(err => {
                                            console.error(err);
                                            document.getElementById('word-container').innerHTML = `
                                                <div class="alert alert-warning">
                                                    No se pudo visualizar el documento automáticamente. 
                                                    <br>
                                                    <a href="${archivoUrl}" class="alert-link">Descárgalo aquí</a>
                                                </div>
                                            `;
                                        });
                                });
                            </script>

                        <?php else: ?>
                            <!-- Tarjeta de Descarga para otros archivos (Word, Excel, etc.) -->
                            <div class="text-center p-5">
                                <i class="bi bi-file-earmark-richtext text-primary" style="font-size: 5rem;"></i>
                                <h3 class="mt-4 text-dark">Documento Adjunto</h3>
                                <p class="text-muted mb-4">
                                    Este contenido está disponible en formato <strong><?= strtoupper($tipo) ?></strong>.
                                    <br>Puedes descargarlo para visualizarlo.
                                </p>
                                <a href="<?= $archivo_path ?>" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm" download>
                                    <i class="bi bi-download me-2"></i> Descargar Documento
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Si hay contenido de texto adicional, mostrarlo abajo -->
                    <?php if (!empty($publicacion['contenido']) && trim($publicacion['contenido']) !== '<p><br></p>'): ?>
                        <div class="mt-5 pt-4 border-top">
                            <h4 class="mb-3 text-secondary">Notas Adicionales</h4>
                            <?= procesarContenido($publicacion['contenido']) ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- Contenido Texto Normal -->
                    <?= procesarContenido($publicacion['contenido']) ?>
                <?php endif; ?>
            </article>
        </section>
    </main>

    <div class="lightbox" id="lightbox" onclick="cerrarLightbox()">
        <span class="lightbox-close">&times;</span>
        <img class="lightbox-content" id="lightbox-img" src="">
    </div>

    <script>
        function abrirLightbox(src) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImg = document.getElementById('lightbox-img');
            lightboxImg.src = src;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function cerrarLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const imagenes = document.querySelectorAll('.publication-content img');
            imagenes.forEach(img => {
                img.style.cursor = 'pointer';
                img.addEventListener('click', function() {
                    abrirLightbox(this.src);
                });
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                cerrarLightbox();
            }
        });
    </script>
</body>
</html>
