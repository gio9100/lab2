<?php
// sidebar-admin.php
// Manejo de prefijo de ruta para subdirectorios
if (!isset($path_prefix)) {
    $path_prefix = '';
}

// Asegurar que tenemos las estadísticas si no se han cargado
$sidebar_stats = null;
if (isset($stats) && isset($stats['total_usuarios'])) {
    $sidebar_stats = $stats;
} elseif (isset($conn) && function_exists('obtenerEstadisticasAdmin')) {
    $sidebar_stats = obtenerEstadisticasAdmin($conn);
}
if (!isset($stats_reportes) && isset($conn) && function_exists('obtenerEstadisticasReportes')) {
    $stats_reportes = obtenerEstadisticasReportes($conn);
}

// Nivel de admin
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';
$current_page = basename($_SERVER['PHP_SELF']);

function isActive($link_page, $current_page) {
    return $link_page === $current_page ? 'active' : '';
}
?>

<div class="sidebar-nav">
    <div class="list-group">
        <a href="<?= $path_prefix ?>../../pagina-principal.php" class="list-group-item list-group-item-action">
            <i class="bi bi-speedometer2 me-2"></i>Página principal
        </a>
        <a href="<?= $path_prefix ?>index-admin.php" class="list-group-item list-group-item-action <?= isActive('index-admin.php', $current_page) ?>">
             <i class="bi bi-speedometer2 me-2"></i>Panel Principal
        </a>
        <a href="<?= $path_prefix ?>../../ollama_ia/panel-moderacion.php" class="list-group-item list-group-item-action <?= isActive('panel-moderacion.php', $current_page) ?>">
            <i class="bi bi-robot me-2"></i>Moderación Automática
        </a>
        <a href="<?= $path_prefix ?>gestionar-reportes.php" class="list-group-item list-group-item-action <?= isActive('gestionar-reportes.php', $current_page) ?>">
            <i class="bi bi-flag me-2"></i>Gestionar Reportes
            <?php if(isset($stats_reportes) && $stats_reportes['pendientes'] > 0): ?>
            <span class="badge bg-danger float-end"><?= $stats_reportes['pendientes'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= $path_prefix ?>gestionar-comentarios.php" class="list-group-item list-group-item-action <?= isActive('gestionar-comentarios.php', $current_page) ?>">
            <i class="bi bi-chat-dots me-2"></i>Gestionar Comentarios
        </a>
        <a href="<?= $path_prefix ?>gestionar_publicadores.php" class="list-group-item list-group-item-action <?= isActive('gestionar_publicadores.php', $current_page) ?>">
            <i class="bi bi-people me-2"></i>Gestionar Publicadores
        </a>
        <a href="<?= $path_prefix ?>usuarios.php" class="list-group-item list-group-item-action <?= isActive('usuarios.php', $current_page) ?>">
            <i class="bi bi-person-badge me-2"></i>Usuarios Registrados
        </a>
        <a href="<?= $path_prefix ?>gestionar-publicaciones.php" class="list-group-item list-group-item-action <?= isActive('gestionar-publicaciones.php', $current_page) ?>">
            <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
        </a>
        <a href="<?= $path_prefix ?>categorias/listar_categorias.php" class="list-group-item list-group-item-action <?= isActive('listar_categorias.php', $current_page) ?>">
            <i class="bi bi-tags me-2"></i>Categorías
        </a>
        <a href="<?= $path_prefix ?>gestionar_accesos.php" class="list-group-item list-group-item-action <?= isActive('gestionar_accesos.php', $current_page) ?>">
            <i class="bi bi-building me-2"></i>Correos Institucionales
        </a>
        <a href="<?= $path_prefix ?>../../mensajes/chat.php?as=admin" class="list-group-item list-group-item-action">
            <i class="bi bi-chat-left-text me-2"></i>
            <span>Mensajes</span>
        </a>
        <a href="<?= $path_prefix ?>configuracion_ia.php" class="list-group-item list-group-item-action <?= isActive('configuracion_ia.php', $current_page) ?>">
            <i class="bi bi-robot me-2"></i>Configuración IA
        </a>
        <a href="<?= $path_prefix ?>perfil-admin.php" class="list-group-item list-group-item-action <?= isActive('perfil-admin.php', $current_page) ?>">
            <i class="bi bi-person-circle me-2"></i>Mi Perfil
        </a>
        <?php if($admin_nivel == 'superadmin'): ?>
        <a href="<?= $path_prefix ?>admins.php" class="list-group-item list-group-item-action <?= isActive('admins.php', $current_page) ?>">
            <i class="bi bi-shield-check me-2"></i>Administradores
        </a>
        <?php endif; ?>
    </div>
    
    <!-- Resumen rápido -->
    <?php if(isset($sidebar_stats)): ?>
    <div class="quick-stats-card mt-4">
        <div class="card-header">
            <h6 class="card-title mb-0">Resumen del Sistema</h6>
        </div>
        <div class="card-body">
            <div class="stat-item">
                <small class="text-muted">Usuarios: <?= $sidebar_stats['total_usuarios'] ?? 0 ?></small>
            </div>
            <div class="stat-item">
                <small class="text-muted">Publicadores: <?= $sidebar_stats['total_publicadores'] ?? 0 ?></small>
            </div>
            <div class="stat-item">
                <small class="text-muted">Publicaciones: <?= $sidebar_stats['total_publicaciones'] ?? 0 ?></small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Overlay para móviles -->
<div id="sidebar-overlay" class="sidebar-overlay"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Funcionalidad del toggle del sidebar
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebarWrapper');
    const overlay = document.getElementById('sidebar-overlay');

    // Mover overlay al body para evitar problemas de z-index y stacking context
    if (overlay && overlay.parentNode !== document.body) {
        document.body.appendChild(overlay);
    }

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault(); 
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open'); // Prevenir scroll
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (icon) {
                if (sidebar.classList.contains('active')) {
                    icon.classList.remove('bi-list');
                    icon.classList.add('bi-x');
                } else {
                    icon.classList.remove('bi-x');
                    icon.classList.add('bi-list');
                }
            }
        });
    }

    // Cerrar al hacer click en overlay
    if (overlay) {
        overlay.addEventListener('click', function() {
            if (sidebar) sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            
            if (sidebarToggle) {
                const icon = sidebarToggle.querySelector('i');
                if (icon) {
                    icon.classList.add('bi-list');
                    icon.classList.remove('bi-x');
                }
            }
        });
    }
});
</script>

<!-- Widget Accesibilidad -->
<script src="../../assets/js/accessibility-widget.js?v=3.2"></script>
