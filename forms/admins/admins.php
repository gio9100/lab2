<?php
// Gestión de administradores (solo para superadmins)
// Permite crear, editar, cambiar contraseñas y eliminar otros admins

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

// Claves secretas para registro de admins
define('CLAVE_ADMIN', 'labexplorer2025');
define('CLAVE_SUPERADMIN', 'superlabexplorer2025');

// Verificar si el usuario es superadmin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] != 'superadmin') {
    $_SESSION['mensaje'] = "No tienes permisos para acceder a esta sección";
    $_SESSION['tipo_mensaje'] = "danger";
    header("Location: index-admin.php");
    exit;
}

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// Procesar formulario POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Crear nuevo administrador
    if (isset($_POST['crear_admin'])) {
        // Obtener y limpiar datos
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $nivel = $_POST['nivel'];
        $clave_secreta = trim($_POST['clave_secreta']);
        
        // Validar clave secreta
        $clave_valida = false;
        
        if ($nivel == 'admin' && $clave_secreta == CLAVE_ADMIN) {
            $clave_valida = true;
        } elseif ($nivel == 'superadmin' && $clave_secreta == CLAVE_SUPERADMIN) {
            $clave_valida = true;
        }
        
        if ($clave_valida) {
            // Hash de contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo admin
            $query = "INSERT INTO admins (nombre, email, password, nivel, fecha_registro) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $nivel);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Administrador creado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al crear administrador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
            $_SESSION['mensaje'] = "Clave secreta incorrecta para el nivel seleccionado";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: admins.php");
        exit;
    }
    
    // Editar administrador
    if (isset($_POST['editar_admin'])) {
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $nivel = $_POST['nivel'];
        
        // Actualizar datos
        $query = "UPDATE admins SET nombre=?, email=?, nivel=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $nombre, $email, $nivel, $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Administrador actualizado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al actualizar administrador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: admins.php");
        exit;
    }
    
    // Cambiar contraseña
    if (isset($_POST['cambiar_password'])) {
        $id = intval($_POST['id']);
        $nueva_password = trim($_POST['nueva_password']);
        
        // Hash de nueva contraseña
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        // Actualizar contraseña
        $query = "UPDATE admins SET password=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $password_hash, $id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Contraseña actualizada correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al cambiar contraseña";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: admins.php");
        exit;
    }
    
    // Eliminar administrador
    if (isset($_POST['eliminar_admin'])) {
        $id = intval($_POST['id']);
        
        // Evitar auto-eliminación
        if ($id == $admin_id) {
            $_SESSION['mensaje'] = "No puedes eliminarte a ti mismo";
            $_SESSION['tipo_mensaje'] = "danger";
        } else {
            // Eliminar registro
            $query = "DELETE FROM admins WHERE id=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Administrador eliminado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al eliminar administrador";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
        
        header("Location: admins.php");
        exit;
    }
}

// Obtener lista de administradores
$query = "SELECT * FROM admins ORDER BY fecha_registro DESC";
$result = $conn->query($query);
$admins = $result->fetch_all(MYSQLI_ASSOC);

// Obtener estadísticas por nivel
$query_stats = "SELECT nivel, COUNT(*) as total FROM admins GROUP BY nivel";
$result_stats = $conn->query($query_stats);

$total_admins = 0;
$total_superadmins = 0;

while ($row = $result_stats->fetch_assoc()) {
    if ($row['nivel'] == 'admin') {
        $total_admins = $row['total'];
    } elseif ($row['nivel'] == 'superadmin') {
        $total_superadmins = $row['total'];
    }
}

$total_general = $total_admins + $total_superadmins;

