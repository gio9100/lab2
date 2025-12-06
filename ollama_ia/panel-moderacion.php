<?php
// =============================================================================
// ARCHIVO: panel-moderacion.php
// PROPÓSITO: Interfaz web para moderar publicaciones automáticamente
// =============================================================================

// Iniciamos la sesión para verificar el usuario logueado
session_start();

// Incluimos la configuración de administrador para validar permisos
require_once '../forms/admins/config-admin.php';

// Verificamos que el usuario sea administrador, si no, redirige
requerirAdmin();

// Obtenemos los datos del administrador desde la sesión
$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador';
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Moderación Automática - Lab-Explorer</title>
    
    <!-- Fuentes de Google Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- CSS de Vendors -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Personalizado -->
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css-admins/admin.css">
    
    <!-- Estilos adicionales específicos para este panel -->
    <style>
        .publicacion-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .publicacion-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .publicacion-titulo {
            font-size: 1.4em;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .publicacion-contenido {
            color: #6c757d;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        
        .publicacion-meta {
            color: #999;
            font-size: 0.9em;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        /* Botón de moderar con gradiente azul/morado */
        .btn-moderar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .btn-moderar:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        /* Estilos del Modal */
        .modal {
            display: none; /* Oculto por defecto */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 700px;
            width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        
        .close-modal {
            float: right;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        
        .close-modal:hover { color: #000; }
        
        /* Estilos para resultados de moderación */
        .resultado-aprobado {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .resultado-rechazado {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .resultado-revision {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        /* Spinner de carga */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Barra de confianza */
        .confianza-bar {
            width: 100%;
            height: 35px;
            background: #e9ecef;
            border-radius: 8px;
            overflow: hidden;
            margin: 15px 0;
        }
        
        .confianza-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #ffc107 50%, #dc3545 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
    </style>
</head>
<body class="admin-page">

    <!-- HEADER -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../index.php" class="logo d-flex align-items-end">
                    <img src="../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1>
                </a>
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="../forms/admins/logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-12">
                    <!-- Título de la sección -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>🤖 Panel de Moderación Automática</h2>
                        <p>Sistema inteligente de moderación basado en reglas</p>
                    </div>
                    
                    <!-- Botón para volver -->
                    <div class="mb-4" data-aos="fade-up">
                        <a href="../forms/admins/gestionar-publicaciones.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver a Gestionar Publicaciones
                        </a>
                    </div>
                    
                    <!-- Contenedor donde se cargarán las publicaciones dinámicamente -->
                    <div id="publicaciones-container">
                        <!-- JavaScript llenará esto -->
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para mostrar resultados -->
    <div id="modal-resultado" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="cerrarModal()">&times;</span>
            <div id="resultado-contenido"></div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        // Función para cargar publicaciones pendientes
        function cargarPublicaciones() {
            const container = document.getElementById('publicaciones-container');
            container.innerHTML = '<div class="loading-message"><div class="spinner"></div><p>Cargando publicaciones...</p></div>';
            
            fetch('obtener-publicaciones.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.success) {
                        container.innerHTML = '<div class="error-message">❌ Error: ' + data.error + '</div>';
                        return;
                    }
                    if (data.publicaciones.length === 0) {
                        container.innerHTML = '<div class="admin-card"><div class="card-body text-center"><p class="text-muted">No hay publicaciones pendientes.</p></div></div>';
                        return;
                    }
                    container.innerHTML = '';
                    data.publicaciones.forEach(pub => {
                        container.appendChild(crearTarjetaPublicacion(pub));
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="error-message">❌ Error de conexión.</div>';
                });
        }
        
        // Función para crear el HTML de una tarjeta
        function crearTarjetaPublicacion(publicacion) {
            const card = document.createElement('div');
            card.className = 'publicacion-card';
            card.setAttribute('data-aos', 'fade-up');
            
            const contenido = publicacion.resumen || (publicacion.contenido ? publicacion.contenido.substring(0, 200) + '...' : 'Sin contenido');
            
            card.innerHTML = `
                <h3 class="publicacion-titulo">${publicacion.titulo || 'Sin título'}</h3>
                <p class="publicacion-contenido">${contenido}</p>
                <p class="publicacion-meta">
                    <i class="bi bi-calendar3 me-2"></i>${publicacion.fecha_creacion || 'Fecha desconocida'} | 
                    <i class="bi bi-person ms-3 me-2"></i>${publicacion.autor || 'Autor desconocido'}
                </p>
                <button class="btn-moderar" onclick="moderarAutomaticamente(${publicacion.id})">
                    <i class="bi bi-lightning-charge me-2"></i>Moderar Automáticamente
                </button>
            `;
            return card;
        }
        
        // Función para enviar a moderar
        function moderarAutomaticamente(publicacionId) {
            mostrarModal('<div class="spinner"></div><p style="text-align:center;">Analizando publicación...</p>');
            
            const formData = new FormData();
            formData.append('publicacion_id', publicacionId);
            
            fetch('moderar-local.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    mostrarError(data.error);
                    return;
                }
                mostrarResultado(data);
                setTimeout(cargarPublicaciones, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error de comunicación con el servidor.');
            });
        }
        
        // Función para mostrar el resultado en el modal
        function mostrarResultado(data) {
            let clase = '', icono = '';
            if (data.decision === 'publicado') {
                clase = 'resultado-aprobado'; icono = '✅';
            } else if (data.decision === 'rechazada') {
                clase = 'resultado-rechazado'; icono = '❌';
            } else {
                clase = 'resultado-revision'; icono = '⚠️';
            }
            
            let html = `
                <div class="${clase}">
                    <h2>${icono} ${(data.decision || '').toUpperCase()}</h2>
                    <p>${data.razon || ''}</p>
                </div>
                <div style="margin:20px 0;">
                    <h3>📊 Puntuación de Calidad</h3>
                    <div class="confianza-bar">
                        <div class="confianza-fill" style="width:${data.confianza}%">${data.confianza}/100</div>
                    </div>
                </div>
            `;
            
            if (data.detalles) {
                html += '<div class="detalles"><h3>📋 Detalles</h3>';
                for (let [k, v] of Object.entries(data.detalles)) {
                    html += `<div><strong>${k.replace(/_/g, ' ')}:</strong> ${v}</div>`;
                }
                html += '</div>';
            }
            
            mostrarModal(html);
        }
        
        function mostrarError(msg) {
            mostrarModal(`<div class="resultado-rechazado"><h2>❌ Error</h2><p>${msg}</p></div>`);
        }
        
        function mostrarModal(content) {
            document.getElementById('resultado-contenido').innerHTML = content;
            document.getElementById('modal-resultado').style.display = 'flex';
        }
        
        function cerrarModal() {
            document.getElementById('modal-resultado').style.display = 'none';
        }
        
        window.onclick = function(e) {
            if (e.target == document.getElementById('modal-resultado')) cerrarModal();
        }
        
        document.addEventListener('DOMContentLoaded', cargarPublicaciones);
    </script>
    
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>AOS.init();</script>
</body>
</html>