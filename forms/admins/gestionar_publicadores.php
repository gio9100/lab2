<?php
// ============================================================================
// 👥 GESTIÓN COMPLETA DE PUBLICADORES - GESTIONAR_PUBLICADORES.PHP
// ============================================================================
// Este es el archivo MÁS COMPLETO Y COMPLEJO del panel de administración.
// Aquí los admins pueden hacer TODO lo relacionado con publicadores.
//
// ¿QUÉ HACE ESTE ARCHIVO?
// 1. CRUD COMPLETO de publicadores (Crear, Leer, Actualizar, Eliminar)
// 2. Aprobar publicadores pendientes
// 3. Rechazar publicadores (con motivo)
// 4. Suspender publicadores activos (con motivo)
// 5. Reactivar publicadores suspendidos
// 6. Eliminar publicadores permanentemente
// 7. Editar datos de publicadores existentes
// 8. Crear nuevos publicadores desde el panel
//
// DIFERENCIAS IMPORTANTES:
// - SUSPENDER: El publicador sigue en la BD pero no puede acceder
// - ELIMINAR: Se borra completamente de la base de datos
// - RECHAZAR: Se marca como rechazado (para publicadores pendientes)
//
// ESTRUCTURA DEL ARCHIVO:
// 1. Configuración y conexión a BD
// 2. Verificación de admin
// 3. Funciones auxiliares
// 4. Procesamiento de acciones POST
// 5. Obtener datos para mostrar
// 6. HTML: Formulario de edición/creación
// 7. HTML: Tabla con todos los publicadores
// 8. HTML: Modales para acciones
// ============================================================================

// Iniciamos la sesión para acceder a las variables del admin logueado
session_start();
// Incluir funciones de correo para notificaciones
require_once 'enviar_correo_publicador.php';
// ----------------------------------------------------------------------------
// 1. CONFIGURACIÓN DE LA BASE DE DATOS
// ----------------------------------------------------------------------------
// Credenciales para conectarnos a MySQL
$servername = "localhost";  // Servidor (localhost = tu computadora)
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña (vacía en XAMPP por defecto)
$dbname = "lab_exp_db";     // Nombre de nuestra base de datos

// Creamos la conexión usando mysqli (MySQL Improved)
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificamos si hubo error al conectar
if ($conn->connect_error) {
    // Si hay error, detenemos todo y mostramos el mensaje
    die("Error de conexión: " . $conn->connect_error);
}

// Configuramos el charset para soportar acentos, ñ y emojis
$conn->set_charset("utf8mb4");

// ----------------------------------------------------------------------------
// 2. VERIFICAR SI ES ADMINISTRADOR
// ----------------------------------------------------------------------------
// Solo los administradores pueden acceder a esta página
// Verificamos que exista una sesión activa y que tenga nivel de admin
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_nivel'] == '') {
    // Si no es admin, lo mandamos al login
    header("Location: login-admin.php");
    exit;  // Detenemos la ejecución
}

// Obtenemos los datos del administrador logueado
$admin_id = $_SESSION['admin_id'];           // ID del admin
$admin_nombre = $_SESSION['admin_nombre'];   // Nombre completo
$admin_nivel = $_SESSION['admin_nivel'];     // Nivel: 'admin' o 'superadmin'

// ----------------------------------------------------------------------------
// 3. FUNCIONES AUXILIARES
// ----------------------------------------------------------------------------
// Estas funciones nos ayudan a obtener datos que necesitamos

/**
 * 🏷️ FUNCIÓN: obtenerCategorias
 * 
 * ¿QUÉ HACE?
 * Obtiene todas las categorías activas de la base de datos
 * 
 * ¿PARA QUÉ SE USA?
 * Para llenar el select de categorías en el formulario de publicadores
 * (aunque en este archivo no se usa mucho, está disponible por si acaso)
 * 
 * ¿QUÉ DEVUELVE?
 * Un array con todas las categorías activas, ordenadas alfabéticamente
 */
