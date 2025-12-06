<?php
// Gestión de publicaciones (Admin)

// Iniciar sesión
session_start();

// Incluir configuración y notificaciones
require_once "config-admin.php";
require_once "notificar_publicador.php";

// Verificar permisos de administrador
requerirAdmin();

// Obtener datos del admin logueado
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Conexión a base de datos
$conn = new mysqli("localhost", "root", "", "lab_exp_db");

// Verificar conexión
if ($conn->connect_error) {
    die("ERROR DE CONEXIÓN: " . $conn->connect_error);
}

// Obtener estadísticas generales
$stats = obtenerEstadisticasAdmin($conn);

// Procesar cambio de estado
if (isset($_POST['cambiar_estado'])) {
    
    $publicacion_id = intval($_POST['publicacion_id']);
    $nuevo_estado = $_POST['nuevo_estado'];
    
    // Actualizar estado
    $query = "UPDATE publicaciones SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $nuevo_estado, $publicacion_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "✅ Estado actualizado a " . ucfirst($nuevo_estado);
        $_SESSION['tipo_mensaje'] = "success";
        
        // Si es rechazo, pedir motivo antes de notificar
        if ($nuevo_estado == 'rechazada') {
            $_SESSION['pedir_motivo_id'] = $publicacion_id;
        } else {
            // Si no es rechazo, notificar inmediatamente
            $query_pub = "SELECT p.titulo, p.tipo, pub.email, pub.nombre 
                          FROM publicaciones p 
                          JOIN publicadores pub ON p.publicador_id = pub.id 
                          WHERE p.id = ?";
            $stmt_pub = $conn->prepare($query_pub);
            $stmt_pub->bind_param("i", $publicacion_id);
            $stmt_pub->execute();
            $result_pub = $stmt_pub->get_result();
            
            if ($result_pub && $result_pub->num_rows > 0) {
                $datos = $result_pub->fetch_assoc();
                
                enviarNotificacionPublicador(
                    $datos['email'],
                    $datos['nombre'],
                    $datos['titulo'],
                    $datos['tipo'],
                    $nuevo_estado,
                    $publicacion_id,
                    $conn
                );
            }
            $stmt_pub->close();

            // Limpiar mensaje de rechazo previo si existe
            $conn->query("UPDATE publicaciones SET mensaje_rechazo = NULL WHERE id = $publicacion_id");
        }
    } else {
        $_SESSION['mensaje'] = "❌ Error al actualizar estado";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    $stmt->close();
    
    header("Location: gestionar-publicaciones.php");
    exit;
}

// Procesar motivo de rechazo
if (isset($_POST['guardar_motivo'])) {
    
    $publicacion_id = intval($_POST['publicacion_id']);
    $mensaje = $_POST['mensaje_rechazo'];
    
    // Guardar motivo
    $query = "UPDATE publicaciones SET mensaje_rechazo = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $mensaje, $publicacion_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "✅ Motivo de rechazo guardado y notificado correctamente";
        $_SESSION['tipo_mensaje'] = "success";

        // Notificar rechazo con motivo
        $query_pub = "SELECT p.titulo, p.tipo, pub.email, pub.nombre 
                      FROM publicaciones p 
                      JOIN publicadores pub ON p.publicador_id = pub.id 
                      WHERE p.id = ?";
        $stmt_pub = $conn->prepare($query_pub);
        $stmt_pub->bind_param("i", $publicacion_id);
        $stmt_pub->execute();
        $result_pub = $stmt_pub->get_result();

        if ($result_pub && $result_pub->num_rows > 0) {
            $datos = $result_pub->fetch_assoc();
            
            enviarNotificacionPublicador(
                $datos['email'],
                $datos['nombre'],
                $datos['titulo'],
                $datos['tipo'],
                'rechazada',
                $publicacion_id,
                $conn
            );
        }
        $stmt_pub->close();

    } else {
        $_SESSION['mensaje'] = "❌ Error al guardar motivo";
        $_SESSION['tipo_mensaje'] = "danger";
    }
    $stmt->close();
    
    header("Location: gestionar-publicaciones.php");
    exit;
}

// Eliminar publicación
if (isset($_GET['eliminar'])) {
    $publicacion_id = intval($_GET['eliminar']);
    
    $query = "DELETE FROM publicaciones WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("i", $publicacion_id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "✅ Publicación eliminada correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al eliminar publicación";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        $stmt->close();
    }
    header("Location: gestionar-publicaciones.php");
    exit;
}

// Eliminar todas las publicaciones
if (isset($_GET['eliminar_todas'])) {
    $query = "DELETE FROM publicaciones";
    
    if ($conn->query($query)) {
        $_SESSION['mensaje'] = "✅ Todas las publicaciones han sido eliminadas correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        $_SESSION['mensaje'] = "❌ Error al eliminar todas las publicaciones: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "danger";
    }
    
    header("Location: gestionar-publicaciones.php");
    exit;
}

