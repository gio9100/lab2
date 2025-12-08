<?php
// Gestionar Reportes (Admin)
// Permite a los administradores revisar y procesar reportes de contenido inapropiado

// Iniciar sesión
session_start();

// Incluir configuración y funciones de admin
require_once "config-admin.php";

// Verificar permisos de administrador
requerirAdmin();

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Procesar un solo reporte
    if (isset($_POST['procesar_reporte'])) {
        $reporte_id = intval($_POST['reporte_id']);
        $accion = $_POST['accion']; // 'aprobar' o 'rechazar'
        
        // Llamar a la función de procesamiento
        if (procesarReporte($reporte_id, $accion, $admin_id, $conn)) {
            $mensaje = $accion === 'aprobar' ? 'Reporte aprobado y contenido eliminado' : 'Reporte descartado';
            $exito = true;
        } else {
            $mensaje = 'Error al procesar el reporte';
            $exito = false;
        }
    }
    
    // Procesar acción masiva (todos los pendientes)
    if (isset($_POST['accion_masiva'])) {
        $accion = $_POST['accion_masiva']; // 'aprobar_todos' o 'rechazar_todos'
        
        // Obtener todos los reportes pendientes
        $query = "SELECT id FROM reportes WHERE estado = 'pendiente'";
        $result = $conn->query($query);
        
        $procesados = 0;
        $errores = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                // Determinar acción individual
                $accion_individual = ($accion === 'aprobar_todos') ? 'aprobar' : 'rechazar';
                
                // Procesar cada reporte
                if (procesarReporte($row['id'], $accion_individual, $admin_id, $conn)) {
                    $procesados++;
                } else {
                    $errores++;
                }
            }
            
            // Generar mensaje de resultado
            if ($accion === 'aprobar_todos') {
                $mensaje = "Se aprobaron {$procesados} reportes y se eliminó el contenido reportado.";
            } else {
                $mensaje = "Se rechazaron {$procesados} reportes.";
            }
            
            if ($errores > 0) {
                $mensaje .= " ({$errores} errores)";
            }
            
            $exito = ($errores === 0);
        } else {
            $mensaje = 'No hay reportes pendientes para procesar.';
            $exito = false;
        }
    }
}

// Obtener filtros de la URL
$filtro_tipo = $_GET['tipo'] ?? null;
$filtro_estado = $_GET['estado'] ?? null;

// Obtener reportes y estadísticas
$reportes = obtenerTodosReportes($filtro_tipo, $filtro_estado, $conn);
$stats = obtenerEstadisticasReportes($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Reportes - Lab-Explora</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
    
    <!-- CSS Principal -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    <!-- LIBRERÍA para generar PDF (Reportes) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                  <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</a>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
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
                <!-- Sidebar -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php include 'sidebar-admin.php'; ?>
                </div>

                <!-- Contenido Derecho -->
                <div class="col-md-9">
                    
                    <!-- Mensajes de Alerta -->
                    <?php if(isset($mensaje)): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>📋 Gestión de Reportes</h2>
                        <p>Administra los reportes de contenido inapropiado</p>
                    </div>
                    
                    <!-- Tarjetas de Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total'] ?></h4>
                                    <small>Total Reportes</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['pendientes'] ?></h4>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['resueltos'] ?></h4>
                                    <small>Resueltos</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card secondary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['descartados'] ?></h4>
                                    <small>Descartados</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción Masiva -->
                    <?php if($stats['pendientes'] > 0): ?>
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-lightning me-2"></i>Acciones Masivas</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Hay <strong><?= $stats['pendientes'] ?></strong> reportes pendientes</p>
                            <div class="d-flex gap-2">
                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de aprobar TODOS los reportes pendientes? Esto eliminará todo el contenido reportado.')">
                                    <input type="hidden" name="accion_masiva" value="aprobar_todos">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-circle"></i> Aprobar Todos
                                    </button>
                                </form>
                                <form method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de rechazar TODOS los reportes pendientes?')">
                                    <input type="hidden" name="accion_masiva" value="rechazar_todos">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-circle"></i> Rechazar Todos
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Filtros -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filtros</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Tipo</label>
                                    <select name="tipo" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="publicacion" <?= $filtro_tipo === 'publicacion' ? 'selected' : '' ?>>Publicaciones</option>
                                        <option value="comentario" <?= $filtro_tipo === 'comentario' ? 'selected' : '' ?>>Comentarios</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select name="estado" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                        <option value="resuelto" <?= $filtro_estado === 'resuelto' ? 'selected' : '' ?>>Resuelto</option>
                                        <option value="ignorado" <?= $filtro_estado === 'ignorado' ? 'selected' : '' ?>>Ignorado</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bi bi-search"></i> Filtrar
                                    </button>
                                    <a href="gestionar-reportes.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Limpiar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Lista de Reportes -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-flag me-2"></i>Reportes</h5>
                            <span class="badge bg-primary"><?= count($reportes) ?> resultados</span>
                        </div>
                        <div class="card-body p-0">
                            <?php if(empty($reportes)): ?>
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>No se encontraron reportes</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tipo</th>
                                                <th>Motivo</th>
                                                <th>Reportado por</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($reportes as $reporte): ?>
                                            <tr>
                                                <td><?= $reporte['id'] ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $reporte['tipo'] === 'publicacion' ? 'primary' : 'info' ?>">
                                                        <?= ucfirst($reporte['tipo']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($reporte['motivo']) ?></td>
                                                <td><?= htmlspecialchars($reporte['usuario_nombre'] ?? 'Usuario eliminado') ?></td>
                                                <td><?= date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])) ?></td>
                                                <td>
                                                    <span class="status-badge <?= $reporte['estado'] ?>">
                                                        <?= ucfirst($reporte['estado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($reporte['estado'] === 'pendiente'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="reporte_id" value="<?= $reporte['id'] ?>">
                                                            <input type="hidden" name="procesar_reporte" value="1">
                                                            <input type="hidden" name="accion" value="aprobar">
                                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('¿Aprobar este reporte y eliminar el contenido?')">
                                                                <i class="bi bi-check-circle"></i>
                                                            </button>
                                                        </form>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="reporte_id" value="<?= $reporte['id'] ?>">
                                                            <input type="hidden" name="procesar_reporte" value="1">
                                                            <input type="hidden" name="accion" value="rechazar">
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Rechazar este reporte?')">
                                                                <i class="bi bi-x-circle"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Procesado</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer position-relative light-background mt-5">
        <div class="container copyright text-center">
            <p>© <strong class="px-1 sitename">Lab-Explora</strong> <span>Todos los derechos reservados</span></p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        // Inicializar Animations
        AOS.init();

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
        
        // Toggle sidebar en móvil
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarWrapper = document.getElementById('sidebarWrapper');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebarWrapper.classList.toggle('active');
                
                // Crear overlay si no existe
                let overlay = document.querySelector('.sidebar-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'sidebar-overlay';
                    document.body.appendChild(overlay);
                }
                
                overlay.classList.toggle('active');
                
                // Cerrar sidebar al hacer click en overlay
                overlay.addEventListener('click', function() {
                    sidebarWrapper.classList.remove('active');
                    overlay.classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>
