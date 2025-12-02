<?php
session_start();
require_once "config-admin.php";
requerirAdmin();

$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Procesar acciones
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['procesar_reporte'])) {
        $reporte_id = intval($_POST['reporte_id']);
        $accion = $_POST['accion']; // 'aprobar' o 'rechazar'
        
        if (procesarReporte($reporte_id, $accion, $admin_id, $conn)) {
            $mensaje = $accion === 'aprobar' ? 'Reporte aprobado y contenido eliminado' : 'Reporte descartado';
            $exito = true;
        } else {
            $mensaje = 'Error al procesar el reporte';
            $exito = false;
        }
    }
    
    // Procesar acción masiva
    if (isset($_POST['accion_masiva'])) {
        $accion = $_POST['accion_masiva']; // 'aprobar_todos' o 'rechazar_todos'
        
        // Obtener todos los reportes pendientes
        $query = "SELECT id FROM reportes WHERE estado = 'pendiente'";
        $result = $conn->query($query);
        
        $procesados = 0;
        $errores = 0;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $accion_individual = ($accion === 'aprobar_todos') ? 'aprobar' : 'rechazar';
                if (procesarReporte($row['id'], $accion_individual, $admin_id, $conn)) {
                    $procesados++;
                } else {
                    $errores++;
                }
            }
            
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

// Obtener filtros
$filtro_tipo = $_GET['tipo'] ?? null;
$filtro_estado = $_GET['estado'] ?? null;

// Obtener datos
$reportes = obtenerTodosReportes($filtro_tipo, $filtro_estado, $conn);
$stats = obtenerEstadisticasReportes($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Reportes - Lab-Explorer</title>
    
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
</head>
<body class="admin-page">

    <!-- HEADER -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- SIDEBAR -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="../../ollama_ia/panel-moderacion.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-robot me-2"></i>Moderación Automática
                            </a>
                            <a href="gestionar-reportes.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-flag me-2"></i>Gestionar Reportes
                                <?php if($stats['pendientes'] > 0): ?>
                                <span class="badge bg-danger float-end"><?= $stats['pendientes'] ?></span>
                                <?php endif; ?>
                            </a>
                            <a href="gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-badge me-2"></i>Usuarios Registrados
                            </a>
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="categorias/listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- CONTENIDO DERECHO -->
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

                    <!-- Tabla de Reportes -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-list-ul me-2"></i>Lista de Reportes</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($reportes)): ?>
                                <p class="text-muted">No hay reportes que mostrar.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
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
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalDetalle<?= $reporte['id'] ?>">
                                                            <i class="bi bi-eye"></i> Ver
                                                        </button>
                                                        
                                                        <?php if($reporte['estado'] === 'pendiente'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="reporte_id" value="<?= $reporte['id'] ?>">
                                                            <input type="hidden" name="accion" value="aprobar">
                                                            <button type="submit" name="procesar_reporte" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('¿Eliminar el contenido reportado?')">
                                                                <i class="bi bi-trash"></i> Aprobar
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="reporte_id" value="<?= $reporte['id'] ?>">
                                                            <input type="hidden" name="accion" value="rechazar">
                                                            <button type="submit" name="procesar_reporte" class="btn btn-sm btn-secondary"
                                                                    onclick="return confirm('¿Descartar este reporte?')">
                                                                <i class="bi bi-x-circle"></i> Rechazar
                                                            </button>
                                                        </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Detalle -->
                                            <div class="modal fade" id="modalDetalle<?= $reporte['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detalle del Reporte #<?= $reporte['id'] ?></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Tipo:</strong> <?= ucfirst($reporte['tipo']) ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Estado:</strong> <?= ucfirst($reporte['estado']) ?>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Reportado por:</strong> <?= htmlspecialchars($reporte['usuario_nombre'] ?? 'N/A') ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($reporte['fecha_creacion'])) ?>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Motivo:</strong>
                                                                <p class="mb-0"><?= htmlspecialchars($reporte['motivo']) ?></p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Descripción:</strong>
                                                                <p class="mb-0"><?= htmlspecialchars($reporte['descripcion'] ?? 'Sin descripción adicional') ?></p>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>ID de Referencia:</strong> <?= $reporte['referencia_id'] ?>
                                                            </div>
                                                            <?php if($reporte['tipo'] === 'comentario' && !empty($reporte['comentario_contenido'])): ?>
                                                            <div class="mb-3">
                                                                <strong>Comentario Reportado:</strong>
                                                                <div class="alert alert-light mt-2">
                                                                    <p class="mb-2"><strong>Autor:</strong> <?= htmlspecialchars($reporte['comentario_autor_nombre'] ?? 'Usuario eliminado') ?></p>
                                                                    <p class="mb-0"><strong>Contenido:</strong></p>
                                                                    <p class="mb-0"><?= nl2br(htmlspecialchars($reporte['comentario_contenido'])) ?></p>
                                                                </div>
                                                            </div>
                                                            <?php endif; ?>
                                                            <?php if($reporte['tipo'] === 'publicacion'): ?>
                                                            <div class="mt-3">
                                                                <a href="../../ver-publicacion.php?id=<?= $reporte['referencia_id'] ?>" 
                                                                   target="_blank" class="btn btn-primary btn-sm">
                                                                    <i class="bi bi-box-arrow-up-right"></i> Ver Publicación
                                                                </a>
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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

    <!-- SCROLL TO TOP -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- SCRIPTS -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>
