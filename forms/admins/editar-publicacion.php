<?php
// Editar publicación (Admin)
// Permite a los administradores modificar el contenido y estado de una publicación

// Iniciar sesión
session_start();

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Establecer charset a UTF-8
$conn->set_charset("utf8mb4");

// Verificar si el usuario es administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] == '') {
    header("Location: login-admin.php");
    exit;
}

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// Verificar si se proporcionó un ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensaje'] = "No se especificó una publicación para editar";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: gestionar-publicaciones.php");
    exit;
}

// Obtener ID de la publicación
$publicacion_id = intval($_GET['id']);

// Procesar formulario de actualización
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['actualizar_publicacion'])) {
    
    // Obtener y limpiar datos
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $categoria_id = intval($_POST['categoria_id']);
    $estado = $_POST['estado'];
    
    // Actualizar publicación
    $query = "UPDATE publicaciones SET 
              titulo = ?, 
              contenido = ?, 
              categoria_id = ?, 
              estado = ?, 
              fecha_actualizacion = NOW()
              WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisi", $titulo, $contenido, $categoria_id, $estado, $publicacion_id);
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Publicación actualizada correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        header("Location: gestionar-publicaciones.php");
        exit;
    } else {
        $_SESSION['mensaje'] = "Error al actualizar la publicación: " . $conn->error;
        $_SESSION['tipo_mensaje'] = "danger";
    }
}

// Obtener datos de la publicación
$query = "SELECT p.*, 
          pub.nombre as publicador_nombre,
          c.nombre as categoria_nombre
          FROM publicaciones p
          LEFT JOIN publicadores pub ON p.publicador_id = pub.id
          LEFT JOIN categorias c ON p.categoria_id = c.id
          WHERE p.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si existe la publicación
if ($result->num_rows === 0) {
    $_SESSION['mensaje'] = "La publicación no existe";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: gestionar-publicaciones.php");
    exit;
}

$publicacion = $result->fetch_assoc();

// Obtener categorías activas para el select
$query_categorias = "SELECT id, nombre FROM categorias WHERE (estado = 'activa' OR estado = 'activo' OR estado IS NULL OR estado = '') ORDER BY nombre";
$result_categorias = $conn->query($query_categorias);
$categorias = $result_categorias->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - Lab-Explora</title>
    
    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS Principal -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <style>
        .form-control, .form-select {
            border-radius: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-secondary {
            background: #6c757d;
            border: none;
        }
        textarea.form-control {
            min-height: 300px;
        }
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
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
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
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    <!-- Mensajes -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Editar Publicación</h2>
                        <p>Modifica los datos de la publicación</p>
                    </div>

                    <!-- Formulario -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header warning-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-pencil me-2"></i>
                                Editando: <?= htmlspecialchars($publicacion['titulo']) ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                
                                <div class="mb-3">
                                    <label class="form-label">Título *</label>
                                    <input type="text" 
                                           name="titulo" 
                                           class="form-control" 
                                           value="<?= htmlspecialchars($publicacion['titulo']) ?>" 
                                           required>
                                    <small class="text-muted">El título de la publicación</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Contenido *</label>
                                    <textarea name="contenido" 
                                              class="form-control" 
                                              rows="10" 
                                              required><?= htmlspecialchars($publicacion['contenido']) ?></textarea>
                                    <small class="text-muted">El contenido completo de la publicación</small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Categoría *</label>
                                            <select name="categoria_id" class="form-select" required>
                                                <option value="">Selecciona una categoría</option>
                                                <?php foreach($categorias as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" 
                                                        <?= $publicacion['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['nombre']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estado *</label>
                                            <select name="estado" class="form-select" required>
                                                <option value="publicada" <?= $publicacion['estado'] == 'publicada' ? 'selected' : '' ?>>Publicada</option>
                                                <option value="borrador" <?= $publicacion['estado'] == 'borrador' ? 'selected' : '' ?>>Borrador</option>
                                                <option value="revision" <?= $publicacion['estado'] == 'revision' ? 'selected' : '' ?>>En Revisión</option>
                                                <option value="rechazada" <?= $publicacion['estado'] == 'rechazada' ? 'selected' : '' ?>>Rechazada</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <strong>Información:</strong><br>
                                    Publicador: <?= htmlspecialchars($publicacion['publicador_nombre']) ?><br>
                                    Fecha de creación: <?= date('d/m/Y H:i', strtotime($publicacion['fecha_publicacion'])) ?><br>
                                    Vistas: <?= $publicacion['vistas'] ?>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="gestionar-publicaciones.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </a>
                                    <button type="submit" name="actualizar_publicacion" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Actualizar Publicación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer id="footer" class="footer dark-background">
        <div class="container">
            <h3 class="sitename">Lab-Explora</h3>
            <p>Panel de Administración</p>
            <div class="copyright">
                <span>Copyright</span> <strong class="px-1 sitename">Lab-Explora</strong> <span>Todos los derechos reservados</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        // Inicializar AOS
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    </script>

</body>
</html>
<?php
// Cerrar conexión
$conn->close();
?>
