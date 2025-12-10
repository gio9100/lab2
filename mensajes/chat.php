<?php
// mensajes/chat.php
// Interfaz principal del sistema de mensajería

require_once 'init.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajería - Lab Explorer</title>
    
    <!-- Vendor CSS -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Main CSS -->
    <link href="../assets/css/main.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Estilos del Chat -->
    <link rel="stylesheet" href="assets/css/chat.css?v=<?= time() ?>">
    <style>
        /* Force mobile styles inline to avoid cache issues */
        @media (max-width: 991px) {
            /* #header { display: none !important; }  <-- RESTORED HEADER */
            
            /* Ajustamos altura para descontar el header aprox (o usamos flex) */
            body { 
                height: 100vh; 
                overflow: hidden; 
                margin: 0; 
                display: flex; 
                flex-direction: column; 
            }
            
            #header {
                flex-shrink: 0; /* Header no se encoge */
                background: white; /* Asegurar fondo blanco */
                border-bottom: 1px solid #dee2e6;
            }

            /* Fix header overlap */
            .header .top-row {
                flex-direction: column !important;
                gap: 10px;
                padding: 10px 0;
            }
            
            .header .social-links {
                display: flex !important;
                flex-direction: column !important;
                align-items: center !important;
                gap: 5px;
            }

            .header .saludo {
                margin-right: 0 !important;
                margin-bottom: 5px;
                font-size: 0.9rem;
            }

            .chat-container { 
                flex: 1; /* Chat ocupa el resto */
                height: auto !important;
                padding: 0; 
                overflow: hidden;
            }
            
            .chat-layout { width: 100%; height: 100%; position: relative; }
            
            /* Sidebar visible by default */
            .chat-sidebar { 
                width: 100% !important; 
                height: 100% !important; 
                display: flex !important;
                position: absolute !important;
                z-index: 10;
                background: white;
            }
            
            /* Chat main hidden by default */
            .chat-main { 
                width: 100% !important; 
                height: 100% !important; 
                display: none !important;
                position: absolute !important;
                z-index: 20;
                background: white;
            }

            /* Active state toggles */
            .chat-layout.chat-active .chat-sidebar { display: none !important; }
            .chat-layout.chat-active .chat-main { display: flex !important; }
            
            .mobile-only { display: block !important; }
            
            /* Make sure back button is visible */
            .btn-back { 
                display: flex !important; 
                margin-top: 10px;
                background: #f0f0f0;
                color: #333;
            }
        }
    </style>
</head>
<body>
    <!-- Header: Hide on mobile using class d-none d-lg-block if available, or just rely on CSS above -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebarToggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="../index.php" class="logo d-flex align-items-end">
                        <img src="../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">LabChat</h1><span></span>
                    </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links d-none d-lg-block">
                        <?php 
                        $nombre_usuario = $_SESSION['publicador_nombre'] ?? $_SESSION['admin_nombre'] ?? 'Usuario';
                        $rol_label = ($current_user_role === 'publicador') ? 'Publicador' : 'Admin';
                        ?>
                        <span class="saludo">🧪 <?= $rol_label ?>: <?= htmlspecialchars($nombre_usuario) ?></span>
                        <?php if($current_user_role === 'publicador'): ?>
                            <a href="../forms/publicadores/logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                        <?php else: ?>
                            <a href="../forms/admins/logout-admin.php" class="logout-btn">Cerrar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="sidebar-wrapper" id="sidebarWrapper" style="z-index: 9999;">
        <div class="d-flex justify-content-end d-md-none p-2">
            <button class="btn-close" id="sidebarClose"></button>
        </div>
        <?php 
        $path_prefix = '../forms/publicadores/';
        if(isset($current_user_role) && $current_user_role == 'admin') {
            $path_prefix = '../forms/admins/';
            include '../forms/admins/sidebar-admin.php'; 
        } else {
            include '../forms/publicadores/sidebar-publicador.php';
        }
        ?>
    </div>

    <!-- Chat Layout -->
    <div class="chat-container">
        <div class="chat-layout">
            <!-- Sidebar de Contactos -->
            <aside class="chat-sidebar">
                <div class="search-container">
                    <div class="search-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-contact" placeholder="Buscar personas...">
                    </div>
                </div>

                <div class="contacts-container" id="contacts-list">
                    <!-- Loading State -->
                    <div class="loading-contacts">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>

                <div class="sidebar-footer">
                    <?php 
                    $backLink = ($current_user_role == 'admin') ? '../forms/admins/index-admin.php' : '../forms/publicadores/index-publicadores.php';
                    ?>
                    <a href="<?= $backLink ?>" class="btn-back">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </aside>

            <!-- Área Principal de Chat -->
            <main class="chat-main">
                <!-- Header del Chat Activo -->
                <header class="chat-header" id="chat-header" style="display: none;">
                    <button id="mobile-back-btn" class="btn-icon mobile-only" style="margin-right: 10px; border:none; background:none;">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="contact-profile">
                        <div class="avatar-wrapper">
                            <img src="" alt="" id="header-avatar" class="avatar">
                            <span class="status-indicator" id="header-status-dot"></span>
                        </div>
                        <div class="contact-details">
                            <h4 id="header-name">Nombre del Contacto</h4>
                            <span id="header-status-text" class="status-text">Desconectado</span>
                        </div>
                    </div>
                </header>

                <!-- Área de Mensajes -->
                <div class="messages-area" id="chat-messages">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="far fa-comments"></i>
                        </div>
                        <h3>Tus Mensajes</h3>
                        <p>Selecciona un contacto de la izquierda para comenzar una conversación.</p>
                    </div>
                </div>

                <!-- Área de Input -->
                <div class="input-area" id="chat-input-area" style="display: none;">
                    <form id="message-form" class="message-form">
                        <div class="input-wrapper">
                            <input type="text" id="message-input" placeholder="Escribe un mensaje..." autocomplete="off">
                        </div>
                        <button type="submit" class="btn-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Variables globales para JS -->
    <script>
        const CURRENT_USER_ID = <?= json_encode($current_user_id) ?>;
        const CURRENT_USER_ROLE = <?= json_encode($current_user_role) ?>;
    </script>
    <script src="assets/js/chat.js?v=<?= time() ?>"></script>
    <script>
        // Global Sidebar Toggle Logic
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarWrapper = document.getElementById('sidebarWrapper') || document.getElementById('sidebar-wrapper');
        const sidebarClose = document.getElementById('sidebarClose') || document.getElementById('sidebar-close');

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
</body>
</html>

