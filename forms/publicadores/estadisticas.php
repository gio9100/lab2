<?php
session_start();
require_once 'config-publicadores.php';

// Verificar sesión
if (!isset($_SESSION['publicador_id'])) {
    header('Location: ../inicio-sesion.php');
    exit();
}

$publicador_id = $_SESSION['publicador_id'];
$publicador_nombre = $_SESSION['publicador_nombre'];

// Query principal para obtener publicaciones y sus estadísticas
$query = "SELECT 
            p.id, 
            p.titulo, 
            p.fecha_creacion,
            p.estado,
            (SELECT COUNT(*) FROM comentarios c WHERE c.publicacion_id = p.id AND c.estado = 'activo') as num_comentarios,
            (SELECT COUNT(*) FROM likes l WHERE l.publicacion_id = p.id AND l.tipo = 'like') as num_likes,
            (SELECT COUNT(*) FROM likes l WHERE l.publicacion_id = p.id AND l.tipo = 'dislike') as num_dislikes,
            (SELECT COUNT(*) FROM leer_mas_tarde s WHERE s.publicacion_id = p.id) as num_guardados,
            (SELECT COUNT(*) FROM reportes r WHERE r.referencia_id = p.id AND r.tipo = 'publicacion') as num_reportes
          FROM publicaciones p
          WHERE p.publicador_id = ? 
          ORDER BY p.fecha_creacion DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $publicador_id);