function obtenerCategorias($conn) {
    // Consultamos solo las categorías con estado = 'activa'
    // ORDER BY nombre = ordenadas alfabéticamente
    $query = "SELECT id, nombre FROM categorias WHERE estado = 'activa' ORDER BY nombre";
    $result = $conn->query($query);
    
    // Devolvemos todas las filas como un array de arrays asociativos
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * 📊 FUNCIÓN: obtenerEstadisticasPublicadores
 * 
 * ¿QUÉ HACE?
 * Cuenta cuántos publicadores hay en cada estado
 * 
 * ¿PARA QUÉ SE USA?
 * Para mostrar las tarjetas de estadísticas en la parte superior
 * 
 * ¿QUÉ DEVUELVE?
 * Un array con los contadores:
 * - total: Total de publicadores
 * - activos: Cuántos están activos
 * - inactivos: Cuántos están inactivos
 * - suspendidos: Cuántos están suspendidos
 * - pendientes: Cuántos esperan aprobación
 */
function obtenerEstadisticasPublicadores($conn) {
    // Inicializamos el array con todos los contadores en 0
    $stats = [
        'total' => 0,
        'activos' => 0,
        'inactivos' => 0,
        'suspendidos' => 0,
        'pendientes' => 0
    ];
    
    // Consultamos agrupando por estado y contando cuántos hay de cada uno
    // GROUP BY estado = agrupa los publicadores por su estado
    // COUNT(*) = cuenta cuántos hay en cada grupo
    $query = "SELECT estado, COUNT(*) as total FROM publicadores GROUP BY estado";
    $result = $conn->query($query);
    
    // Recorremos cada grupo de estado
    while ($row = $result->fetch_assoc()) {
        // Sumamos al total general
        $stats['total'] += $row['total'];
        
        // Guardamos el contador específico de ese estado
        // Por ejemplo: si estado = 'activo', guardamos en $stats['activos']
        $stats[$row['estado'] . 's'] = $row['total'];
    }
    
    return $stats;
}

// ----------------------------------------------------------------------------
// 4. PROCESAR ACCIONES
// ----------------------------------------------------------------------------
// Aquí procesamos todas las acciones que el admin puede hacer
// Verificamos si se envió un formulario por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ========================================================================
    // ACCIÓN 1: APROBAR PUBLICADOR
    // ========================================================================
    // Se ejecuta cuando el admin aprueba un publicador pendiente
    if (isset($_POST['aprobar_publicador'])) {
        // Obtenemos el ID del publicador
        // intval() convierte a número entero (seguridad)
        $publicador_id = intval($_POST['publicador_id']);
                
        // PRIMERO: Obtener datos del publicador para enviar el correo
        $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
        $stmt_datos = $conn->prepare($query_datos);
        $stmt_datos->bind_param("i", $publicador_id);
        $stmt_datos->execute();
        $result_datos = $stmt_datos->get_result();
        $publicador_datos = $result_datos->fetch_assoc();
        // Preparamos la consulta UPDATE
        // Cambiamos el estado a 'activo' y guardamos la fecha de activación
        $query = "UPDATE publicadores SET estado = 'activo', fecha_activacion = NOW() WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        // Vinculamos el parámetro (i = integer)
        $stmt->bind_param("i", $publicador_id);
        
        // Ejecutamos la consulta
        if ($stmt->execute()) {
                       // Enviar correo de aprobación al publicador
            enviarCorreoAprobacion($publicador_datos['email'], $publicador_datos['nombre']);
            
            $_SESSION['mensaje'] = "Publicador aprobado correctamente y notificado por correo";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            // Si hubo error, guardamos mensaje de error
            $_SESSION['mensaje'] = "Error al aprobar publicador";
            $_SESSION['tipo_mensaje'] = "danger";
        }
        
        // Redirigimos a gestionar_publicadores.php para mostrar el mensaje
        header("Location: gestionar_publicadores.php");
        exit;
    }
    
    // ========================================================================
    // ACCIÓN 2: RECHAZAR PUBLICADOR
    // ========================================================================
    // Se ejecuta cuando el admin rechaza un publicador pendiente
    if (isset($_POST['rechazar_publicador'])) {
        // Obtenemos el ID del publicador
        $publicador_id = intval($_POST['publicador_id']);
        
        // Obtenemos el motivo del rechazo que escribió el admin
        // trim() quita espacios en blanco
        $motivo = trim($_POST['motivo'] ?? "");

                
        // PRIMERO: Obtener datos del publicador para enviar el correo
        $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
        $stmt_datos = $conn->prepare($query_datos);
        $stmt_datos->bind_param("i", $publicador_id);
        $stmt_datos->execute();
        $result_datos = $stmt_datos->get_result();
        $publicador_datos = $result_datos->fetch_assoc();
        
        // Preparamos la consulta UPDATE
        // Cambiamos el estado a 'rechazado' y guardamos el motivo
        $query = "UPDATE publicadores SET estado = 'rechazado', motivo_suspension = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        
        // Vinculamos los parámetros (s = string, i = integer)
        $stmt->bind_param("si", $motivo, $publicador_id);
        
        // Ejecutamos
        if ($stmt->execute()) {
                       // Enviar correo de rechazo al publicador
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
    
    // ========================================================================
    // ACCIÓN 3: SUSPENDER PUBLICADOR
    // ========================================================================
    // Se ejecuta cuando el admin suspende un publicador activo
    // IMPORTANTE: Suspender NO elimina, solo cambia el estado
    if (isset($_POST['suspender_publicador'])) {
        // Obtenemos el ID del publicador
        $publicador_id = intval($_POST['publicador_id']);
        
        // Obtenemos el motivo de la suspensión
        $motivo = trim($_POST['motivo'] ?? "");
        
        // Preparamos la consulta UPDATE
        // Cambiamos el estado a 'suspendido' y guardamos el motivo
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
    
    // ========================================================================
    // ACCIÓN 4: ACTIVAR PUBLICADOR
    // ========================================================================
    // Se ejecuta cuando el admin reactiva un publicador suspendido
    if (isset($_POST['activar_publicador'])) {
        // Obtenemos el ID del publicador
        $publicador_id = intval($_POST['publicador_id']);
        
        // Preparamos la consulta UPDATE
        // Cambiamos el estado a 'activo' y borramos el motivo de suspensión
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
    
    // ========================================================================
    // ACCIÓN 5: ELIMINAR PUBLICADOR
    // ========================================================================
    // Se ejecuta cuando el admin elimina permanentemente un publicador
    // IMPORTANTE: Esto BORRA el publicador de la base de datos
    if (isset($_POST['eliminar_publicador'])) {
        // Obtenemos el ID del publicador
        $publicador_id = intval($_POST['publicador_id']);
        
        // Preparamos la consulta DELETE
        // ESTO ES PERMANENTE, no se puede deshacer
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
    
    // ========================================================================
    // ACCIÓN 6: GUARDAR PUBLICADOR (CREAR O ACTUALIZAR)
    // ========================================================================
    // Esta acción sirve para DOS cosas:
    // 1. CREAR un nuevo publicador (si no hay ID)
    // 2. ACTUALIZAR un publicador existente (si hay ID)
    if (isset($_POST['guardar_publicador'])) {
        // Obtenemos todos los datos del formulario
        // trim() quita espacios en blanco al inicio y final
        $nombre = trim($_POST['nombre']);                           // Nombre completo
        $email = trim($_POST['email']);                             // Email
        $especialidad = trim($_POST['especialidad']);               // Especialidad
        $estado = $_POST['estado'];                                 // Estado
        $telefono = trim($_POST['telefono'] ?? '');                 // Teléfono (opcional)
        $titulo_academico = trim($_POST['titulo_academico'] ?? ''); // Título académico
        $institucion = trim($_POST['institucion'] ?? '');           // Institución
        $biografia = trim($_POST['biografia'] ?? '');               // Biografía
        $experiencia_años = intval($_POST['experiencia_años'] ?? 0); // Años de experiencia
        $limite_publicaciones_mes = intval($_POST['limite_publicaciones_mes'] ?? 5); // Límite mensual
        $notificaciones_email = isset($_POST['notificaciones_email']) ? 1 : 0; // Checkbox
        
        // ============================================================
        // VALIDACIÓN DE DOMINIO DE EMAIL
        // ============================================================
        // Verificamos que el email tenga un formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Si el email no es válido
            $_SESSION['mensaje'] = "❌ El formato del email no es válido";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: gestionar_publicadores.php");
            exit;
        }
        
        // Extraemos el dominio del email (lo que viene después del @)
        $dominio = substr(strrchr($email, "@"), 1);
        // strrchr busca el último @, substr quita el @ y deja solo el dominio
        
        // Verificamos que el dominio tenga registros MX (Mail eXchange)
        // Esto confirma que el dominio puede recibir correos
        if (!checkdnsrr($dominio, 'MX')) {
            // Si el dominio no tiene registros MX, no puede recibir emails
            $_SESSION['mensaje'] = "❌ El dominio del email ($dominio) no es válido o no puede recibir correos";
            $_SESSION['tipo_mensaje'] = "danger";
            header("Location: gestionar_publicadores.php");
            exit;
        }
        
        // Verificamos si estamos EDITANDO o CREANDO
        // Si existe 'id_publicador' en POST, estamos editando
        if (isset($_POST['id_publicador']) && $_POST['id_publicador'] != '') {
            // ============================================================
            // MODO: ACTUALIZAR PUBLICADOR EXISTENTE
            // ============================================================
            $id_publicador = intval($_POST['id_publicador']);
            
            // Preparamos la consulta UPDATE
            // Actualizamos todos los campos del publicador
            $query = "UPDATE publicadores SET 
                     nombre=?, email=?, especialidad=?, estado=?, telefono=?,
                     titulo_academico=?, institucion=?, biografia=?,
                     experiencia_años=?, limite_publicaciones_mes=?, notificaciones_email=?
                     WHERE id=?";
            
            $stmt = $conn->prepare($query);
            
            // Vinculamos todos los parámetros
            // s = string, i = integer
            // 8 strings + 3 integers + 1 integer para WHERE = 12 parámetros
            $stmt->bind_param("ssssssssiiii", 
                $nombre,                      // s = string
                $email,                       // s = string
                $especialidad,                // s = string
                $estado,                      // s = string
                $telefono,                    // s = string
                $titulo_academico,            // s = string
                $institucion,                 // s = string
                $biografia,                   // s = string
                $experiencia_años,            // i = integer
                $limite_publicaciones_mes,    // i = integer
                $notificaciones_email,        // i = integer
                $id_publicador                // i = integer (para el WHERE)
            );
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "✅ Publicador actualizado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar publicador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
            
        } else {
            // ============================================================
            // MODO: CREAR NUEVO PUBLICADOR
            // ============================================================
            
            // Obtenemos la contraseña del formulario
            $password = trim($_POST['password'] ?? '');
            
            // Verificamos que la contraseña no esté vacía
            if ($password == '') {
                $_SESSION['mensaje'] = "❌ La contraseña es obligatoria para crear un publicador";
                $_SESSION['tipo_mensaje'] = "danger";
                header("Location: gestionar_publicadores.php");
                exit;
            }
            
            // Encriptamos la contraseña antes de guardarla
            // NUNCA guardamos contraseñas en texto plano
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Preparamos la consulta INSERT
            // Insertamos un nuevo publicador en la base de datos
            $query = "INSERT INTO publicadores (
                     nombre, email, password, especialidad, estado, telefono,
                     titulo_academico, institucion, biografia,
                     experiencia_años, limite_publicaciones_mes, notificaciones_email,
                     fecha_registro
                     ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $conn->prepare($query);
            
            // Vinculamos todos los parámetros
            // 9 strings + 3 integers = 12 parámetros
            $stmt->bind_param("sssssssssiii", 
                $nombre,                      // s = string
                $email,                       // s = string
                $password_hash,               // s = string (contraseña encriptada)
                $especialidad,                // s = string
                $estado,                      // s = string
                $telefono,                    // s = string
                $titulo_academico,            // s = string
                $institucion,                 // s = string
                $biografia,                   // s = string
                $experiencia_años,            // i = integer
                $limite_publicaciones_mes,    // i = integer
                $notificaciones_email         // i = integer
            );
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "✅ Publicador creado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear publicador: " . $conn->error;
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
        
        // Redirigimos para mostrar el mensaje
        header("Location: gestionar_publicadores.php");
        exit;
    }
}

// ----------------------------------------------------------------------------
// 5. OBTENER DATOS PARA MOSTRAR
// ----------------------------------------------------------------------------

// Obtenemos las estadísticas de publicadores
$stats = obtenerEstadisticasPublicadores($conn);

// Obtenemos TODOS los publicadores para mostrar en la tabla
// ORDER BY fecha_registro DESC = los más recientes primero
$query_publicadores = "SELECT * FROM publicadores ORDER BY fecha_registro DESC";
$result_publicadores = $conn->query($query_publicadores);
$publicadores = $result_publicadores->fetch_all(MYSQLI_ASSOC);

// Verificamos si estamos en modo EDICIÓN
// Si hay ?editar=ID en la URL, cargamos los datos de ese publicador
$publicador_editar = null;  // Inicialmente null
if (isset($_GET['editar'])) {
    // Obtenemos el ID del publicador a editar
    $id_editar = intval($_GET['editar']);
    
    // Consultamos los datos de ese publicador
    $query = "SELECT * FROM publicadores WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Guardamos los datos del publicador
    $publicador_editar = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ================================================================ -->
    <!-- CONFIGURACIÓN BÁSICA DEL DOCUMENTO -->
    <!-- ================================================================ -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Publicadores - Lab-Explorer</title>
    
    <!-- ================================================================ -->
    <!-- FUENTES DE GOOGLE FONTS -->
    <!-- ================================================================ -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- ================================================================ -->
    <!-- ARCHIVOS CSS -->
    <!-- ================================================================ -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="../../assets/css/main.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
</head>
<body class="admin-page">

    <!-- ================================================================ -->
    <!-- HEADER -->
    <!-- ================================================================ -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                <!-- Logo -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <!-- Saludo y cerrar sesión -->
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        <a href="logout-admin.php" class="logout-btn">Cerrar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- ================================================================ -->
    <!-- CONTENIDO PRINCIPAL -->
    <!-- ================================================================ -->
    <main class="main">
        <div class="container-fluid mt-4">
            <div class="row">

                <!-- ======================================================== -->
                <!-- SIDEBAR (MENÚ LATERAL) -->
                <!-- ======================================================== -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <a href="../../index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Página principal
                            </a>
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-house me-2"></i>Panel Principal
                            </a>
                            <a href="gestionar_publicadores.php" class="list-group-item list-group-item-action active">
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
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Tarjeta con estadísticas rápidas -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Estadísticas</h6>
                            </div>
                            <div class="card-body">
                                <div class="stat-item">
                                    <small class="text-muted">Total: <?= $stats['total'] ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Activos: <?= $stats['activos'] ?? 0 ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Pendientes: <?= $stats['pendientes'] ?? 0 ?></small>
                                </div>
                                <div class="stat-item">
                                    <small class="text-muted">Suspendidos: <?= $stats['suspendidos'] ?? 0 ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- CONTENIDO PRINCIPAL (LADO DERECHO) -->
                <!-- ======================================================== -->
                <div class="col-md-9">
                    
                    <!-- ================================================ -->
                    <!-- MENSAJES DE ÉXITO O ERROR -->
                    <!-- ================================================ -->
                    <?php if(isset($_SESSION['mensaje'])): ?>
                    <div class="alert alert-<?= $_SESSION['tipo_mensaje'] ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($_SESSION['mensaje']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php 
                        // Limpiamos los mensajes de la sesión después de mostrarlos
                        unset($_SESSION['mensaje']);
                        unset($_SESSION['tipo_mensaje']);
                    endif; ?>

                    <!-- ================================================ -->
                    <!-- TÍTULO DE LA PÁGINA -->
                    <!-- ================================================ -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Publicadores</h2>
                        <p>Administra los publicadores del sistema</p>
                    </div>

                    <!-- ================================================ -->
                    <!-- TARJETAS DE ESTADÍSTICAS -->
                    <!-- ================================================ -->
                    <div class="row stats-grid mb-4" data-aos="fade-up">
                        <!-- Tarjeta: Total -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['total'] ?></h4>
                                    <small>Total Publicadores</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta: Activos -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['activos'] ?? 0 ?></h4>
                                    <small>Activos</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta: Pendientes -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['pendientes'] ?? 0 ?></h4>
                                    <small>Pendientes</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta: Suspendidos -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card danger">
                                <div class="stat-content text-center">
                                    <h4><?= $stats['suspendidos'] ?? 0 ?></h4>
                                    <small>Suspendidos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================ -->
                    <!-- FORMULARIO CREAR/EDITAR PUBLICADOR -->
                    <!-- ================================================ -->
                    <!-- Este formulario sirve para DOS cosas:
                         1. CREAR nuevo publicador (cuando no hay ?editar en URL)
                         2. EDITAR publicador existente (cuando hay ?editar=ID en URL) -->
                    <div class="admin-card mb-4" data-aos="fade-up">
                        <div class="card-header <?= $publicador_editar ? 'warning-header' : 'primary-header' ?>">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-<?= $publicador_editar ? 'pencil' : 'plus' ?> me-2"></i>
                                <?= $publicador_editar ? 'Editar Publicador' : 'Nuevo Publicador' ?>
                            </h5>
                            <!-- Si estamos editando, mostramos botón para cancelar -->
                            <?php if($publicador_editar): ?>
                                <a href="gestionar_publicadores.php" class="btn btn-sm btn-secondary">Cancelar</a>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <!-- Formulario -->
                            <form method="POST">
                                <!-- Si estamos editando, guardamos el ID en un campo oculto -->
                                <?php if($publicador_editar): ?>
                                    <input type="hidden" name="id_publicador" value="<?= $publicador_editar['id'] ?>">
                                <?php endif; ?>
                                
                                <div class="row">
                                    <!-- Campo: Nombre Completo -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Nombre Completo *</label>
                                            <input type="text" name="nombre" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['nombre']) : '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <!-- Campo: Email -->
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

                                <!-- Campo: Contraseña (solo al crear) -->
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
                                    <!-- Campo: Especialidad -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Especialidad *</label>
                                            <input type="text" name="especialidad" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['especialidad']) : '' ?>" 
                                                   required>
                                        </div>
                                    </div>
                                    
                                    <!-- Campo: Estado -->
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
                                    <!-- Campo: Teléfono -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Teléfono</label>
                                            <input type="tel" name="telefono" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['telefono'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Campo: Título Académico -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Título Académico</label>
                                            <input type="text" name="titulo_academico" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['titulo_academico'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Campo: Institución -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Institución</label>
                                            <input type="text" name="institucion" class="form-control" 
                                                   value="<?= $publicador_editar ? htmlspecialchars($publicador_editar['institucion'] ?? '') : '' ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Campo: Años de Experiencia -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Años de Experiencia</label>
                                            <input type="number" name="experiencia_años" class="form-control" min="0" 
                                                   value="<?= $publicador_editar ? $publicador_editar['experiencia_años'] : 0 ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Campo: Biografía -->
                                <div class="mb-3">
                                    <label class="form-label">Biografía</label>
                                    <textarea name="biografia" class="form-control" rows="3"><?= $publicador_editar ? htmlspecialchars($publicador_editar['biografia'] ?? '') : '' ?></textarea>
                                </div>

                                <div class="row">
                                    <!-- Campo: Límite de Publicaciones por Mes -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Límite Publicaciones/Mes</label>
                                            <input type="number" name="limite_publicaciones_mes" class="form-control" min="1" 
                                                   value="<?= $publicador_editar ? $publicador_editar['limite_publicaciones_mes'] : 5 ?>">
                                        </div>
                                    </div>
                                    
                                    <!-- Campo: Notificaciones por Email (Checkbox) -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" name="notificaciones_email" 
                                                       <?= $publicador_editar && $publicador_editar['notificaciones_email'] ? 'checked' : '' ?>>
                                                <label class="form-check-label">
                                                    Recibir notificaciones por email
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Botón de enviar -->
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" name="guardar_publicador" class="btn btn-primary">
                                        <i class="bi bi-<?= $publicador_editar ? 'check' : 'plus' ?>-circle me-1"></i>
                                        <?= $publicador_editar ? 'Actualizar' : 'Crear' ?> Publicador
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ================================================ -->
                    <!-- TABLA DE TODOS LOS PUBLICADORES -->
                    <!-- ================================================ -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-people me-2"></i>
                                Lista de Publicadores
                                <span class="badge bg-primary"><?= count($publicadores) ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if(empty($publicadores)): ?>
                                <p class="text-muted">No hay publicadores registrados.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="admin-table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Email</th>
                                                <th>Especialidad</th>
                                                <th>Estado</th>
                                                <th>Fecha Registro</th>
                                                <th>Último Acceso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($publicadores as $pub): ?>
                                            <tr>
                                                <td><?= $pub['id'] ?></td>
                                                <td><strong><?= htmlspecialchars($pub['nombre']) ?></strong></td>
                                                <td><?= htmlspecialchars($pub['email']) ?></td>
                                                <td><?= htmlspecialchars($pub['especialidad']) ?></td>
                                                <td>
                                                    <span class="status-badge <?= $pub['estado'] ?>">
                                                        <?= ucfirst($pub['estado']) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('d/m/Y', strtotime($pub['fecha_registro'])) ?></td>
                                                <td>
                                                    <?= $pub['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($pub['ultimo_acceso'])) : 'Nunca' ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <!-- Botón Editar -->
                                                        <a href="?editar=<?= $pub['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        
                                                        <!-- Botones según el estado -->
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
                                                                <i class="bi bi-pause"></i>
                                                            </button>
                                                        <?php elseif($pub['estado'] == 'suspendido'): ?>
                                                            <!-- Activar -->
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                                <button type="submit" name="activar_publicador" class="btn btn-sm btn-outline-success" title="Activar">
                                                                    <i class="bi bi-play"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Botón Eliminar (siempre disponible) -->
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
                                                                <p>¿Rechazar a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Motivo del rechazo:</label>
                                                                    <textarea name="motivo" class="form-control" rows="3" required></textarea>
                                                                </div>
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
                                                                <p>¿Suspender a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Motivo de la suspensión:</label>
                                                                    <textarea name="motivo" class="form-control" rows="3" required></textarea>
                                                                </div>
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
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Eliminar Publicador</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <input type="hidden" name="publicador_id" value="<?= $pub['id'] ?>">
                                                            <div class="modal-body">
                                                                <p>¿Eliminar permanentemente a <strong><?= htmlspecialchars($pub['nombre']) ?></strong>?</p>
                                                                <p class="text-danger">Esta acción no se puede deshacer.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <button type="submit" name="eliminar_publicador" class="btn btn-danger">Eliminar</button>
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

    <!-- ================================================================ -->
    <!-- SCRIPTS -->
    <!-- ================================================================ -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/vendor/aos/aos.js"></script>
    <script src="../../assets/js/main.js"></script>
    
    <script>
        // Inicializamos AOS (animaciones)
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