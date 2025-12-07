<?php
session_start();
require_once 'config-publicadores.php';

// VERIFICACIÓN MEJORADA - acepta sesión principal o de publicador
if (isset($_SESSION['publicador_id'])) {
    // Ya tiene sesión de publicador, continuar normalmente
    $publicador_id = $_SESSION['publicador_id'];
} elseif (isset($_SESSION['usuario_id']) && isset($_SESSION['es_publicador']) && $_SESSION['es_publicador'] === true) {
    // Viene de sesión principal y es publicador, crear sesión de publicador
    $email = $_SESSION['usuario_correo'];
    $query = "SELECT * FROM publicadores WHERE email = ? AND estado = 'activo'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $publicador = $result->fetch_assoc();
        $_SESSION["publicador_id"] = $publicador["id"];
        $_SESSION["publicador_nombre"] = $publicador["nombre"];
        $_SESSION["publicador_email"] = $publicador["email"];
        $_SESSION["publicador_especialidad"] = $publicador["especialidad"];
        
        $publicador_id = $publicador["id"];
    } else {
        // No es publicador activo, redirigir al login
        header('Location: login.php');
        exit();
    }
} else {
    // No tiene ninguna sesión válida, redirigir al login
    header('Location: login.php');
    exit();
}

// Obtener datos del publicador desde la sesión
$publicador_nombre = $_SESSION['publicador_nombre'];
$publicador_especialidad = $_SESSION['publicador_especialidad'];

// Variables para mensajes (específicas de publicadores)
$mensaje = "";
$exito = false;

// Verificar si hay mensaje de sesión
if (isset($_SESSION['publicador_mensaje'])) {
    $mensaje = $_SESSION['publicador_mensaje'];
    $exito = ($_SESSION['publicador_tipo_mensaje'] ?? 'error') === 'success';
    
    // Limpiar mensajes de sesión después de leerlos
    unset($_SESSION['publicador_mensaje']);
    unset($_SESSION['publicador_tipo_mensaje']);
}

// Obtener categorías disponibles
$categorias = [];
$query_categorias = "SELECT id, nombre FROM categorias WHERE estado = 'activa' ORDER BY nombre";
$result_categorias = $conn->query($query_categorias);
if ($result_categorias) {
    $categorias = $result_categorias->fetch_all(MYSQLI_ASSOC);
}

// Obtener estadísticas del publicador
$estadisticas = [
    'total_publicaciones' => 0,
    'publicadas' => 0,
    'borradores' => 0,
    'en_revision' => 0
];

$query_stats = "SELECT estado, COUNT(*) as total 
                FROM publicaciones 
                WHERE publicador_id = ? 
                GROUP BY estado";
$stmt_stats = $conn->prepare($query_stats);
$stmt_stats->bind_param("i", $publicador_id);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();

while ($row = $result_stats->fetch_assoc()) {
    $estadisticas[$row['estado']] = $row['total'];
    $estadisticas['total_publicaciones'] += $row['total'];
}
$stmt_stats->close();

// Obtener publicaciones recientes
$publicaciones_recientes = [];
$query_recientes = "SELECT p.id, p.titulo, p.estado, p.fecha_creacion, c.nombre as categoria
                    FROM publicaciones p 
                    LEFT JOIN categorias c ON p.categoria_id = c.id 
                    WHERE p.publicador_id = ? 
                    ORDER BY p.fecha_creacion DESC 
                    LIMIT 5";
$stmt_recientes = $conn->prepare($query_recientes);
$stmt_recientes->bind_param("i", $publicador_id);
$stmt_recientes->execute();
$result_recientes = $stmt_recientes->get_result();
$publicaciones_recientes = $result_recientes->fetch_all(MYSQLI_ASSOC);
$stmt_recientes->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Publicadores - Lab-Explorer</title>
    
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
    
    <!-- Driver.js para Onboarding -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
