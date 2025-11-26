<?php
// ============================================================================
// 🛡️ GESTIÓN DE ADMINISTRADORES - ADMINS.PHP
// ============================================================================
// Este archivo permite a los SUPERADMINISTRADORES gestionar a otros admins.
//
// FUNCIONALIDADES PRINCIPALES:
// 1. Listar todos los administradores registrados
// 2. Crear nuevos administradores (requiere clave secreta)
// 3. Editar datos de administradores (nombre, email, nivel)
// 4. Cambiar contraseñas de administradores
// 5. Eliminar administradores (excepto a uno mismo)
//
// SEGURIDAD:
// - Solo accesible para usuarios con nivel 'superadmin'
// - Uso de claves secretas para crear cuentas
// - Protección contra auto-eliminación
// ============================================================================

// ============================================================================
// 📌 EXPLICACIÓN DE session_start()
// ============================================================================
// session_start() inicia o reanuda una sesión PHP.
// Es fundamental para acceder a $_SESSION, donde guardamos si el usuario
// está logueado y qué nivel de permisos tiene.
session_start();

// ----------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
$servername = "localhost";  // Servidor de base de datos (usualmente localhost)
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña de MySQL
$dbname = "lab_exp_db";     // Nombre de la base de datos

// ============================================================================
// 📌 EXPLICACIÓN DE new mysqli()
// ============================================================================
// Creamos una nueva instancia de la clase mysqli para conectar a la BD.
// Si falla, $conn->connect_error tendrá el mensaje de error.
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificamos la conexión
if ($conn->connect_error) {
    // die() detiene la ejecución inmediatamente y muestra el mensaje
    die("Error de conexión: " . $conn->connect_error);
}

// ============================================================================
// 📌 EXPLICACIÓN DE set_charset("utf8mb4")
// ============================================================================
// Establece el juego de caracteres a UTF-8 Multibyte.
// Es crucial para soportar emojis, acentos y caracteres especiales correctamente.
$conn->set_charset("utf8mb4");

// ----------------------------------------------------------------------------
// 2. CLAVES SECRETAS PARA CREAR ADMINISTRADORES
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE define()
// ============================================================================
// define() crea una CONSTANTE.
// A diferencia de las variables ($var), las constantes:
// 1. No llevan el signo $
// 2. No se pueden cambiar una vez definidas
// 3. Son globales (accesibles desde cualquier parte del script)
//
// AQUÍ: Definimos las claves que se deben escribir en el formulario
// para autorizar la creación de nuevos administradores.
define('CLAVE_ADMIN', 'labexplorer2025');           // Para crear admin normal
define('CLAVE_SUPERADMIN', 'superlabexplorer2025'); // Para crear superadmin

// ----------------------------------------------------------------------------
// 3. VERIFICAR SI ES SUPERADMIN
// ----------------------------------------------------------------------------
// Esta página es CRÍTICA. Solo los superadmins deben entrar.
//
// Verificamos dos cosas:
// 1. !isset(...) -> ¿No existe la variable de sesión? (No está logueado)
// 2. ... != 'superadmin' -> ¿El nivel NO es superadmin?
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] != 'superadmin') {
    // Si falla alguna verificación, lo sacamos de aquí
    $_SESSION['mensaje'] = "No tienes permisos para acceder a esta sección";
    $_SESSION['tipo_mensaje'] = "danger";
    
    // header() redirige al usuario a otra página
    header("Location: index-admin.php");
    exit; // IMPORTANTE: exit detiene el script para que no se ejecute nada más
}

