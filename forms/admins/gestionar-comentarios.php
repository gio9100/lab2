<?php
// Gestionar Comentarios (Admin)
// Permite a los administradores moderar y eliminar comentarios de usuarios

// Iniciar sesión
session_start();

// Incluir configuración y funciones de admin
require_once "config-admin.php";

// Verificar permisos de administrador
requerirAdmin();

// Obtener datos del admin actual
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Eliminar comentario
    if (isset($_POST['eliminar_comentario'])) {
        $id = intval($_POST['comentario_id']);
        
        // Llamar a función de eliminación
        if (eliminarComentarioAdmin($id, $conn)) {
            $mensaje = "Comentario eliminado correctamente.";
            $exito = true;
        } else {
            $mensaje = "Error al eliminar el comentario.";
            $exito = false;
        }
    }
}

// Obtener todos los comentarios para listar
$comentarios = obtenerTodosComentarios($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Comentarios - Lab-Explorer</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- CSS Vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
    
    <!-- CSS Principal -->
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

    <!-- Contenido Principal -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">
                
                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            <a href="gestionar-reportes.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-flag me-2"></i>Reportes
                            </a>
                            <a href="gestionar-comentarios.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-chat-square-text me-2"></i>Comentarios
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contenido Derecho -->
                <div class="col-md-9">
                    
                    <!-- Mensajes de Alerta -->
                    <?php if(isset($mensaje)): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>

                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header info-header">
                            <h5 class="card-title mb-0"><i class="bi bi-chat-dots me-2"></i>Todos los Comentarios</h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($comentarios)): ?>
                                <p class="text-muted">No hay comentarios activos.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Publicación</th>
                                                <th>Contenido</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($comentarios as $com): ?>
                                            <tr>
                                                <td><?= date('d/m/Y H:i', strtotime($com['fecha_creacion'])) ?></td>
                                                <td><?= htmlspecialchars($com['usuario_nombre']) ?></td>
                                                <td><?= htmlspecialchars($com['publicacion_titulo']) ?></td>
                                                <td><?= htmlspecialchars(substr($com['contenido'], 0, 50)) ?>...</td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <a href="../../ver-publicacion.php?id=<?= $com['publicacion_id'] ?>" target="_blank" class="btn btn-info btn-sm" title="Ver Publicación">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        
                                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este comentario?');">
                                                            <input type="hidden" name="comentario_id" value="<?= $com['id'] ?>">
                                                            <button type="submit" name="eliminar_comentario" class="btn btn-danger btn-sm" title="Eliminar Comentario">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
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

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script>
        // Inicializar AOS
        AOS.init();
        
        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.addEventListener('click', e => e.target.parentElement.style.display = 'none');
        });
    </script>
</body>
</html>
