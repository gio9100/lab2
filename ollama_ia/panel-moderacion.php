<?php
// =============================================================================
// ARCHIVO: panel-moderacion.php
// PROPÓSITO: Interfaz web para moderar publicaciones con IA
// =============================================================================

// Iniciar sesión
session_start();

// Incluir la configuración de administrador
require_once '../forms/admins/config-admin.php';

// Verificar que el usuario sea administrador
// Si no lo es, redirige automáticamente al login
requerirAdmin();

// Obtener datos del administrador
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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- CSS de Vendors -->
    <link href="../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Personalizado -->
    <link href="../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css-admins/admin.css">
    
    <!-- Estilos adicionales para el panel de moderación -->
    <style>
        /* Tarjeta de publicación - estilo admin-card */
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
        
        /* Botón de moderar */
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
        
        .btn-moderar:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Modal */
        .modal {
            display: none;
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
            line-height: 20px;
        }
        
        .close-modal:hover {
            color: #000;
        }
        
        /* Resultados */
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
        
        /* Spinner */
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
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        /* Detalles */
        .detalles {
            margin-top: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .detalle-item {
            margin: 12px 0;
            padding: 12px;
            background: white;
            border-left: 4px solid #667eea;
            border-radius: 4px;
            padding-left: 15px;
        }
        
        /* Mensaje de error */
        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        /* Mensaje de carga */
        .loading-message {
            text-align: center;
            color: #6c757d;
            padding: 40px;
        }
    </style>
</head>
<body class="admin-page">

    <!-- HEADER -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                
                <a href="../index.php" class="logo d-flex align-items-end">
                    <img src="../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
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
                <!-- Contenido principal -->
                <div class="col-12">
                    <!-- Título -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>🤖 Panel de Moderación Automática</h2>
                        <p>Sistema inteligente de moderación de publicaciones</p>
                    </div>
                    
                    <!-- Botón para volver -->
                    <div class="mb-4" data-aos="fade-up">
                        <a href="../forms/admins/gestionar-publicaciones.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver a Gestionar Publicaciones
                        </a>
                    </div>
                    
                    <!-- Aquí se cargarán las publicaciones dinámicamente -->
                    <div id="publicaciones-container">
                        <!-- Las publicaciones se cargarán aquí con JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Modal para mostrar resultados -->
    <div id="modal-resultado" class="modal">
        <div class="modal-content">
            <!-- Botón para cerrar el modal -->
            <span class="close-modal" onclick="cerrarModal()">&times;</span>
            
            <!-- Aquí se mostrará el resultado del análisis -->
            <div id="resultado-contenido">
                <!-- El contenido se carga dinámicamente -->
            </div>
        </div>
    </div>
    
    <!-- JavaScript para la funcionalidad -->
    <script>
        // =============================================================================
        // FUNCIÓN: cargarPublicaciones
        // Carga las publicaciones pendientes de moderación
        // =============================================================================
        function cargarPublicaciones() {
            // Obtener el contenedor donde se mostrarán las publicaciones
            const container = document.getElementById('publicaciones-container');
            
            // Mostrar un mensaje de carga
            container.innerHTML = '<div class="loading-message"><div class="spinner"></div><p>Cargando publicaciones...</p></div>';
            
            // Hacer una petición AJAX para obtener las publicaciones
            fetch('obtener-publicaciones.php')
                .then(response => response.json()) // Convertir la respuesta a JSON
                .then(data => {
                    // Verificar si hubo error
                    if (!data.success) {
                        container.innerHTML = '<div class="error-message">❌ Error: ' + data.error + '</div>';
                        return;
                    }
                    
                    // Si no hay publicaciones
                    if (data.publicaciones.length === 0) {
                        container.innerHTML = '<div class="admin-card" data-aos="fade-up"><div class="card-body text-center"><p class="text-muted mb-0">No hay publicaciones pendientes de moderación.</p></div></div>';
                        return;
                    }
                    
                    // Limpiar el contenedor
                    container.innerHTML = '';
                    
                    // Crear una tarjeta para cada publicación
                    data.publicaciones.forEach(pub => {
                        const card = crearTarjetaPublicacion(pub);
                        container.appendChild(card);
                    });
                })
                .catch(error => {
                    // Si hay error en la petición
                    console.error('Error:', error);
                    container.innerHTML = '<div class="error-message">❌ Error al cargar las publicaciones. Verifica que estés conectado.</div>';
                });
        }
        
        // =============================================================================
        // FUNCIÓN: crearTarjetaPublicacion
        // Crea el HTML de una tarjeta de publicación
        // =============================================================================
        function crearTarjetaPublicacion(publicacion) {
            // Crear el elemento div para la tarjeta
            const card = document.createElement('div');
            card.className = 'publicacion-card';
            card.setAttribute('data-aos', 'fade-up');
            
            // Construir el HTML interno
            card.innerHTML = `
                <h3 class="publicacion-titulo">${publicacion.titulo}</h3>
                <p class="publicacion-contenido">${publicacion.resumen || publicacion.contenido.substring(0, 200) + '...'}</p>
                <p class="publicacion-meta">
                    <i class="bi bi-calendar3 me-2"></i>${publicacion.fecha_creacion} | 
                    <i class="bi bi-person ms-3 me-2"></i>${publicacion.autor || 'Autor desconocido'}
                </p>
                <button class="btn-moderar" onclick="moderarConIA(${publicacion.id})">
                    <i class="bi bi-robot me-2"></i>Moderar con IA
                </button>
            `;
            
            return card;
        }
        
        // =============================================================================
        // FUNCIÓN: moderarConIA
        // Envía una publicación a la IA para que la analice
        // =============================================================================
        function moderarConIA(publicacionId) {
            // Mostrar el modal con un spinner de carga
            mostrarModal('<div class="spinner"></div><p style="text-align:center;">Analizando publicación...</p><p style="text-align:center; color:#999; font-size:0.9em;">Usando moderación local (sin IA externa)</p>');
            
            // Crear los datos del formulario
            const formData = new FormData();
            formData.append('publicacion_id', publicacionId);
            
            // Hacer la petición AJAX al endpoint LOCAL
            fetch('moderar-local.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json()) // Convertir respuesta a JSON
            .then(data => {
                // Verificar si hubo error
                if (!data.success) {
                    mostrarError(data.error);
                    return;
                }
                
                // Mostrar el resultado del análisis
                mostrarResultado(data);
                
                // Recargar las publicaciones después de 2 segundos
                setTimeout(() => {
                    cargarPublicaciones();
                }, 2000);
            })
            .catch(error => {
                // Si hay error en la petición
                console.error('Error:', error);
                mostrarError('Error al comunicarse con el servidor. Por favor, intenta de nuevo.');
            });
        }
        
        // =============================================================================
        // FUNCIÓN: mostrarResultado
        // Muestra el resultado del análisis en el modal
        // =============================================================================
        function mostrarResultado(data) {
            // Determinar la clase CSS según la decisión
            let claseResultado = '';
            if (data.decision === 'aprobada') {
                claseResultado = 'resultado-aprobado';
            } else if (data.decision === 'rechazada') {
                claseResultado = 'resultado-rechazado';
            } else {
                claseResultado = 'resultado-revision';
            }
            
            // Construir el HTML del resultado
            let html = `
                <div class="${claseResultado}">
                    <h2>${data.icono} ${data.decision.toUpperCase()}</h2>
                    <p style="margin-top:10px;">${data.mensaje}</p>
                </div>
                
                <div style="margin:20px 0;">
                    <h3>📊 Nivel de Confianza</h3>
                    <div class="confianza-bar">
                        <div class="confianza-fill" style="width:${data.confianza}%">
                            ${data.confianza}%
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3>💭 Razón</h3>
                    <p style="padding:15px; background:#f8f9fa; border-radius:8px; margin-top:10px;">
                        ${data.razon}
                    </p>
                </div>
            `;
            
            // Si hay detalles adicionales, mostrarlos
            if (data.detalles && Object.keys(data.detalles).length > 0) {
                html += '<div class="detalles"><h3>📋 Detalles del Análisis</h3>';
                
                // Iterar sobre cada detalle
                for (let [clave, valor] of Object.entries(data.detalles)) {
                    // Formatear el nombre de la clave
                    const nombreClave = clave.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    html += `<div class="detalle-item"><strong>${nombreClave}:</strong> ${valor}</div>`;
                }
                
                html += '</div>';
            }
            
            // Agregar información del tipo de análisis
            html += `
                <p style="margin-top:20px; color:#999; font-size:0.9em; text-align:center;">
                    Tipo de análisis: ${data.tipo_analisis}
                </p>
            `;
            
            // Mostrar en el modal
            mostrarModal(html);
        }
        
        // =============================================================================
        // FUNCIÓN: mostrarError
        // Muestra un mensaje de error en el modal
        // =============================================================================
        function mostrarError(mensaje) {
            const html = `
                <div class="resultado-rechazado">
                    <h2>❌ Error</h2>
                    <p style="margin-top:10px;">${mensaje}</p>
                </div>
                <p style="margin-top:20px; color:#666; font-size:0.9em;">
                    <strong>Posibles soluciones:</strong><br>
                    • Verifica que Ollama esté corriendo (ejecuta: <code>ollama list</code>)<br>
                    • Asegúrate de haber descargado un modelo (ejecuta: <code>ollama pull llama2</code>)<br>
                    • Revisa los logs en ollama_ia/logs/ollama_debug.log
                </p>
            `;
            mostrarModal(html);
        }
        
        // =============================================================================
        // FUNCIÓN: mostrarModal
        // Muestra el modal con el contenido especificado
        // =============================================================================
        function mostrarModal(contenido) {
            // Obtener los elementos del DOM
            const modal = document.getElementById('modal-resultado');
            const contenedor = document.getElementById('resultado-contenido');
            
            // Establecer el contenido
            contenedor.innerHTML = contenido;
            
            // Mostrar el modal (cambiar display a flex)
            modal.style.display = 'flex';
        }
        
        // =============================================================================
        // FUNCIÓN: cerrarModal
        // Cierra el modal
        // =============================================================================
        function cerrarModal() {
            const modal = document.getElementById('modal-resultado');
            modal.style.display = 'none';
        }
        
        // =============================================================================
        // EVENTO: Click fuera del modal para cerrarlo
        // =============================================================================
        window.onclick = function(event) {
            const modal = document.getElementById('modal-resultado');
            // Si se hace click en el fondo oscuro (no en el contenido)
            if (event.target === modal) {
                cerrarModal();
            }
        }
        
        // =============================================================================
        // CARGAR PUBLICACIONES AL INICIAR LA PÁGINA
        // =============================================================================
        // Cuando la página termine de cargar, ejecutar esta función
        document.addEventListener('DOMContentLoaded', function() {
            cargarPublicaciones();
        });
    </script>

    <!-- SCRIPTS -->
    <script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/aos/aos.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Inicializar animaciones AOS
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>