// Obtenemos los datos del superadmin actual para mostrar en el header
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// ----------------------------------------------------------------------------
// 4. PROCESAR ACCIONES (POST)
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE $_SERVER["REQUEST_METHOD"]
// ============================================================================
// Detectamos si el usuario envió un formulario.
// GET = Solo visitar la página
// POST = Enviar datos (guardar, editar, eliminar)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ========================================================================
    // CASO A: CREAR NUEVO ADMINISTRADOR
    // ========================================================================
    if (isset($_POST['crear_admin'])) {
        // ====================================================================
        // 📌 EXPLICACIÓN DE trim()
        // ====================================================================
        // trim() elimina espacios en blanco al inicio y al final.
        // Evita errores como " juan@email.com " (con espacios).
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $nivel = $_POST['nivel'];
        $clave_secreta = trim($_POST['clave_secreta']);
        
        // Validamos la clave secreta
        $clave_valida = false;
        
        // Operador && (AND): Ambas condiciones deben ser verdaderas
        if ($nivel == 'admin' && $clave_secreta == CLAVE_ADMIN) {
            $clave_valida = true;
        } elseif ($nivel == 'superadmin' && $clave_secreta == CLAVE_SUPERADMIN) {
            $clave_valida = true;
        }
        
        if ($clave_valida) {
            // ================================================================
            // 📌 EXPLICACIÓN DE password_hash()
            // ================================================================
            // NUNCA guardar contraseñas en texto plano.
            // password_hash() crea un "hash" seguro de la contraseña.
            // PASSWORD_DEFAULT usa el algoritmo bcrypt (actualmente estándar).
            // El resultado es una cadena larga ininteligible.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Preparamos la consulta INSERT
            // NOW() es una función SQL que devuelve la fecha/hora actual
            $query = "INSERT INTO admins (nombre, email, password, nivel, fecha_registro) 
                      VALUES (?, ?, ?, ?, NOW())";
            
            // ================================================================
            // 📌 EXPLICACIÓN DE prepare() y bind_param()
            // ================================================================
            // 1. prepare(): Prepara la estructura SQL (con ? como marcadores)
            // 2. bind_param(): Reemplaza los ? con los datos reales de forma segura
            //
            // Tipos en bind_param("ssss"):
            // s = string (texto)
            // i = integer (número)
            // d = double (decimal)
            // b = blob (binario)
            //
            // Aquí son 4 strings: nombre, email, password_hash, nivel
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $nivel);
            
            // execute() ejecuta la consulta preparada
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Administrador creado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                // $conn->error contiene el detalle si algo falló (ej. email duplicado)
                $_SESSION['mensaje'] = "Error al crear administrador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } else {
            $_SESSION['mensaje'] = "Clave secreta incorrecta para el nivel seleccionado";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        // Recargamos la página para limpiar el formulario (patrón PRG)
        header("Location: admins.php");
        exit;
    }
    
    // ========================================================================
    // CASO B: EDITAR ADMINISTRADOR EXISTENTE
    // ========================================================================
    if (isset($_POST['editar_admin'])) {
        // intval() asegura que el ID sea un número entero (seguridad)
        $id = intval($_POST['id']);
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $nivel = $_POST['nivel'];
        
        $query = "UPDATE admins SET nombre=?, email=?, nivel=? WHERE id=?";
        
        $stmt = $conn->prepare($query);
        // "sssi" = string, string, string, integer
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
    
    // ========================================================================
    // CASO C: CAMBIAR CONTRASEÑA
    // ========================================================================
    if (isset($_POST['cambiar_password'])) {
        $id = intval($_POST['id']);
        $nueva_password = trim($_POST['nueva_password']);
        
        // Siempre hashear la nueva contraseña antes de guardarla
        $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
        
        $query = "UPDATE admins SET password=? WHERE id=?";
        
        $stmt = $conn->prepare($query);
        // "si" = string (password), integer (id)
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
    
    // ========================================================================
    // CASO D: ELIMINAR ADMINISTRADOR
    // ========================================================================
    if (isset($_POST['eliminar_admin'])) {
        $id = intval($_POST['id']);
        
        // VALIDACIÓN IMPORTANTE: No permitir auto-eliminación
        // Si el ID a eliminar es igual al ID del usuario logueado ($admin_id)
        if ($id == $admin_id) {
            $_SESSION['mensaje'] = "No puedes eliminarte a ti mismo";
            $_SESSION['tipo_mensaje'] = "danger";
        } else {
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

// ----------------------------------------------------------------------------
// 5. OBTENER LISTA DE TODOS LOS ADMINISTRADORES
// ----------------------------------------------------------------------------
// ORDER BY fecha_registro DESC = Los más nuevos primero
$query = "SELECT * FROM admins ORDER BY fecha_registro DESC";
$result = $conn->query($query);

// ============================================================================
// 📌 EXPLICACIÓN DE fetch_all(MYSQLI_ASSOC)
// ============================================================================
// fetch_all() obtiene TODAS las filas de la consulta de una sola vez.
// MYSQLI_ASSOC hace que el resultado sea un array asociativo.
//
// Ejemplo de estructura resultante:
// [
//    ['id' => 1, 'nombre' => 'Admin 1', ...],
//    ['id' => 2, 'nombre' => 'Admin 2', ...]
// ]
$admins = $result->fetch_all(MYSQLI_ASSOC);

// ----------------------------------------------------------------------------
// 6. OBTENER ESTADÍSTICAS
// ----------------------------------------------------------------------------
// Usamos COUNT(*) y GROUP BY para contar cuántos hay de cada nivel
// Esto es más eficiente que traer todos los datos y contarlos con PHP
$query_stats = "SELECT nivel, COUNT(*) as total FROM admins GROUP BY nivel";
$result_stats = $conn->query($query_stats);

$total_admins = 0;
$total_superadmins = 0;

// Recorremos los resultados del GROUP BY
while ($row = $result_stats->fetch_assoc()) {
    if ($row['nivel'] == 'admin') {
        $total_admins = $row['total'];
    } elseif ($row['nivel'] == 'superadmin') {
        $total_superadmins = $row['total'];
    }
}

// Total general es la suma de ambos
$total_general = $total_admins + $total_superadmins;

// ----------------------------------------------------------------------------
// 7. OBTENER ADMIN PARA EDITAR (si se seleccionó uno)
// ----------------------------------------------------------------------------
// Si la URL tiene ?editar=123, cargamos los datos de ese admin
$admin_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    
    $query = "SELECT * FROM admins WHERE id=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // fetch_assoc() obtiene una sola fila
    $admin_editar = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Administradores - Lab-Explorer</title>
    
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS de Bootstrap y vendors -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- CSS personalizado -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <style>
        /* Estilos específicos para esta página */
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
                <!-- Logo -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>
                <!-- Info del usuario -->
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- htmlspecialchars() evita ataques XSS al mostrar el nombre -->
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
                <!-- Sidebar de navegación -->
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
                            <a href="./categorias/crear_categoria.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            <!-- Clase 'active' marca la página actual -->
                            <a href="admins.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                        </div>
                        
                        <!-- Card de resumen rápido -->
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
                    <!-- Mostrar mensajes de sesión (éxito/error) -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] == 'success' ? 'success' : 'danger' ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                        // Limpiamos el mensaje para que no aparezca de nuevo al recargar
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <!-- Título de sección -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Administradores</h2>
                        <p>Administra los usuarios con acceso al panel de administración</p>
                    </div>

                    <!-- Tarjetas de estadísticas -->
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

                    <!-- Formulario Crear/Editar Admin -->
                    <!-- Usamos un operador ternario para cambiar el color del header si estamos editando -->
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
                            <!-- ========================================================== -->
                            <!-- FORMULARIO DE CREACIÓN -->
                            <!-- ========================================================== -->
                            <form method="POST">
                                <!-- Campo oculto para saber qué acción realizar -->
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

                                <!-- Clave Secreta (seguridad extra) -->
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
                            <!-- ========================================================== -->
                            <!-- FORMULARIO DE EDICIÓN -->
                            <!-- ========================================================== -->
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
                                        <!-- Pre-seleccionamos el nivel actual -->
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
                                        <!-- ================================================== -->
                                        <!-- 📌 EXPLICACIÓN DE foreach -->
                                        <!-- ================================================== -->
                                        <!-- Recorremos el array de administradores -->
                                        <!-- $admins = array con todos los datos -->
                                        <!-- $adm = variable temporal para cada fila en el bucle -->
                                        <?php foreach($admins as $adm): ?>
                                        <tr>
                                            <td><?= $adm['id'] ?></td>
                                            <td><strong><?= htmlspecialchars($adm['nombre']) ?></strong></td>
                                            <td><?= htmlspecialchars($adm['email']) ?></td>
                                            
                                            <!-- Badge de color según nivel -->
                                            <td>
                                                <span class="badge badge-nivel bg-<?= $adm['nivel'] == 'superadmin' ? 'danger' : 'primary' ?>">
                                                    <?= $adm['nivel'] == 'superadmin' ? 'SuperAdmin' : 'Admin' ?>
                                                </span>
                                            </td>
                                            
                                            <!-- Formato de fecha: día/mes/año -->
                                            <td><?= date('d/m/Y', strtotime($adm['fecha_registro'])) ?></td>
                                            
                                            <!-- Botones de acción -->
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- Editar: Recarga la página con ?editar=ID -->
                                                    <a href="?editar=<?= $adm['id'] ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    
                                                    <!-- Cambiar Password: Abre modal -->
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalPassword<?= $adm['id'] ?>" 
                                                            title="Cambiar Contraseña">
                                                        <i class="bi bi-key"></i>
                                                    </button>
                                                    
                                                    <!-- Eliminar: Solo si no es él mismo -->
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

                                        <!-- ================================================== -->
                                        <!-- MODAL CAMBIAR CONTRASEÑA -->
                                        <!-- ================================================== -->
                                        <!-- ID dinámico: id="modalPassword1", id="modalPassword2", etc. -->
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

                                        <!-- ================================================== -->
                                        <!-- MODAL ELIMINAR -->
                                        <!-- ================================================== -->
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
        // Inicializamos las animaciones AOS
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
// Cerramos la conexión a la base de datos
$conn->close();
?>
