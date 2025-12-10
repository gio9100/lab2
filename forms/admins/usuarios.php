<?php
// Gestión de usuarios registrados (no publicadores ni admins)

// Iniciar sesión
session_start();

// Incluir configuración
require_once "config-admin.php";

// Verificar permisos de administrador
requerirAdmin();

// Obtener datos del admin logueado
$admin_id = $_SESSION['admin_id'];
$admin_nombre = $_SESSION['admin_nombre'];
$admin_nivel = $_SESSION['admin_nivel'] ?? 'admin';

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Crear nuevo usuario
    if (isset($_POST['crear_usuario'])) {
        
        // Obtener y limpiar datos
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $password = $_POST['password'];
        
        // Validaciones
        if (empty($nombre) || empty($correo) || empty($password)) {
            $mensaje = "Todos los campos son obligatorios";
            $exito = false;
        }
        elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El formato del correo electrónico no es válido";
            $exito = false;
        }
        elseif (usuarioExiste($correo, $conn)) {
            $mensaje = "El correo electrónico ya está registrado";
            $exito = false;
        }
        else {
            // Datos para creación
            $datos = [
                'nombre' => $nombre,
                'correo' => $correo,
                'password' => $password
            ];
            
            // Intentar crear usuario
            if (crearUsuario($datos, $conn)) {
                $mensaje = "Usuario creado exitosamente";
                $exito = true;
            } else {
                $mensaje = "Error al crear el usuario";
                $exito = false;
            }
        }
    }
    
    // Editar usuario existente
    if (isset($_POST['editar_usuario'])) {
        
        $usuario_id = intval($_POST['usuario_id']);
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $password = trim($_POST['password'] ?? '');
        
        // Validaciones
        if (empty($nombre) || empty($correo)) {
            $mensaje = "El nombre y correo son obligatorios";
            $exito = false;
        }
        elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El formato del correo electrónico no es válido";
            $exito = false;
        }
        elseif (usuarioExiste($correo, $conn, $usuario_id)) {
            $mensaje = "El correo electrónico ya está registrado por otro usuario";
            $exito = false;
        }
        else {
            // Datos para actualización
            $datos = [
                'nombre' => $nombre,
                'correo' => $correo
            ];
            
            // Actualizar contraseña solo si se proporciona
            if (!empty($password)) {
                $datos['password'] = $password;
            }
            
            // Intentar actualizar
            if (editarUsuario($usuario_id, $datos, $conn)) {
                $mensaje = "Usuario actualizado exitosamente";
                $exito = true;
            } else {
                $mensaje = "Error al actualizar el usuario";
                $exito = false;
            }
        }
    }
    
    // Eliminar usuario
    if (isset($_POST['eliminar_usuario'])) {
        
        $usuario_id = intval($_POST['usuario_id']);
        
        // Intentar eliminar
        if (eliminarUsuario($usuario_id, $conn)) {
            $mensaje = "Usuario eliminado exitosamente";
            $exito = true;
        } else {
            $mensaje = "Error al eliminar el usuario";
            $exito = false;
        }
    }
}

// Obtener estadísticas generales
$stats = obtenerEstadisticasAdmin($conn);

// Obtener lista de usuarios normales
$usuarios_normales = obtenerUsuariosNormales($conn);

// Calcular estadísticas adicionales
$total_usuarios = count($usuarios_normales);
$usuarios_mes_actual = 0;
$usuarios_hoy = 0;

$mes_actual = date('Y-m');
$fecha_hoy = date('Y-m-d');

