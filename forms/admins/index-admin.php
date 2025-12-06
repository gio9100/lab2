<?php
// Panel principal de administración
// Muestra estadísticas y tablas de gestión

// Iniciar sesión
session_start();

// Incluir configuración de admin
require_once "config-admin.php";

// Verificar permisos de admin
requerirAdmin();

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Obtener estadísticas generales
$stats = obtenerEstadisticasAdmin($conn);
$stats_reportes = obtenerEstadisticasReportes($conn);

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Aprobar publicador
    if (isset($_POST['aprobar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        if (aprobarPublicador($publicador_id, $conn)) {
            $mensaje = "Publicador aprobado exitosamente";
            $exito = true;
        }
    }
    
    // Rechazar publicador
    if (isset($_POST['rechazar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        $motivo = trim($_POST['motivo'] ?? "");
        
        if (rechazarPublicador($publicador_id, $motivo, $conn)) {
            $mensaje = "Publicador rechazado";
            $exito = true;
        }
    }
    
    // Suspender publicador
    if (isset($_POST['suspender_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        $motivo = trim($_POST['motivo'] ?? "");
        
        if (suspenderPublicador($publicador_id, $motivo, $conn)) {
            $mensaje = "Publicador suspendido";
            $exito = true;
        }
    }
    
    // Activar publicador
    if (isset($_POST['activar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        
        if (activarPublicador($publicador_id, $conn)) {
            $mensaje = "Publicador activado";
            $exito = true;
        }
    }
}

// Obtener datos para las tablas
$publicadores_pendientes = obtenerPublicadoresPendientes($conn);
$publicadores_todos = obtenerTodosPublicadores($conn);
$usuarios_normales = obtenerUsuariosNormales($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administración - Lab-Explorer</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
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

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../../index.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-speedometer2 me-2"></i>Página principal
                            </a>
                            <a href="../../ollama_ia/panel-moderacion.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-robot me-2"></i>Moderación Automática
                            </a>
                            <a href="gestionar-reportes.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-flag me-2"></i>Gestionar Reportes
                                <?php if($stats_reportes['pendientes'] > 0): ?>
                                <span class="badge bg-danger float-end"><?= $stats_reportes['pendientes'] ?></span>
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
                            <a href="../../mensajes/chat.php?as=admin" class="list-group-item list-group-item-action">
                                <i class="bi bi-chat-left-text me-2"></i>
                                <span>Mensajes</span>
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
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
                    
                    <!-- Mensajes -->
                    <?php if(isset($mensaje)): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Panel de Administración</h2>
                    </div>
                    
                    <!-- Tarjetas de Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        
                        <!-- Total Usuarios -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total_usuarios'] ?></h4>
                                    <small>Usuarios Totales</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Total Publicadores -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total_publicadores'] ?></h4>
                                    <small>Publicadores</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pendientes -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['publicadores_pendientes'] ?></h4>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Publicaciones -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total_publicaciones'] ?></h4>
                                    <small>Publicaciones</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Administradores -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card secondary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total_admins'] ?></h4>
                                    <small>Administradores</small>
                                </div>
                            </div>
                        </div>

                        <!-- Reportes Pendientes -->
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card danger">
                                <div class="stat-content text-center">
                                    <h4><?= $stats_reportes['pendientes'] ?></h4>
                                    <small>Reportes</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla 1: Publicadores Pendientes -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header warning-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-clock-history me-2"></i>
                                Publicadores Pendientes de Aprobación
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($publicadores_pendientes)): ?>
                                <p class="text-muted">No hay publicadores pendientes de aprobación.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Especialidad</th>
                                                <th>Institución</th>
                                                <th>Fecha Registro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($publicadores_pendientes as $publicador): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($publicador['nombre']) ?></td>
                                                <td><?= htmlspecialchars($publicador['email']) ?></td>
                                                <td><?= htmlspecialchars($publicador['especialidad']) ?></td>
                                                <td><?= htmlspecialchars($publicador['institucion'] ?? 'No especificada') ?></td>
                                                
                                                <td><?= date('d/m/Y', strtotime($publicador['fecha_registro'])) ?></td>
                                                
                                                <td>
                                                    <div class="action-buttons">
                                                        <!-- Formulario Aprobar -->
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="publicador_id" value="<?= $publicador['id'] ?>">
                                                            <button type="submit" name="aprobar_publicador" class="btn btn-success btn-sm">
                                                                <i class="bi bi-check-lg"></i> Aprobar
                                                            </button>
                                                        </form>
                                                        
                                                        <!-- Botón Rechazar (Modal) -->
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalRechazar<?= $publicador['id'] ?>">
                                                            <i class="bi bi-x-lg"></i> Rechazar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Rechazar -->
                                            <div class="modal fade" id="modalRechazar<?= $publicador['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Rechazar Publicador</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="publicador_id" value="<?= $publicador['id'] ?>">
                                                            <div class="modal-body">
                                                                <p>¿Estás seguro de rechazar a <strong><?= htmlspecialchars($publicador['nombre']) ?></strong>?</p>
                                                                <div class="form-group">
                                                                    <label for="motivo" class="form-label">Motivo del rechazo:</label>
                                                                    <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="rechazar_publicador" class="btn btn-danger">Rechazar</button>
                                                            </div>
                                                        </form>
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

                    <!-- Tabla 2: Todos los Publicadores -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Todos los Publicadores
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($publicadores_todos)): ?>
                                <p class="text-muted">No hay publicadores registrados.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Especialidad</th>
                                                <th>Estado</th>
                                                <th>Fecha Registro</th>
                                                <th>Último Acceso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($publicadores_todos as $publicador): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($publicador['nombre']) ?></td>
                                                <td><?= htmlspecialchars($publicador['email']) ?></td>
                                                <td><?= htmlspecialchars($publicador['especialidad']) ?></td>
                                                
                                                <td>
                                                    <span class="status-badge <?= $publicador['estado'] ?>">
                                                        <?= ucfirst($publicador['estado']) ?>
                                                    </span>
                                                </td>
                                                
                                                <td><?= date('d/m/Y', strtotime($publicador['fecha_registro'])) ?></td>
                                                
                                                <td>
                                                    <?= $publicador['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($publicador['ultimo_acceso'])) : 'Nunca' ?>
                                                </td>
                                                
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if($publicador['estado'] == 'activo'): ?>
                                                            <button type="button" class="btn btn-warning btn-sm" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#modalSuspender<?= $publicador['id'] ?>">
                                                                <i class="bi bi-pause"></i> Suspender
                                                            </button>
                                                        <?php elseif($publicador['estado'] == 'suspendido'): ?>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="publicador_id" value="<?= $publicador['id'] ?>">
                                                                <button type="submit" name="activar_publicador" class="btn btn-success btn-sm">
                                                                    <i class="bi bi-play"></i> Activar
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Suspender -->
                                            <?php if($publicador['estado'] == 'activo'): ?>
                                            <div class="modal fade" id="modalSuspender<?= $publicador['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Suspender Publicador</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="publicador_id" value="<?= $publicador['id'] ?>">
                                                            <div class="modal-body">
                                                                <p>¿Estás seguro de suspender a <strong><?= htmlspecialchars($publicador['nombre']) ?></strong>?</p>
                                                                <div class="form-group">
                                                                    <label for="motivo" class="form-label">Motivo de la suspensión:</label>
                                                                    <textarea class="form-control" id="motivo" name="motivo" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="suspender_publicador" class="btn btn-warning">Suspender</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tabla 3: Usuarios Normales -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Usuarios Normales Registrados
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($usuarios_normales)): ?>
                                <p class="text-muted">No hay usuarios registrados.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Fecha Registro</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($usuarios_normales as $usuario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                                <td>
                                                    <span class="status-badge active">Activo</span>
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

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        // Inicializar animaciones AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>
</body>
</html>