// Obtener todas las publicaciones
$query = "SELECT 
    p.id, 
    p.titulo, 
    p.estado, 
    p.tipo,
    p.fecha_creacion,
    p.vistas,
    p.contenido,
    p.categoria_id,
    pub.nombre as publicador_nombre,
    pub.email as publicador_email,
    c.nombre as categoria_nombre
FROM publicaciones p
LEFT JOIN publicadores pub ON p.publicador_id = pub.id
LEFT JOIN categorias c ON p.categoria_id = c.id
ORDER BY p.fecha_creacion DESC";

$result = $conn->query($query);
$publicaciones = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Publicaciones - Lab-Explorer</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Principal -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <style>
        .action-buttons .btn { margin: 2px; }
        .badge-estado { font-size: 0.75rem; }
        .table-actions { white-space: nowrap; }
    </style>
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

    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- Sidebar -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../../index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Página principal
                            </a>
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-house me-2"></i>Panel Principal
                            </a>
                            <a href="gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-badge me-2"></i>Usuarios Registrados
                            </a>
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="historial-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-clock-history me-2"></i>Historial de Publicaciones
                            </a>
                            <a href="./categorias/listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Resumen Rápido -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Resumen Publicaciones</h6>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <small class="text-muted">Total: <?= count($publicaciones) ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Publicadas: <?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'publicado')) ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Borradores: <?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'borrador')) ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">En Revisión: <?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'revision')) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    <!-- Mensajes -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert-message <?= $_SESSION['tipo_mensaje'] == 'success' ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php 
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Publicaciones</h2>
                        <p>Administra todas las publicaciones del sistema</p>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= count($publicaciones) ?></h4>
                                    <small>Total Publicaciones</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'publicado')) ?></h4>
                                    <small>Publicadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'borrador')) ?></h4>
                                    <small>Borradores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= count(array_filter($publicaciones, fn($p) => $p['estado'] == 'revision')) ?></h4>
                                    <small>En Revisión</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-funnel me-2"></i>Filtros y Búsqueda
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" placeholder="Buscar publicación..." 
                                           onkeyup="filtrarPublicaciones(this.value)">
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" onchange="filtrarPorEstado(this.value)">
                                        <option value="">Todos los estados</option>
                                        <option value="publicado">Publicado</option>
                                        <option value="borrador">Borrador</option>
                                        <option value="revision">En Revisión</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" onchange="filtrarPorCategoria(this.value)">
                                        <option value="">Todas las categorías</option>
                                        <?php
                                        $categorias_query = "SELECT id, nombre FROM categorias";
                                        $categorias_result = $conn->query($categorias_query);
                                        if ($categorias_result) {
                                            while($cat = $categorias_result->fetch_assoc()) {
                                                echo "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-secondary w-100" onclick="limpiarFiltros()">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Publicaciones -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-file-text me-2"></i>
                                Todas las Publicaciones
                                <span class="badge bg-primary"><?= count($publicaciones) ?></span>
                            </h5>
                            <a href="gestionar-publicaciones.php?eliminar_todas=1" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás SEGURO de que quieres eliminar TODAS las publicaciones del sistema? Esta acción no se puede deshacer.')">
                                <i class="bi bi-trash"></i> Eliminar Todo
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if(empty($publicaciones)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <h5 class="text-muted mt-3">No hay publicaciones</h5>
                                    <p class="text-muted">Los publicadores aún no han creado contenido</p>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>Título</th>
                                                <th>Publicador</th>
                                                <th>Categoría</th>
                                                <th>Estado</th>
                                                <th>Fecha</th>
                                                <th>Vistas</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($publicaciones as $publicacion): ?>
                                            <tr class="fila-publicacion" 
                                                data-estado="<?= $publicacion['estado'] ?>"
                                                data-categoria="<?= $publicacion['categoria_id'] ?>"
                                                data-titulo="<?= htmlspecialchars(strtolower($publicacion['titulo'])) ?>">
                                                <td>
                                                    <strong><?= htmlspecialchars($publicacion['titulo']) ?></strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($publicacion['tipo']) ?> • 
                                                        <?php 
                                                        $contenido_preview = $publicacion['contenido'] ?? '';
                                                        if (!empty($contenido_preview)) {
                                                            $contenido_limpio = strip_tags($contenido_preview);
                                                            echo substr($contenido_limpio, 0, 80) . '...';
                                                        } else {
                                                            echo 'Sin contenido...';
                                                        }
                                                        ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($publicacion['publicador_nombre']) ?>
                                                    <br>
                                                    <small class="text-muted"><?= $publicacion['publicador_email'] ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($publicacion['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                                <td>
                                                    <form method="POST" class="d-inline" id="form-estado-<?= $publicacion['id'] ?>">
                                                        <input type="hidden" name="publicacion_id" value="<?= $publicacion['id'] ?>">
                                                        <input type="hidden" name="mensaje_rechazo" id="mensaje-rechazo-<?= $publicacion['id'] ?>" value="">
                                                        <select name="nuevo_estado" class="form-select form-select-sm" 
                                                                onchange="cambiarEstado(this, <?= $publicacion['id'] ?>)" style="width: 140px;">
                                                            <option value="publicado" <?= $publicacion['estado'] == 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                                            <option value="borrador" <?= $publicacion['estado'] == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                                            <option value="revision" <?= $publicacion['estado'] == 'revision' ? 'selected' : '' ?>>En Revisión</option>
                                                            <option value="rechazada" <?= $publicacion['estado'] == 'rechazada' ? 'selected' : '' ?>>Rechazada</option>
                                                        </select>
                                                        <input type="hidden" name="cambiar_estado" value="1">
                                                    </form>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y H:i', strtotime($publicacion['fecha_creacion'])) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bi bi-eye me-1"></i>
                                                        <?= $publicacion['vistas'] ?? 0 ?>
                                                    </span>
                                                </td>
                                                <td class="table-actions">
                                                    <div class="action-buttons">
                                                        <a href="../../ver-publicacion-admins.php?id=<?= $publicacion['id'] ?>" 
                                                           target="_blank" class="btn btn-outline-info btn-sm" title="Ver">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="editar-publicacion.php?id=<?= $publicacion['id'] ?>" 
                                                           class="btn btn-outline-warning btn-sm" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="gestionar-publicaciones.php?eliminar=<?= $publicacion['id'] ?>" 
                                                           class="btn btn-outline-danger btn-sm" title="Eliminar"
                                                           onclick="return confirm('¿Estás seguro de eliminar esta publicación?')">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
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

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        // Inicializar AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Funciones de filtrado
        function filtrarPublicaciones(termino) {
            const filas = document.querySelectorAll('.fila-publicacion');
            const busqueda = termino.toLowerCase();
            
            filas.forEach(fila => {
                const texto = fila.dataset.titulo;
                if (texto.includes(busqueda)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        function filtrarPorEstado(estado) {
            const filas = document.querySelectorAll('.fila-publicacion');
            filas.forEach(fila => {
                if (estado === '' || fila.dataset.estado === estado) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        function filtrarPorCategoria(categoriaId) {
            const filas = document.querySelectorAll('.fila-publicacion');
            filas.forEach(fila => {
                if (categoriaId === '' || fila.dataset.categoria === categoriaId) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        }

        function limpiarFiltros() {
            document.querySelectorAll('select').forEach(select => select.value = '');
            document.querySelector('input[type="text"]').value = '';
            document.querySelectorAll('.fila-publicacion').forEach(fila => fila.style.display = '');
        }

        // Variables globales
        let modalRechazo = null;
        let publicacionActual = null;
        let selectActual = null;

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('modalRechazo');
            if (modalEl) {
                modalRechazo = new bootstrap.Modal(modalEl);
                
                // Limpiar y resetear cuando se cierra el modal
                modalEl.addEventListener('hidden.bs.modal', function () {
                    if (selectActual && publicacionActual) {
                        const fila = selectActual.closest('tr');
                        const estadoOriginal = fila.dataset.estado;
                        if (estadoOriginal && selectActual.value === 'rechazada') {
                            selectActual.value = estadoOriginal;
                        }
                    }
                    
                    publicacionActual = null;
                    selectActual = null;
                    document.getElementById('mensajeRechazo').value = '';
                });
            }
        });
        
        // Función para cambiar estado
        function cambiarEstado(selectElement, publicacionId) {
            const form = document.getElementById('form-estado-' + publicacionId);
            if (form) form.submit();
        }
    </script>

    <?php if(isset($_SESSION['pedir_motivo_id'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const id = <?= $_SESSION['pedir_motivo_id'] ?>;
            document.getElementById('modal_publicacion_id').value = id;
            
            const modalEl = document.getElementById('modalRechazo');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            
            document.getElementById('mensajeRechazo').focus();
        });
    </script>
    <?php 
        unset($_SESSION['pedir_motivo_id']); 
    endif; 
    ?>

    <!-- Modal Rechazo -->
    <div class="modal fade" id="modalRechazo" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Motivo del Rechazo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formRechazo" method="POST" action="gestionar-publicaciones.php">
                    <input type="hidden" name="guardar_motivo" value="1">
                    <input type="hidden" name="publicacion_id" id="modal_publicacion_id" value="">
                    
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <small><i class="bi bi-info-circle me-1"></i> La publicación ya ha sido marcada como <strong>Rechazada</strong>. Por favor indica el motivo.</small>
                        </div>
                        <div class="mb-3">
                            <label for="mensajeRechazo" class="form-label">
                                <strong>Motivo del rechazo:</strong>
                            </label>
                            <textarea class="form-control" name="mensaje_rechazo" id="mensajeRechazo" rows="4" 
                                      placeholder="Explica por qué se rechaza esta publicación..." required></textarea>
                            <small class="text-muted">Este mensaje será visible para el publicador</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Omitir mensaje</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>Guardar Motivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php 
$conn->close();
?>