foreach($usuarios_normales as $usuario) {
    // Verificar mes de registro
    $fecha_registro_mes = substr($usuario['fecha_registro'], 0, 7);
    if($fecha_registro_mes == $mes_actual) {
        $usuarios_mes_actual++;
    }
    
    // Verificar día de registro
    $fecha_registro_dia = substr($usuario['fecha_registro'], 0, 10);
    if($fecha_registro_dia == $fecha_hoy) {
        $usuarios_hoy++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados - Panel Admin</title>
    
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
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- LIBRERÍA para generar PDF (Reportes) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
                    
                    <!-- Mensajes de Alerta -->
                    <?php if(isset($mensaje)): ?>
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>
                    
                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Usuarios Registrados</h2>
                        <p>Aquí puedes ver todos los usuarios normales que se han registrado en el sistema</p>
                    </div>
                    
                    <!-- Estadísticas -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $total_usuarios ?></h4>
                                    <small>Total de Usuarios</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $usuarios_mes_actual ?></h4>
                                    <small>Nuevos este Mes</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= $usuarios_hoy ?></h4>
                                    <small>Registrados Hoy</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $total_usuarios ?></h4>
                                    <small>Usuarios Activos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Usuarios -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Listado Completo de Usuarios
                            </h5>
                            
                            <div>
                                <button onclick="exportarPDF()" class="btn btn-outline-danger me-2">
                                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Exportar PDF
                                </button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                    <i class="bi bi-plus-circle me-1"></i> Crear Nuevo Usuario
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="areaReporte">
                            <?php if(empty($usuarios_normales)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay usuarios registrados en el sistema todavía.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" id="tablaUsuarios">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Firma Digital</th>
                                                <th>Fecha Registro</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($usuarios_normales as $usuario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                                <td>
                                                    <?php 
                                                    // Generar firma digital única para el usuario
                                                    $data_to_hash = $usuario['id'] . $usuario['nombre'] . $usuario['correo'] . "LAB_EXPLORA_2024";
                                                    $firma = strtoupper(substr(hash('sha256', $data_to_hash), 0, 16));
                                                    ?>
                                                    <code style="font-size: 0.75rem; background: #f8f9fa; padding: 4px 8px; border-radius: 4px; display: inline-block; font-family: 'Courier New', monospace;" title="Firma Digital Única">
                                                        <?= $firma ?>
                                                    </code>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                                <td>
                                                    <span class="status-badge active">Activo</span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEditar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </button>
                                                        
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEliminar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Editar -->
                                            <div class="modal fade" id="modalEditar<?= $usuario['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Usuario</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                            
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                                                    <input type="text" class="form-control" name="nombre" 
                                                                           value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                                                    <input type="email" class="form-control" name="correo" 
                                                                           value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                                                    <input type="password" class="form-control" name="password" 
                                                                           placeholder="Dejar vacío para mantener la actual">
                                                                    <small class="text-muted">Solo completa este campo si deseas cambiar la contraseña</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Eliminar -->
                                            <div class="modal fade" id="modalEliminar<?= $usuario['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Confirmar Eliminación</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                            
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                                                                </div>
                                                                <p>¿Estás seguro de que deseas eliminar al usuario <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>?</p>
                                                                <p class="text-muted">Email: <?= htmlspecialchars($usuario['correo']) ?></p>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="eliminar_usuario" class="btn btn-danger">Sí, Eliminar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                    </div>
```
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $total_usuarios ?></h4>
                                    <small>Usuarios Activos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Usuarios -->
                    <div class="admin-card" id="contenedor-usuarios" data-aos="fade-up">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Listado Completo de Usuarios
                            </h5>
                            
                            <div>
                                <!-- Botón PDF eliminado -->
                            </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                    <i class="bi bi-plus-circle me-1"></i> Crear Nuevo Usuario
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="areaReporte">
                            <?php if(empty($usuarios_normales)): ?>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay usuarios registrados en el sistema todavía.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table" id="tablaUsuarios">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Fecha Registro</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($usuarios_normales as $usuario): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                                <td>
                                                    <span class="status-badge active">Activo</span>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEditar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </button>
                                                        
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEliminar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal Editar -->
                                            <div class="modal fade" id="modalEditar<?= $usuario['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Usuario</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                            
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                                                    <input type="text" class="form-control" name="nombre" 
                                                                           value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                                                    <input type="email" class="form-control" name="correo" 
                                                                           value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                                                </div>
                                                                
                                                                <div class="mb-3">
                                                                    <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                                                    <input type="password" class="form-control" name="password" 
                                                                           placeholder="Dejar vacío para mantener la actual">
                                                                    <small class="text-muted">Solo completa este campo si deseas cambiar la contraseña</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Eliminar -->
                                            <div class="modal fade" id="modalEliminar<?= $usuario['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title">Confirmar Eliminación</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                            
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                                                    <strong>¡Atención!</strong> Esta acción no se puede deshacer.
                                                                </div>
                                                                <p>¿Estás seguro de que deseas eliminar al usuario <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>?</p>
                                                                <p class="text-muted">Email: <?= htmlspecialchars($usuario['correo']) ?></p>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="eliminar_usuario" class="btn btn-danger">Sí, Eliminar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
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

    <!-- Modal Crear Usuario -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Crear Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" name="correo" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Botón volver arriba -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Scripts Vendor -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <!-- LIBRERÍA PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <script>
        // Inicializar animaciones
        AOS.init();

        // Inicializar DataTables
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true
            });
        });

        // Cerrar alertas
        document.querySelectorAll('.close-btn').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    </script>

</body>
</html>
```
