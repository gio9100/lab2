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
    <link rel="stylesheet" href="assets/css/chat.css">
</head>
<body>
    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../index.php" class="logo d-flex align-items-end">
                    <img src="../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">LabChat</h1><span></span>
                </a>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <?php 
                        $nombre_usuario = $_SESSION['publicador_nombre'] ?? $_SESSION['admin_nombre'] ?? 'Usuario';
                        $rol_label = ($current_user_role === 'publicador') ? 'Publicador' : 'Admin';
                        ?>
                        <span class="saludo">🧪 <?= $rol_label ?>: <?= htmlspecialchars($nombre_usuario) ?></span>
                        <?php if($current_user_role === 'publicador'): ?>
                            <a href="../forms/publicadores/logout-publicadores.php" class="logout-btn">Cerrar sesión</a>
                        <?php else: ?>
                            <a href="../forms/admins/logout.php" class="logout-btn">Cerrar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
    <script src="assets/js/chat.js"></script>
</body>
</html>

