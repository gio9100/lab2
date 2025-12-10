<?php
// Gestión completa de publicadores (Admin)
// Permite crear, editar, aprobar, rechazar, suspender y eliminar publicadores

// Iniciar sesión
session_start();

// Incluir notificaciones por correo
require_once 'enviar_correo_publicador.php';

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

// Verificar permisos de administrador
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] == '') {
    header("Location: login-admin.php");
    exit;
}

// Obtener datos del admin actual
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'];

// Función para obtener estadísticas
function obtenerEstadisticasPublicadores($conn) {
    $stats = [
        'total' => 0,
        'activos' => 0,
        'inactivos' => 0,
        'suspendidos' => 0,
        'pendientes' => 0
    ];
    
    $query = "SELECT estado, COUNT(*) as total FROM publicadores GROUP BY estado";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $stats['total'] += $row['total'];
        $stats[$row['estado'] . 's'] = $row['total'];
    }
    
    return $stats;
}

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Aprobar publicador
    if (isset($_POST['aprobar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        
        // Obtener datos para correo
        $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
        $stmt_datos = $conn->prepare($query_datos);
        $stmt_datos->bind_param("i", $publicador_id);
        $stmt_datos->execute();
        $result_datos = $stmt_datos->get_result();
        $publicador_datos = $result_datos->fetch_assoc();
        
        // Actualizar estado
        $query = "UPDATE publicadores SET estado = 'activo', fecha_activacion = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $publicador_id);
        
        if ($stmt->execute()) {
            enviarCorreoAprobacion($publicador_datos['email'], $publicador_datos['nombre']);
            $_SESSION['mensaje'] = "Publicador aprobado correctamente y notificado por correo";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al aprobar publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // Rechazar publicador
    if (isset($_POST['rechazar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        $motivo = trim($_POST['motivo'] ?? "");
        
        // Obtener datos para correo
        $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
        $stmt_datos = $conn->prepare($query_datos);
        $stmt_datos->bind_param("i", $publicador_id);
        $stmt_datos->execute();
        $result_datos = $stmt_datos->get_result();
        $publicador_datos = $result_datos->fetch_assoc();
        
        // Actualizar estado
        $query = "UPDATE publicadores SET estado = 'rechazado', motivo_suspension = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $motivo, $publicador_id);
        
        if ($stmt->execute()) {
            enviarCorreoRechazo($publicador_datos['email'], $publicador_datos['nombre'], $motivo);
            $_SESSION['mensaje'] = "Publicador rechazado correctamente y notificado por correo";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al rechazar publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // Suspender publicador
    if (isset($_POST['suspender_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        $motivo = trim($_POST['motivo'] ?? "");
        
        $query = "UPDATE publicadores SET estado = 'suspendido', motivo_suspension = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $motivo, $publicador_id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Publicador suspendido correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al suspender publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // Activar publicador
    if (isset($_POST['activar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        
        $query = "UPDATE publicadores SET estado = 'activo', motivo_suspension = NULL WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $publicador_id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Publicador activado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al activar publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // Eliminar publicador
    if (isset($_POST['eliminar_publicador'])) {
        $publicador_id = intval($_POST['publicador_id']);
        
        $query = "DELETE FROM publicadores WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $publicador_id);
        
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Publicador eliminado correctamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al eliminar publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // Guardar publicador (Crear o Editar)
    if (isset($_POST['guardar_publicador'])) {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $especialidad = trim($_POST['especialidad']);
        $estado = $_POST['estado'];
        $telefono = trim($_POST['telefono'] ?? '');
        $titulo_academico = trim($_POST['titulo_academico'] ?? '');
        $institucion = trim($_POST['institucion'] ?? '');
        $biografia = trim($_POST['biografia'] ?? '');
        $experiencia_años = intval($_POST['experiencia_años'] ?? 0);
        $limite_publicaciones_mes = intval($_POST['limite_publicaciones_mes'] ?? 5);
        $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0;
        
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['mensaje'] = "El formato del email no es válido";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: gestionar_publicadores.php");
            exit;
        }
        
        // Validar dominio
        $dominio = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($dominio, 'MX')) {
            $_SESSION['mensaje'] = "El dominio del email ($dominio) no es válido o no puede recibir correos";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: gestionar_publicadores.php");
            exit;
        }
        
        // Editar existente
        if (isset($_POST['id_publicador']) && $_POST['id_publicador'] != '') {
            $id_publicador = intval($_POST['id_publicador']);
            
            $query = "UPDATE publicadores SET 
                     nombre=?, email=?, especialidad=?, estado=?, telefono=?,
                     titulo_academico=?, institucion=?, biografia=?,
                     experiencia_años=?, limite_publicaciones_mes=?, notificaciones_email=?
                     WHERE id=?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssiiii", 
                $nombre, $email, $especialidad, $estado, $telefono,
                $titulo_academico, $institucion, $biografia,
                $experiencia_años, $limite_publicaciones_mes, $notificaciones_email,
                $id_publicador
            );
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Publicador actualizado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al actualizar publicador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
            
        } else {
            // Crear nuevo
            $password = trim($_POST['password'] ?? '');
            
            if ($password == '') {
                $_SESSION['mensaje'] = "La contraseña es obligatoria para crear un publicador";
                $_SESSION['tipo_mensaje'] = "danger";
                header("Location: gestionar_publicadores.php");
                exit;
            }
            
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO publicadores (
                     nombre, email, password, especialidad, estado, telefono,
                     titulo_academico, institucion, biografia,
                     experiencia_años, limite_publicaciones_mes, notificaciones_email,
                     fecha_registro
                     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssiii", 
                $nombre, $email, $password_hash, $especialidad, $estado, $telefono,
                $titulo_academico, $institucion, $biografia,
                $experiencia_años, $limite_publicaciones_mes, $notificaciones_email
            );
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Publicador creado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al crear publicador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
        
        header("Location: gestionar_publicadores.php");
        exit;
    }
}

// Obtener estadísticas y lista de publicadores
$stats = obtenerEstadisticasPublicadores($conn);

$query_publicadores = "SELECT * FROM publicadores ORDER BY fecha_registro DESC";
$result_publicadores = $conn->query($query_publicadores);
$publicadores = $result_publicadores->fetch_all(MYSQLI_ASSOC);

// Cargar datos para edición si es necesario
$publicador_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    
    $query = "SELECT * FROM publicadores WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    $publicador_editar = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Publicadores - Lab-Explora</title>
    
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
</head>
<body class="admin-page">

    <!-- Header -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">

                <div class="d-flex align-items-center">
                    <button class="btn btn-outline-primary sidebar-toggle me-3 d-md-none" id="sidebar-toggle">
                        <i class="bi bi-list"></i>
                    </button>

                <a href="../../pagina-principal.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/logobrayan2.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explora</h1><span></span>
                </a>
                </div>

                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <a href="perfil-admin.php" class="saludo d-none d-md-inline text-decoration-none text-dark me-3">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</a>
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
                <!-- Sidebar -->
                <div class="col-md-3 mb-4 sidebar-wrapper" id="sidebarWrapper">
                    <?php include 'sidebar-admin.php'; ?>
                </div>

                <!-- Contenido Principal -->
                <div class="col-md-9">
                    
                    <!-- Mensajes -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Publicadores</h2>
                        <p>Administra los publicadores del sistema</p>
                    </div>

                    <!-- Tarjetas de Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total'] ?></h4>
                                    <small>Total Publicadores</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['activos'] ?? 0 ?></h4>
                                    <small>Activos</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['pendientes'] ?? 0 ?></h4>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card danger">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['suspendidos'] ?? 0 ?></h4>
                                    <small>Suspendidos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario Crear/Editar -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header <?= $publicador_editar ? 'warning-header' : 'primary-header' ?>">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-<?= $publicador_editar ? 'pencil' : 'plus' ?> me-2"></i>
                                <?= $publicador_editar ? 'Editar Publicador' : 'Nuevo Publicador' ?>
                            </h5>
                            <?php if($publicador_editar): ?>
                                <a href="gestionar_publicadores.php" class="btn btn-sm btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <?php if($publicador_editar): ?>
                                    <input type="hidden" name="id_publicador" value="<?= $publicador_editar['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre Completo *</label>
                                            <input type="text" name="nombre" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['nombre']) : '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" name="email" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['email']) : '' ?>" 
                                                   required>
                                            <small class="text-muted">Se verificará que el dominio pueda recibir correos</small>
                                        </div>
                                    </div>
                                </div>

                                <?php if(!$publicador_editar): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Contraseña *</label>
                                            <input type="password" name="password" class="form-control" required>
                                            <small class="text-muted">Mínimo 6 caracteres</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Especialidad *</label>
                                            <input type="text" name="especialidad" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['especialidad']) : '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Estado *</label>
                                            <select name="estado" class="form-select" required>
                                                <option value="pendiente" <?= $publicador_editar && $publicador_editar['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                                <option value="activo" <?= $publicador_editar && $publicador_editar['estado'] == 'activo' ? 'selected' : '' ?>>Activo</option>
                                                <option value="suspendido" <?= $publicador_editar && $publicador_editar['estado'] == 'suspendido' ? 'selected' : '' ?>>Suspendido</option>
                                                <option value="rechazado" <?= $publicador_editar && $publicador_editar['estado'] == 'rechazado' ? 'selected' : '' ?>>Rechazado</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" name="telefono" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['telefono'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Título Académico</label>
                                            <input type="text" name="titulo_academico" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['titulo_academico'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Institución</label>
                                            <input type="text" name="institucion" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['institucion'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Años Experiencia</label>
                                            <input type="number" name="experiencia_años" class="form-control" 
                                                   value="<?= $publicador_editar ? intval($publicador_editar['experiencia_años']) : 0 ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Límite Publicaciones/Mes</label>
                                            <input type="number" name="limite_publicaciones_mes" class="form-control" 
                                                   value="<?= $publicador_editar ? intval($publicador_editar['limite_publicaciones_mes']) : 5 ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Biografía</label>
                                    <textarea name="biografia" class="form-control" rows="3"><?= $publicador_editar ? htmlspecialchars($publicador_editar['biografia'] ?? '') : '' ?></textarea>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="notificaciones" name="notificaciones_email" 
                                           <?= (!$publicador_editar || $publicador_editar['notificaciones_email']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="notificaciones">Recibir notificaciones por email</label>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="guardar_publicador" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i><?= $publicador_editar ? 'Actualizar' : 'Crear Publicador' ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Publicadores -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Lista de Publicadores
                                <span class="badge bg-primary"><?= count($publicadores) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre / Email</th>
                                            <th>Especialidad</th>
                                            <th>Estado</th>
                                            <th>Firma Digital</th>
                                            <th>Fecha Registro</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($publicadores as $pub): ?>
                                        <tr>
                                            <td><?= $pub['id'] ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($pub['nombre']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($pub['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($pub['especialidad']) ?></td>
                                            <td>
                                                <?php
                                                $clase_estado = 'secondary';
                                                switch($pub['estado']) {
                                                    case 'activo': $clase_estado = 'success'; break;
                                                    case 'pendiente': $clase_estado = 'warning'; break;
                                                    case 'suspendido': $clase_estado = 'danger'; break;
                                                    case 'rechazado': $clase_estado = 'dark'; break;
                                                }
                                                ?>
                                                <span class="badge bg-<?= $clase_estado ?>">
                                                    <?= ucfirst($pub['estado']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php 
                                                // Generar firma digital única para el publicador
                                                $data_to_hash = $pub['id'] . $pub['nombre'] . $pub['email'] . "LAB_EXPLORA_PUB_SECURE_KEY";
                                                $firma = strtoupper(substr(hash('sha256', $data_to_hash), 0, 16));
                                                ?>
                                                <code style="font-size: 0.75rem; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; display: inline-block; font-family: 'Courier New', monospace;" title="Firma Digital Única">
                                                    <?= $firma ?>
                                                </code>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($pub['fecha_registro'])) ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <!-- Botón Editar -->
                                                    <a href="?editar=<?= $pub['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <!-- Acciones según estado -->
                                                    <?php if($pub['estado'] == 'pendiente'): ?>
                                                        <!-- Aprobar -->
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                            <button type="submit" name="aprobar_publicador" class="btn btn-sm btn-outline-success" title="Aprobar">
                                                                <i class="bi bi-check-lg"></i>
                                                            </button>
                                                        </form>
                                                        <!-- Rechazar -->
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                data-bs-toggle="modal" data-bs-target="#modalRechazar<?= $pub['id'] ?>" title="Rechazar">
                                                            <i class="bi bi-x-lg"></i>
                                                        </button>
                                                    
                                                    <?php elseif($pub['estado'] == 'activo'): ?>
                                                        <!-- Suspender -->
                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                data-bs-toggle="modal" data-bs-target="#modalSuspender<?= $pub['id'] ?>" title="Suspender">
                                                            <i class="bi bi-pause-fill"></i>
                                                        </button>
                                                    
                                                    <?php elseif($pub['estado'] == 'suspendido'): ?>
                                                        <!-- Activar -->
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                            <button type="submit" name="activar_publicador" class="btn btn-sm btn-outline-success" title="Reactivar">
                                                                <i class="bi bi-play-fill"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <!-- Eliminar (Siempre disponible) -->
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" data-bs-target="#modalEliminar<?= $pub['id'] ?>" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Rechazar -->
                                        <div class="modal fade" id="modalRechazar<?= $pub['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Rechazar Publicador</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                        <div class="modal-body">
                                                            <p>¿Por qué rechazas a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                            <textarea name="motivo" class="form-control" rows="3" required placeholder="Motivo del rechazo..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="rechazar_publicador" class="btn btn-danger">Rechazar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Suspender -->
                                        <div class="modal fade" id="modalSuspender<?= $pub['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Suspender Publicador</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                        <div class="modal-body">
                                                            <p>¿Por qué suspendes a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                            <textarea name="motivo" class="form-control" rows="3" required placeholder="Motivo de la suspensión..."></textarea>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="suspender_publicador" class="btn btn-warning">Suspender</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Eliminar -->
                                        <div class="modal fade" id="modalEliminar<?= $pub['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Eliminar Publicador</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de eliminar a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                            <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> Esta acción es irreversible y borrará todas sus publicaciones.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" name="eliminar_publicador" class="btn btn-danger">Eliminar Definitivamente</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

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