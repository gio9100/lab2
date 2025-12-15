<?php
// Historial de Publicaciones (Admin)
// Muestra un historial completo de todas las publicaciones con filtros

// Iniciar sesión
session_start();

// Incluir configuración y funciones de admin
require_once "config-admin.php";

// Verificar permisos de administrador
requerirAdmin();

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "lab_exp_db");

// Verificar conexión
if ($conn->connect_error) {
    die("ERROR DE CONEXIÓN: " . $conn->connect_error);
}

// Obtener filtros de la URL
$filtro_estado = $_GET['estado'] ?? '';
$filtro_publicador = $_GET['publicador'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_fecha_desde = $_GET['fecha_desde'] ?? '';
$filtro_fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construir consulta base
$query = "SELECT 
    p.id,
    p.titulo,
    p.estado,
    p.tipo,
    p.fecha_creacion,
    p.fecha_publicacion,
    p.vistas,
    p.mensaje_rechazo,
    pub.nombre as publicador_nombre,
    pub.email as publicador_email,
    c.nombre as categoria_nombre
FROM publicaciones p
LEFT JOIN publicadores pub ON p.publicador_id = pub.id
LEFT JOIN categorias c ON p.categoria_id = c.id
WHERE 1=1";

// Parámetros para la consulta preparada
$params = [];
$types = "";

// Aplicar filtros dinámicamente
if (!empty($filtro_estado)) {
    $query .= " AND p.estado = ?";
    $params[] = $filtro_estado;
    $types .= "s";
}

if (!empty($filtro_publicador)) {
    $query .= " AND p.publicador_id = ?";
    $params[] = intval($filtro_publicador);
    $types .= "i";
}

if (!empty($filtro_categoria)) {
    $query .= " AND p.categoria_id = ?";
    $params[] = intval($filtro_categoria);
    $types .= "i";
}

if (!empty($filtro_fecha_desde)) {
    $query .= " AND DATE(p.fecha_creacion) >= ?";
    $params[] = $filtro_fecha_desde;
    $types .= "s";
}

if (!empty($filtro_fecha_hasta)) {
    $query .= " AND DATE(p.fecha_creacion) <= ?";
    $params[] = $filtro_fecha_hasta;
    $types .= "s";
}

// Ordenar por fecha descendente
$query .= " ORDER BY p.fecha_creacion DESC";

// Preparar y ejecutar consulta
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$publicaciones = $result->fetch_all(MYSQLI_ASSOC);

// Calcular estadísticas
$total_publicaciones = count($publicaciones);
$total_publicadas = count(array_filter($publicaciones, fn($p) => $p['estado'] == 'publicado'));
$total_borradores = count(array_filter($publicaciones, fn($p) => $p['estado'] == 'borrador'));
$total_revision = count(array_filter($publicaciones, fn($p) => $p['estado'] == 'revision'));
$total_rechazadas = count(array_filter($publicaciones, fn($p) => $p['estado'] == 'rechazada'));

// Obtener listas para filtros (publicadores y categorías)
$publicadores_query = "SELECT id, nombre FROM publicadores ORDER BY nombre";
$publicadores_result = $conn->query($publicadores_query);
$lista_publicadores = $publicadores_result->fetch_all(MYSQLI_ASSOC);

$categorias_query = "SELECT id, nombre FROM categorias ORDER BY nombre";
$categorias_result = $conn->query($categorias_query);
$lista_categorias = $categorias_result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Publicaciones - Lab-Explora</title>
    
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
    
    <style>
        .historial-table { font-size: 0.9rem; }
        .historial-table td { vertical-align: middle; }
        .badge-estado { font-size: 0.75rem; padding: 0.35em 0.65em; }
    </style>
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logo-labexplora.png" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
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
                            <a href="../../pagina-principal.php" class="list-group-item list-group-item-action">
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
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="historial-publicaciones.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-clock-history me-2"></i>Historial de Publicaciones
                            </a>
                            <a href="./categorias/crear_categoria.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Contenido Derecho -->
                <div class="col-md-9">
                    <div class="section-title" data-aos="fade-up">
                        <h2><i class="bi bi-clock-history me-2"></i>Historial de Publicaciones</h2>
                        <p>Visualiza el historial completo de todas las publicaciones del sistema</p>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $total_publicaciones ?></h4>
                                    <small>Total</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $total_publicadas ?></h4>
                                    <small>Publicadas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $total_borradores ?></h4>
                                    <small>Borradores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= $total_revision ?></h4>
                                    <small>En Revisión</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-6 mb-3">
                            <div class="stat-card danger">
                                <div class="stat-content text-center">
                                    <h4><?= $total_rechazadas ?></h4>
                                    <small>Rechazadas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-funnel me-2"></i>Filtros de Búsqueda</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="historial-publicaciones.php">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label">Estado</label>
                                        <select name="estado" class="form-select">
                                            <option value="">Todos los estados</option>
                                            <option value="publicado" <?= $filtro_estado == 'publicado' ? 'selected' : '' ?>>Publicado</option>
                                            <option value="borrador" <?= $filtro_estado == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                            <option value="revision" <?= $filtro_estado == 'revision' ? 'selected' : '' ?>>En Revisión</option>
                                            <option value="rechazada" <?= $filtro_estado == 'rechazada' ? 'selected' : '' ?>>Rechazada</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Publicador</label>
                                        <select name="publicador" class="form-select">
                                            <option value="">Todos los publicadores</option>
                                            <?php foreach($lista_publicadores as $pub): ?>
                                            <option value="<?= $pub['id'] ?>" <?= $filtro_publicador == $pub['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($pub['nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Categoría</label>
                                        <select name="categoria" class="form-select">
                                            <option value="">Todas las categorías</option>
                                            <?php foreach($lista_categorias as $cat): ?>
                                            <option value="<?= $cat['id'] ?>" <?= $filtro_categoria == $cat['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat['nombre']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Desde</label>
                                        <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($filtro_fecha_desde) ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Hasta</label>
                                        <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($filtro_fecha_hasta) ?>">
                                    </div>

                                    <div class="col-md-9 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="bi bi-search me-1"></i>Filtrar
                                        </button>
                                        <a href="historial-publicaciones.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>Limpiar
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de Historial -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-table me-2"></i>Historial Completo
                                <span class="badge bg-primary"><?= $total_publicaciones ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($publicaciones)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox display-4 text-muted"></i>
                                    <h5 class="text-muted mt-3">No se encontraron publicaciones</h5>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive d-none d-lg-block">
                                    <table class="admin-table historial-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 60px;">ID</th>
                                                <th style="width: 25%;">Título</th>
                                                <th style="width: 15%;">Publicador</th>
                                                <th style="width: 12%;">Categoría</th>
                                                <th style="width: 10%;">Estado</th>
                                                <th style="width: 12%;">Fecha</th>
                                                <th style="width: 8%;">Vistas</th>
                                                <th style="width: 10%;">Rechazo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($publicaciones as $pub): ?>
                                            <tr>
                                                <td><strong>#<?= $pub['id'] ?></strong></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($pub['titulo']) ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?= htmlspecialchars($pub['tipo']) ?></small>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($pub['publicador_nombre']) ?>
                                                    <br>
                                                    <small class="text-muted"><?= $pub['publicador_email'] ?></small>
                                                </td>
                                                <td><?= htmlspecialchars($pub['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = match($pub['estado']) {
                                                        'publicado' => 'bg-success',
                                                        'borrador' => 'bg-secondary',
                                                        'revision' => 'bg-warning',
                                                        'rechazada' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?= $badge_class ?> badge-estado">
                                                        <?= ucfirst($pub['estado']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?></small>
                                                    <br>
                                                    <small class="text-muted"><?= date('H:i', strtotime($pub['fecha_creacion'])) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="bi bi-eye me-1"></i>
                                                        <?= $pub['vistas'] ?? 0 ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($pub['estado'] == 'rechazada' && !empty($pub['mensaje_rechazo'])): ?>
                                                        <button class="btn btn-outline-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalVerRechazo"
                                                                data-titulo="<?= htmlspecialchars($pub['titulo']) ?>"
                                                                data-mensaje="<?= htmlspecialchars($pub['mensaje_rechazo']) ?>">
                                                            <i class="bi bi-exclamation-circle"></i> Ver
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Vista Móvil -->
                                <div class="d-lg-none">
                                    <?php foreach($publicaciones as $pub): ?>
                                    <div class="card mb-3 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h6 class="mb-1"><strong><?= htmlspecialchars($pub['titulo']) ?></strong></h6>
                                                    <small class="text-muted">#<?= $pub['id'] ?></small>
                                                </div>
                                                <span class="badge bg-primary"><?= ucfirst($pub['estado']) ?></span>
                                            </div>
                                            <p class="mb-1"><small><strong>Publicador:</strong> <?= htmlspecialchars($pub['publicador_nombre']) ?></small></p>
                                            <p class="mb-1"><small><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?></small></p>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Rechazo -->
    <div class="modal fade" id="modalVerRechazo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-circle me-2"></i>Motivo de Rechazo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Publicación:</strong> <span id="modalTitulo"></span></p>
                    <hr>
                    <p><strong>Motivo:</strong></p>
                    <div class="alert alert-danger" id="modalMensaje"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>

    <script>
        AOS.init();
        
        const modalVerRechazo = document.getElementById('modalVerRechazo');
        modalVerRechazo.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('modalTitulo').textContent = button.getAttribute('data-titulo');
            document.getElementById('modalMensaje').textContent = button.getAttribute('data-mensaje');
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