// Cargar datos para edición si es necesario
$admin_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    
    $query = "SELECT * FROM admins WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin_editar = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores - Lab-Explorer</title>
    
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
        .action-buttons .btn { 
            margin: 2px; 
        }
        .badge-nivel {
            font-size: 0.85rem;
            padding: 0.4em 0.8em;
        }
        .clave-info {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
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
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            <a href="./categorias/listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <a href="admins.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                        </div>
                        
                        <!-- Resumen Rápido -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Resumen</h6>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <small class="text-muted">Total: <?= $total_general ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Admins: <?= $total_admins ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">SuperAdmins: <?= $total_superadmins ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    <!-- Mensajes de Sesión -->
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
                        <h2>Gestión de Administradores</h2>
                        <p>Administra los usuarios con acceso al panel de administración</p>
                    </div>

                    <!-- Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up">
                        <div class="col-md-4 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $total_general ?></h4>
                                    <small>Total Administradores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $total_admins ?></h4>
                                    <small>Admins</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $total_superadmins ?></h4>
                                    <small>SuperAdmins</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario Crear/Editar -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header <?= $admin_editar ? 'warning-header' : 'primary-header' ?>">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-<?= $admin_editar ? 'pencil' : 'plus' ?> me-2"></i>
                                <?= $admin_editar ? 'Editar Administrador' : 'Nuevo Administrador' ?>
                            </h5>
                            <?php if($admin_editar): ?>
                                <a href="admins.php" class="btn btn-sm btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <?php if(!$admin_editar): ?>
                            <!-- Formulario Creación -->
                            <form method="POST">
                                <input type="hidden" name="crear_admin" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre Completo *</label>
                                            <input type="text" name="nombre" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña *</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nivel *</label>
                                            <select name="nivel" class="form-select" required>
                                                <option value="admin">Admin</option>
                                                <option value="superadmin">SuperAdmin</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Clave Secreta *</label>
                                    <input type="password" name="clave_secreta" class="form-control" 
                                           placeholder="Ingresa la clave secreta" required>
                                </div>

                                <div class="clave-info">
                                    <strong><i class="bi bi-info-circle me-2"></i>Información sobre claves secretas:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Las claves son diferentes para crear un <strong>Admin</strong> y un <strong>SuperAdmin</strong> y deben mantenerse en secreto</li>
                                    </ul>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-1"></i>Crear Administrador
                                    </button>
                                </div>
                            </form>
                            
                            <?php else: ?>
                            <!-- Formulario Edición -->
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $admin_editar['id'] ?>">
                                <input type="hidden" name="editar_admin" value="1">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre Completo *</label>
                                            <input type="text" name="nombre" class="form-control" 
                                                   value="<?= htmlspecialchars($admin_editar['nombre']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" name="email" class="form-control" 
                                                   value="<?= htmlspecialchars($admin_editar['email']) ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nivel *</label>
                                    <select name="nivel" class="form-select" required>
                                        <option value="admin" <?= $admin_editar['nivel'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="superadmin" <?= $admin_editar['nivel'] == 'superadmin' ? 'selected' : '' ?>>SuperAdmin</option>
                                    </select>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Actualizar
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Lista de Administradores -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-shield-check me-2"></i>
                                Lista de Administradores
                                <span class="badge bg-primary"><?= count($admins) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Email</th>
                                            <th>Nivel</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($admins as $adm): ?>
                                        <tr>
                                            <td><?= $adm['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($adm['nombre']) ?></strong></td>
                                            <td><?= htmlspecialchars($adm['email']) ?></td>
                                            
                                            <td>
                                                <span class="badge badge-nivel bg-<?= $adm['nivel'] == 'superadmin' ? 'danger' : 'primary' ?>">
                                                    <?= $adm['nivel'] == 'superadmin' ? 'SuperAdmin' : 'Admin' ?>
                                                </span>
                                            </td>
                                            
                                            <td><?= date('d/m/Y', strtotime($adm['fecha_registro'])) ?></td>
                                            
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?editar=<?= $adm['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalPassword<?= $adm['id'] ?>" 
                                                            title="Cambiar Contraseña">
                                                        <i class="bi bi-key"></i>
                                                    </button>
                                                    
                                                    <?php if($adm['id'] != $admin_id): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalEliminar<?= $adm['id'] ?>" 
                                                            title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Cambiar Contraseña -->
                                        <div class="modal fade" id="modalPassword<?= $adm['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Cambiar Contraseña</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?= $adm['id'] ?>">
                                                        <div class="modal-body">
                                                            <p>Cambiar contraseña de: <strong><?= htmlspecialchars($adm['nombre']) ?></strong></p>
                                                            <div class="mb-3">
                                                                <label class="form-label">Nueva Contraseña</label>
                                                                <input type="password" name="nueva_password" class="form-control" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="cambiar_password" class="btn btn-warning">Cambiar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Eliminar -->
                                        <?php if($adm['id'] != $admin_id): ?>
                                        <div class="modal fade" id="modalEliminar<?= $adm['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Eliminar Administrador</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?= $adm['id'] ?>">
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de eliminar a <strong><?= htmlspecialchars($adm['nombre']) ?></strong>?</p>
                                                            <p class="text-danger">Esta acción no se puede deshacer.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="eliminar_admin" class="btn btn-danger">Eliminar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        // Inicializar animaciones AOS
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
