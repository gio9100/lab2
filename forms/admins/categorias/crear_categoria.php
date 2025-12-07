<?php
// Crear Categoría (Admin)
// Permite a los administradores crear nuevas categorías para las publicaciones

// Iniciar sesión
session_start();

// Incluir configuración y funciones de admin
require_once '../config-admin.php';
requerirAdmin();

// Obtener datos del admin actual
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];
$stats = obtenerEstadisticasAdmin($conn);

// Incluir clases de categorías
include_once 'config-categorias.php';
include_once 'categoria.php';

// Inicializar conexión y objeto categoría
$database = new Database();
$db = $database->getConnection();
$categoria = new Categoria($db);

$mensaje = "";
$exito = false;

// Procesar formulario POST
if ($_POST) {
    // Asignar valores del formulario al objeto
    $categoria->nombre = $_POST['nombre'];
    $categoria->descripcion = $_POST['descripcion'];
    $categoria->color = $_POST['color'];
    $categoria->icono = $_POST['icono'];
    $categoria->estado = $_POST['estado'];
    
    // Intentar crear la categoría
    if ($categoria->crear()) {
        $mensaje = 'Categoría creada exitosamente';
        $exito = true;
    } else {
        $mensaje = 'Error al crear la categoría';
        $exito = false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Categoría - Lab-Explora</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Principal -->
    <link href="../../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css-admins/admin.css">
    
    <style>
        .color-preview {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            border: 2px solid #ddd;
            display: inline-block;
            vertical-align: middle;
            margin-left: 10px;
        }
        .icon-preview {
            font-size: 2rem;
            margin-left: 10px;
        }
    </style>
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../../index.php" class="logo d-flex align-items-end">
                    <img src="../../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="../logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Contenido Principal -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                
                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="../gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            <a href="../gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Ver Categorías
                            </a>
                            <a href="crear_categoria.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-plus-circle me-2"></i>Crear Categoría
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="../admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Resumen rápido -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Resumen del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <small class="text-muted">Usuarios: <?= $stats['total_usuarios'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Publicadores: <?= $stats['total_publicadores'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Publicaciones: <?= $stats['total_publicaciones'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Pendientes: <?= $stats['publicadores_pendientes'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido Derecho -->
                <div class="col-md-9">
                    
                    <!-- Mensajes de Alerta -->
                    <?php if($mensaje): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn" onclick="this.parentElement.style.display='none'">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Crear Nueva Categoría</h2>
                        <p>Agrega una nueva categoría para organizar las publicaciones del laboratorio</p>
                    </div>
                    
                    <!-- Formulario de Creación -->
                    <div class="card" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-body">
                            <form method="POST" id="formCategoria">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required 
                                               placeholder="Ej: Hematología, Parasitología...">
                                        <small class="text-muted">El slug se generará automáticamente</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="estado" class="form-label">Estado</label>
                                        <select class="form-select" id="estado" name="estado">
                                            <option value="activo">Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                              placeholder="Descripción breve de la categoría..."></textarea>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="color" class="form-label">Color de la Categoría</label>
                                        <div class="d-flex align-items-center">
                                            <input type="color" class="form-control form-control-color" id="color" name="color" value="#007bff">
                                            <span class="color-preview ms-2" id="colorPreview" style="background-color: #007bff;"></span>
                                        </div>
                                        <small class="text-muted">Este color se usará para identificar la categoría</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="icono" class="form-label">Icono (Bootstrap Icons)</label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" class="form-control" id="icono" name="icono" 
                                                   placeholder="bi-flask" value="bi-flask">
                                            <i class="bi bi-flask icon-preview" id="iconPreview"></i>
                                        </div>
                                        <small class="text-muted">Usa nombres de <a href="https://icons.getbootstrap.com/" target="_blank">Bootstrap Icons</a></small>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <a href="listar_categorias.php" class="btn btn-secondary me-md-2">
                                        <i class="bi bi-arrow-left me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Crear Categoría
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Guía de Iconos -->
                    <div class="card mt-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="bi bi-palette me-2"></i>Iconos Sugeridos para Categorías de Laboratorio</h5>
                        </div>
                        <div class="card-body">
                            
                            <h6 class="text-danger mb-3"><i class="bi bi-droplet-fill me-2"></i>Hematología</h6>
                            <div class="row text-center mb-4">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-droplet-fill" style="font-size: 2.5rem; color: #dc3545;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-droplet-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-droplet-half" style="font-size: 2.5rem; color: #e74c3c;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-droplet-half</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-heart-pulse-fill" style="font-size: 2.5rem; color: #c0392b;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-heart-pulse-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-heart-fill" style="font-size: 2.5rem; color: #e83e8c;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-heart-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-activity" style="font-size: 2.5rem; color: #d63031;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-activity</code></p>
                                </div>
                            </div>

                            <h6 class="text-success mb-3"><i class="bi bi-virus me-2"></i>Microbiología & Bacteriología</h6>
                            <div class="row text-center mb-4">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-virus" style="font-size: 2.5rem; color: #28a745;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-virus</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-virus2" style="font-size: 2.5rem; color: #27ae60;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-virus2</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-bug-fill" style="font-size: 2.5rem; color: #2ecc71;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-bug-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-bug" style="font-size: 2.5rem; color: #16a085;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-bug</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-moisture" style="font-size: 2.5rem; color: #1abc9c;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-moisture</code></p>
                                </div>
                            </div>

                            <h6 class="text-info mb-3"><i class="bi bi-flask me-2"></i>Química Clínica & Bioquímica</h6>
                            <div class="row text-center mb-4">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-flask" style="font-size: 2.5rem; color: #007bff;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-flask</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-eyedropper" style="font-size: 2.5rem; color: #3498db;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-eyedropper</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-thermometer-half" style="font-size: 2.5rem; color: #2980b9;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-thermometer-half</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-droplet" style="font-size: 2.5rem; color: #17a2b8;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-droplet</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-water" style="font-size: 2.5rem; color: #5dade2;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-water</code></p>
                                </div>
                            </div>

                            <h6 class="text-warning mb-3"><i class="bi bi-shield-fill-check me-2"></i>Inmunología & Serología</h6>
                            <div class="row text-center mb-4">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-shield-fill-check" style="font-size: 2.5rem; color: #f39c12;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-shield-fill-check</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-shield-check" style="font-size: 2.5rem; color: #e67e22;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-shield-check</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-shield-plus" style="font-size: 2.5rem; color: #d68910;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-shield-plus</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-capsule" style="font-size: 2.5rem; color: #ffc107;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-capsule</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-bandaid-fill" style="font-size: 2.5rem; color: #f8b739;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-bandaid-fill</code></p>
                                </div>
                            </div>

                            <h6 class="text-dark mb-3"><i class="bi bi-clipboard2-pulse me-2"></i>Patología & Anatomía</h6>
                            <div class="row text-center mb-4">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-clipboard2-pulse-fill" style="font-size: 2.5rem; color: #34495e;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-clipboard2-pulse-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-clipboard2-pulse" style="font-size: 2.5rem; color: #2c3e50;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-clipboard2-pulse</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-file-medical-fill" style="font-size: 2.5rem; color: #1c2833;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-file-medical-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-file-earmark-medical" style="font-size: 2.5rem; color: #566573;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-file-earmark-medical</code></p>
                                </div>
                            </div>

                            <h6 class="text-secondary mb-3"><i class="bi bi-star me-2"></i>Iconos Generales</h6>
                            <div class="row text-center">
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-clipboard-check" style="font-size: 2.5rem; color: #17a2b8;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-clipboard-check</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-clipboard-data-fill" style="font-size: 2.5rem; color: #007bff;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-clipboard-data-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-graph-up" style="font-size: 2.5rem; color: #28a745;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-graph-up</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-bar-chart-fill" style="font-size: 2.5rem; color: #ffc107;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-bar-chart-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-diagram-3-fill" style="font-size: 2.5rem; color: #9b59b6;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-diagram-3-fill</code></p>
                                </div>
                                <div class="col-md-2 col-4 mb-3">
                                    <i class="bi bi-bookmark-star-fill" style="font-size: 2.5rem; color: #e83e8c;"></i>
                                    <p class="small mt-2 mb-0"><code>bi-bookmark-star-fill</code></p>
                                </div>
                            </div>

                            <div class="alert alert-info mt-4 mb-0">
                                <i class="bi bi-info-circle me-2"></i><strong>Tip:</strong> Copia el nombre del icono (sin las comillas) y pégalo en el campo "Icono" del formulario.
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/vendor/aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Previsualización de color
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('colorPreview').style.backgroundColor = this.value;
        });

        // Previsualización de icono
        document.getElementById('icono').addEventListener('input', function() {
            const iconPreview = document.getElementById('iconPreview');
            iconPreview.className = 'bi ' + this.value + ' icon-preview';
        });
    </script>
</body>
</html>