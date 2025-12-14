<?php
if (!isset($path_prefix)) {
    $path_prefix = '';
}
?>
<div class="sidebar-nav">
    <div class="list-group">
        <a href="<?= $path_prefix ?>index-publicadores.php" class="list-group-item list-group-item-action">
            <i class="bi bi-speedometer2 me-2"></i>Panel Principal
        </a>
        <a href="<?= $path_prefix ?>../../pagina-principal.php" class="list-group-item list-group-item-action">
            <i class="bi bi-house me-2"></i>Página Principal (Web)
        </a>
        <a href="<?= $path_prefix ?>crear_nueva_publicacion.php" class="list-group-item list-group-item-action">
            <i class="bi bi-plus-circle me-2"></i>Nueva Publicación
        </a>
        <a href="<?= $path_prefix ?>mis-publicaciones.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'mis-publicaciones.php' ? 'active' : '' ?>">
            <i class="bi bi-file-text me-2"></i>Mis Publicaciones
        </a>
        <!-- Mensajes link: check if prefix is used, assume path relative to forms/publicadores/ -->
        <a href="<?= $path_prefix ?>../../mensajes/chat.php?as=publicador" class="list-group-item list-group-item-action">
            <i class="bi bi-chat-left-text me-2"></i>Mensajes
        </a>
        <a href="<?= $path_prefix ?>estadisticas.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'estadisticas.php' ? 'active' : '' ?>">
            <i class="bi bi-graph-up me-2"></i>Estadísticas
        </a>
        <a href="<?= $path_prefix ?>perfil.php" class="list-group-item list-group-item-action <?= basename($_SERVER['PHP_SELF']) == 'perfil.php' ? 'active' : '' ?>">
            <i class="bi bi-person me-2"></i>Mi Perfil
        </a>
    </div>

    <!-- Información del publicador -->
    <div class="quick-stats-card mt-4 d-none d-md-block">
        <div class="card-header">
            <h6 class="card-title mb-0">Mi Información</h6>
        </div>
        <div class="card-body">
            <div class="stat-item">
                <small class="text-muted"><strong>Especialidad:</strong><br>
                    <?= htmlspecialchars($_SESSION['publicador_especialidad'] ?? 'No definida') ?></small>
            </div>
            <div class="stat-item">
                <small class="text-muted"><strong>Estado:</strong> Activo</small>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const closeSidebarBtn = document.getElementById('closeSidebar');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
    });
</script>

<!-- Widget Accesibilidad -->
<script src="../../assets/js/accessibility-widget.js?v=3.2"></script>