</head>
<body class="publicador-page">

    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <!-- Hamburger Button -->
                    <button class="btn btn-outline-primary d-md-none me-2" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">Lab-Explorer</h1><span></span>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
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

                <!-- Sidebar (Desktop & Mobile Overlay) -->
                <div class="col-md-3 sidebar-wrapper" id="sidebarWrapper">
                     <!-- Mobile Close Button -->
                    <div class="d-flex justify-content-end d-md-none p-2">
                        <button class="btn-close" id="sidebarClose"></button>
                    </div>
                    <?php include 'sidebar-publicador.php'; ?>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    <!-- Mensajes -->
                    <?php if($mensaje): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Panel del Publicador</h2>
                        <p class="text-muted">Bienvenido al centro de gestión de tus publicaciones científicas</p>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $estadisticas['total_publicaciones'] ?></h4>
                                    <small>Total Publicaciones</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $estadisticas['publicadas'] ?? 0 ?></h4>
                                    <small>Publicadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $estadisticas['borrador'] ?? 0 ?></h4>
                                    <small>Borradores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= $estadisticas['revision'] ?? 0 ?></h4>
                                    <small>En Revisión</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Publicaciones Recientes -->
                        <div class="col-lg-8 mb-4">
                            <div class="admin-card" data-aos="fade-up">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-clock-history me-2"></i>
                                        Publicaciones Recientes
                                    </h5>
                                    <a href="mis-publicaciones.php" class="btn btn-sm btn-outline-primary">
                                        Ver Todas
                                    </a>
                                </div>
                                <div class="card-body">
                                    <?php if(empty($publicaciones_recientes)): ?>
                                        <div class="text-center py-4">
                                            <i class="bi bi-inbox display-4 text-muted"></i>
                                            <p class="text-muted mt-3">No hay publicaciones aún</p>
                                            <!-- CAMBIO AQUÍ: Botón con href directo -->
                                            <a href="nueva-publicacion.php" class="btn btn-primary mt-2">
                                                Crear Primera Publicación
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <div class="publicaciones-list">
                                            <?php foreach($publicaciones_recientes as $publicacion): ?>
                                            <div class="publicacion-item d-flex justify-content-between align-items-center">
                                                <div class="publicacion-info">
                                                    <h6 class="publicacion-titulo"><?= htmlspecialchars($publicacion['titulo']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($publicacion['categoria'] ?? 'Sin categoría') ?> • 
                                                        <?= date('d/m/Y', strtotime($publicacion['fecha_creacion'])) ?>
                                                    </small>
                                                </div>
                                                <span class="status-badge <?= $publicacion['estado'] ?>">
                                                    <?= ucfirst($publicacion['estado']) ?>
                                                </span>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones Rápidas -->
                        <div class="col-lg-4 mb-4">
                            <div class="admin-card" data-aos="fade-up" data-aos-delay="200">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-lightning me-2"></i>
                                        Acciones Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="quick-actions">
                                        <!-- CAMBIO AQUÍ: Botones con href directo -->
                                        <a href="nueva-publicacion.php" class="btn quick-action-btn">
                                            <i class="bi bi-plus-circle me-2"></i>
                                            Nueva Publicación
                                        </a>
                                        <a href="mis-publicaciones.php" class="btn quick-action-btn">
                                            <i class="bi bi-list-ul me-2"></i>
                                            Gestionar Publicaciones
                                        </a>
                                        <a href="perfil.php" class="btn quick-action-btn">
                                            <i class="bi bi-person me-2"></i>
                                            Editar Perfil
                                        </a>
                                        <a href="estadisticas.php" class="btn quick-action-btn">
                                            <i class="bi bi-graph-up me-2"></i>
                                            Ver Estadísticas
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- ELIMINADO: Modal para Crear Publicación (ya no es necesario) -->

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script>
        // Inicializar AOS
        AOS.init();

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
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
    </script>

    <!-- Script del Tour Onboarding -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        if (!localStorage.getItem('tour_publicador_visto')) {
            const driver = window.driver.js.driver;
            
            const driverObj = driver({
                showProgress: true,
                animate: true,
                doneBtnText: '¡Entendido!',
                nextBtnText: 'Siguiente',
                prevBtnText: 'Anterior',
                steps: [
                    { 
                        element: '.saludo', 
                        popover: { 
                            title: '👨‍🔬 Panel de Publicador', 
                            description: 'Bienvenido a tu espacio de trabajo. Aquí podrás gestionar todas tus contribuciones científicas.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '.stats-grid', 
                        popover: { 
                            title: '📊 Estadísticas Rápidas', 
                            description: 'Monitorea el impacto de tus publicaciones y el estado de tus borradores en tiempo real.', 
                            side: "bottom", 
                            align: 'start' 
                        } 
                    },
                    { 
                        element: '.quick-actions', 
                        popover: { 
                            title: '⚡ Acciones Rápidas', 
                            description: 'Crea nuevas publicaciones o gestiona tu perfil con un solo clic.', 
                            side: "left", 
                            align: 'start' 
                        } 
                    }
                ]
            });

            driverObj.drive();
            localStorage.setItem('tour_publicador_visto', 'true');
        }
    });
    </script>
</body>
</html>