$stmt->execute();
$result = $stmt->get_result();
$publicaciones = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas - Lab-Explorer</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- Main CSS -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">

    <style>
        .stats-badge {
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            min-width: 60px;
            justify-content: center;
        }
        .stats-badge.likes { background-color: #d1e7dd; color: #0f5132; }
        .stats-badge.dislikes { background-color: #f8d7da; color: #842029; }
        .stats-badge.saved { background-color: #cfe2ff; color: #084298; }
        .stats-badge.reports { background-color: #fff3cd; color: #664d03; }
        .stats-badge.comments { background-color: #e2e3e5; color: #41464b; }
    </style>
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
                    <div class="section-title" data-aos="fade-up">
                        <h2>Estadísticas de Publicaciones</h2>
                        <p class="text-muted">Monitorea el rendimiento y la interacción de tu contenido</p>
                    </div>
                    </div>

                    </div>
                    
                    <!-- 1. Tabla de Resumen de Interacciones (Moved Up) -->
                    <div class="admin-card mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-graph-up me-2"></i>
                                Resumen de Interacciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Publicación</th>
                                            <th class="text-center"><i class="bi bi-hand-thumbs-up-fill text-success" title="Me gusta"></i></th>
                                            <th class="text-center"><i class="bi bi-hand-thumbs-down-fill text-danger" title="No me gusta"></i></th>
                                            <th class="text-center"><i class="bi bi-bookmark-fill text-primary" title="Guardados"></i></th>
                                            <th class="text-center"><i class="bi bi-chat-dots-fill text-secondary" title="Comentarios"></i></th>
                                            <th class="text-center"><i class="bi bi-flag-fill text-warning" title="Reportes"></i></th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($publicaciones)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No tienes publicaciones aún.</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach($publicaciones as $pub): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= htmlspecialchars($pub['titulo']) ?></div>
                                                    <small class="text-muted"><?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?></small>
                                                    <?php if($pub['estado'] !== 'publicado'): ?>
                                                        <span class="badge bg-secondary ms-2"><?= ucfirst($pub['estado']) ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <span class="stats-badge likes"><?= $pub['num_likes'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="stats-badge dislikes"><?= $pub['num_dislikes'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="stats-badge saved"><?= $pub['num_guardados'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="stats-badge comments"><?= $pub['num_comentarios'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="stats-badge reports"><?= $pub['num_reportes'] ?></span>
                                                </td>
                                                <td class="text-center">
                                                    <button class="btn btn-sm btn-outline-info" onclick="verComentarios(<?= $pub['id'] ?>)">
                                                        <i class="bi bi-chat-text"></i> Ver Comentarios
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Gráficos Estadísticos (Moved Down) -->
                    <div class="row mb-4">
                        <div class="col-lg-8 mb-4 mb-lg-0">
                            <div class="admin-card h-100" data-aos="fade-up">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="bi bi-bar-chart-line me-2"></i>Top 5 Publicaciones</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="topPostsChart" style="max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="admin-card h-100" data-aos="fade-up" data-aos-delay="100">
                                <div class="card-header">
                                    <h5 class="card-title mb-0"><i class="bi bi-pie-chart me-2"></i>Interacción Global</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="interactionsChart" style="max-height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Ver Comentarios -->
    <div class="modal fade" id="modalComentarios" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Comentarios de la Publicación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="listaComentarios" class="d-flex flex-column gap-3">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor JS -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>

    <!-- Main JS (para el sidebar) -->
    <script src="../../assets/js/main.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Script Personalizado -->
    <script>
        // Inicializar AOS
        AOS.init();

        // Control del Sidebar...
        const sidebarToggle = document.getElementById('sidebarToggle');
        // ... (resto del código del sidebar existente se mantiene abajo, no lo rompas)

        // --- Lógica de Gráficos ---
        document.addEventListener('DOMContentLoaded', function() {
            // Preparar datos desde PHP
            <?php 
                // Procesar datos para gráficos
                $top_posts = array_slice($publicaciones, 0, 5); // Top 5
                
                $labels = [];
                $likes_data = [];
                $views_data = []; // Si tuviéramos vistas, por ahora usaremos comentarios como proxy de actividad
                
                $total_likes = 0;
                $total_comments = 0;
                $total_saved = 0;

                foreach($publicaciones as $p) {
                    $total_likes += $p['num_likes'];
                    $total_comments += $p['num_comentarios'];
                    $total_saved += $p['num_guardados'];
                }

                foreach($top_posts as $p) {
                    $labels[] = mb_strimwidth($p['titulo'], 0, 20, "...");
                    $likes_data[] = $p['num_likes'];
                }
            ?>

            const labels = <?= json_encode($labels) ?>;
            const likesData = <?= json_encode($likes_data) ?>;
            
            // Gráfico de Barras (Top Publicaciones)
            const ctxBar = document.getElementById('topPostsChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Me Gusta',
                        data: likesData,
                        backgroundColor: 'rgba(13, 202, 240, 0.6)',
                        borderColor: 'rgba(13, 202, 240, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Gráfico de Dona (Interacciones)
            const ctxDoughnut = document.getElementById('interactionsChart').getContext('2d');
            new Chart(ctxDoughnut, {
                type: 'doughnut',
                data: {
                    labels: ['Me Gusta', 'Comentarios', 'Guardados'],
                    datasets: [{
                        data: [<?= $total_likes ?>, <?= $total_comments ?>, <?= $total_saved ?>],
                        backgroundColor: [
                            'rgba(25, 135, 84, 0.7)', // Success (Likes)
                            'rgba(108, 117, 125, 0.7)', // Secondary (Comentarios)
                            'rgba(13, 110, 253, 0.7)'  // Primary (Guardados)
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                    }
                }
            });
        });

        // ... (código existente del sidebar y modal)

        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarWrapper = document.getElementById('sidebarWrapper');

        if (sidebarToggle && sidebarWrapper) {
            sidebarToggle.addEventListener('click', () => {
                sidebarWrapper.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        }

        if (sidebarClose && sidebarWrapper) {
            sidebarClose.addEventListener('click', () => {
                sidebarWrapper.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Función para cargar comentarios
        function verComentarios(publicacionId) {
            const modal = new bootstrap.Modal(document.getElementById('modalComentarios'));
            const container = document.getElementById('listaComentarios');
            
            modal.show();
            container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Cargando comentarios...</p></div>';

            // Fetch comentarios via AJAX
            // Vamos a usar una petición a un archivo auxiliar para obtener los comentarios en JSON
            // Como no tenemos una API JSON lista, vamos a crear un pequeño endpoint o usar el existente si es adaptable.
            // Por simplicidad, incrustaré un script PHP aquí mismo que actúe como API si recibe un parámetro POST.
            // O mejor, creo un archivo `obtener_comentarios_ajax.php` si no existe.
            // Para ser rápidos, haré la llamada a `forms/usuario.php` no funciona porque devuelve arrays en PHP, no JSON.
            // Crearé un endpoint simple en este mismo archivo o uno nuevo.
            
            // Usaremos un truco: fetch a este mismo archivo con un parámetro especial
            fetch(`obtener_comentarios.php?id=${publicacionId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length === 0) {
                        container.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-chat-square mb-2 display-6"></i><p>No hay comentarios en esta publicación.</p></div>';
                        return;
                    }
                    
                    let html = '';
                    data.forEach(c => {
                        html += `
                            <div class="card border-0 shadow-sm bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>${c.usuario_nombre}</strong>
                                        <small class="text-muted">${c.fecha_creacion}</small>
                                    </div>
                                    <p class="mb-0">${c.contenido}</p>
                                </div>
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    container.innerHTML = '<div class="alert alert-danger">Error al cargar comentarios</div>';
                });
        }
    </script>
</body>
</html>
