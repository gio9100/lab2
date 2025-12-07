<?php
// Listar Categorías (Admin)
// Muestra todas las categorías existentes y permite gestionarlas

// Iniciar sesión
session_start();

// Incluir configuración y funciones de admin
require_once '../config-admin.php';
requerirAdmin();

// Obtener datos del admin actual
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];
$stats = obtenerEstadisticasAdmin($conn);

// Incluir clases de categorías
include_once 'config-categorias.php';
include_once 'categoria.php';

// Inicializar conexión y objeto categoría
$database = new Database();
$db = $database->getConnection();
$categoria = new Categoria($db);

// Obtener todas las categorías
$stmt = $categoria->leer();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - Lab-Explora</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Principal -->
    <link href="../../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css-admins/admin.css">
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
<<<<<<< HEAD
                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>
                    <a href="../../../pagina-principal.php" class="logo d-flex align-items-end">
                        <img src="../../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                        <h1 class="sitename">Lab-Explorer</h1><span></span>
                    </a>
                </div>
=======
                <a href="../../../index.php" class="logo d-flex align-items-end">
                    <img src="../../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>
>>>>>>> fb0fcd8bcbd77da65d4cfafc071306162a214b0c
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="../perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</a>
                        <a href="../logout-admin.php" class="logout-btn">Cerrar sesión</a>
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
                <!-- Sidebar -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php 
                    $path_prefix = '../'; 
                    include '../sidebar-admin.php'; 
                    ?>
                </div>

                <!-- Contenido Derecho -->
                <div class="col-md-9">
                    
                    <div class="section-title" data-aos="fade-up">
                        <h2>Categorías de Laboratorio Clínico</h2>
                        <p>Gestiona las categorías para organizar las publicaciones</p>
                    </div>
                    
                    <!-- Botón Nueva Categoría -->
                    <div class="d-flex justify-content-between mb-4" data-aos="fade-up" data-aos-delay="100">
                        <a href="crear_categoria.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
                        </a>
                    </div>
                    
                    <!-- Grid de Categorías -->
                    <div class="row">
                        <?php
                        if ($stmt->rowCount() > 0) {
                            $delay = 100;
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $estado_badge = $row['estado'] == 'activo' ? 'bg-success' : 'bg-secondary';
                                $icono = !empty($row['icono']) ? $row['icono'] : 'bi-flask';
                                ?>
                                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?= $delay ?>">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <h5 class="card-title">
                                                    <i class="bi <?php echo $icono; ?> me-2" style="color: <?php echo $row['color']; ?>"></i>
                                                    <?php echo htmlspecialchars($row['nombre']); ?>
                                                </h5>
                                                <span class="badge <?php echo $estado_badge; ?>">
                                                    <?php echo ucfirst($row['estado']); ?>
                                                </span>
                                            </div>
                                            <p class="card-text text-muted">
                                                <?php echo htmlspecialchars($row['descripcion'] ?? 'Sin descripción'); ?>
                                            </p>
                                            <div class="mt-3">
                                                <small class="text-muted">
                                                    <i class="bi bi-link-45deg"></i> Slug: <?= $row['slug'] ?>
                                                </small><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3"></i> Creado: <?= date('d/m/Y', strtotime($row['fecha_creacion'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <div class="d-grid gap-2">
                                                <a href="editar_categoria.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                                    <i class="bi bi-pencil-square me-1"></i> Editar
                                                </a>
                                                <a href="eliminar_categoria.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" 
                                                   onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                                    <i class="bi bi-trash me-1"></i> Eliminar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $delay += 50;
                            }
                        } else {
                            ?>
                            <div class="col-12" data-aos="fade-up">
                                <div class="alert alert-info text-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay categorías registradas. 
                                    <a href="crear_categoria.php" class="alert-link">Crear la primera categoría</a>
                                </div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../../assets/vendor/aos/aos.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>