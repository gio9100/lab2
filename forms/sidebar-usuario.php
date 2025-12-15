<?php
// Determinar la ruta base dependiendo de dónde se incluya
$base_path = "./";
if (strpos($_SERVER['SCRIPT_NAME'], '/forms/') !== false) {
    // Si estamos en /forms/ (ej: perfil.php), la base es ../
    $base_path = "../";
} elseif (strpos($_SERVER['SCRIPT_NAME'], '/mensajes/') !== false) {
    // Si estamos en /mensajes/, la base es ../
    $base_path = "../";
}

$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar-wrapper" id="sidebar-wrapper">
    <div class="sidebar-nav">
        <!-- Header del sidebar -->
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <h5 class="m-0" style="color: #7390A0; font-weight: 700;">Menú</h5>
            <button class="btn-close d-md-none" id="sidebar-close"></button>
        </div>

        <div class="list-group">
            <a href="<?= $base_path ?>pagina-principal.php" class="list-group-item list-group-item-action <?= ($current_page == 'pagina-principal.php') ? 'active' : '' ?>">
                <i class="bi bi-house me-2"></i>Inicio
            </a>
            
            <a href="<?= $base_path ?>index.php" class="list-group-item list-group-item-action <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                <i class="bi bi-book me-2"></i>Ver Publicaciones
            </a>
            
            <?php if (isset($_SESSION['usuario_id']) || isset($_SESSION['publicador_id']) || isset($_SESSION['admin_id'])): ?>
                <!-- Links para usuarios logueados -->
                
                <a href="<?= $base_path ?>forms/perfil.php" class="list-group-item list-group-item-action <?= ($current_page == 'perfil.php') ? 'active' : '' ?>">
                    <i class="bi bi-person me-2"></i>Mi Perfil
                </a>

                <?php
                    $logout_path = $base_path . "forms/logout.php";
                ?>
                <a href="<?= $logout_path ?>" class="list-group-item list-group-item-action text-danger">
                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                </a>

            <?php else: ?>
                <!-- Links para visitantes -->
                <a href="<?= $base_path ?>forms/inicio-sesion.php" class="list-group-item list-group-item-action <?= ($current_page == 'inicio-sesion.php') ? 'active' : '' ?>">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                </a>
                <a href="<?= $base_path ?>forms/register.php" class="list-group-item list-group-item-action <?= ($current_page == 'register.php') ? 'active' : '' ?>">
                    <i class="bi bi-person-plus me-2"></i>Registrarse
                </a>
            <?php endif; ?>

             <!-- Link Publicador -->
            <a href="<?= $base_path ?>forms/publicadores/inicio-sesion-publicadores.php" class="list-group-item list-group-item-action">
                <i class="bi bi-pencil-square me-2"></i>¿Eres publicador?
            </a>
            
             <!-- Legal Links -->
             <div class="mt-2">
                <a href="<?= $base_path ?>terminos.php" class="list-group-item list-group-item-action small">
                    <i class="bi bi-file-text me-2"></i>Términos y Condiciones
                </a>
                <a href="<?= $base_path ?>privacidad.php" class="list-group-item list-group-item-action small">
                    <i class="bi bi-shield-lock me-2"></i>Política de Privacidad
                </a>
            </div>

             <!-- Social Links (Mobile Only style) -->
            <div class="mt-4 px-2">
                <h6 class="text-muted text-uppercase small fw-bold mb-3">Síguenos</h6>
                <div class="d-flex gap-3">
                    <a href="#" class="text-decoration-none" style="color: #1877f2; font-size: 1.5rem;"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-decoration-none" style="color: #1da1f2; font-size: 1.5rem;"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-decoration-none" style="color: #e4405f; font-size: 1.5rem;"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
    // Script simple para manejar el sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const sidebarWrapper = document.getElementById('sidebar-wrapper');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const body = document.body;

        function openSidebar() {
            sidebarWrapper.classList.add('active');
            sidebarOverlay.classList.add('active');
            body.classList.add('sidebar-open');
        }

        function closeSidebar() {
            sidebarWrapper.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            body.classList.remove('sidebar-open');
        }

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                openSidebar();
            });
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', closeSidebar);
        }
    });
</script>

<!-- Widget Accesibilidad -->
<script src="../assets/js/accessibility-widget.js?v=3.2"></script>
