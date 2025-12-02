<?php
// ============================================================================
// 👥 GESTIÓN DE USUARIOS REGISTRADOS - USUARIOS.PHP
// ============================================================================
// Este archivo es una página dedicada exclusivamente a la gestión de usuarios
// normales (no publicadores ni administradores) que se han registrado en el sistema.
//
// ¿QUÉ MUESTRA ESTE ARCHIVO?
// 1. Estadísticas de usuarios (total de usuarios, nuevos del mes, activos, etc.)
// 2. Tabla completa con TODOS los usuarios registrados
// 3. Información detallada de cada usuario (nombre, email, fecha de registro)
//
// ¿QUÉ ACCIONES SE PUEDEN HACER?
// - Ver listado completo de usuarios
// - Buscar usuarios por nombre o email
// - Ver estadísticas de usuarios
// - Filtrar usuarios por fecha de registro
//
// ARCHIVOS QUE USA:
// - config-admin.php: Para funciones y conexión a BD
// - CSS: Bootstrap + admin.css para estilos
// - JavaScript: AOS para animaciones, DataTables para búsqueda y paginación
//
// SEGURIDAD:
// - Solo administradores logueados pueden acceder
// - Se verifica con requerirAdmin() al inicio
// ============================================================================

// ============================================================================
// 📌 EXPLICACIÓN DE session_start()
// ============================================================================
// session_start() es como "abrir la puerta" a la información de la sesión.
// Sin esto, no podríamos saber quién está logueado ni acceder a $_SESSION.
// IMPORTANTE: Debe ir ANTES de cualquier HTML o echo, sino da error.
session_start();

// ============================================================================
// 📌 EXPLICACIÓN DE require_once
// ============================================================================
// require_once es como decir "necesito este archivo SÍ O SÍ".
// Si el archivo no existe, el script se detiene completamente.
// El "once" significa que si ya lo incluimos antes, no lo vuelve a incluir
// (esto evita errores de "función ya definida" o "clase ya declarada").
require_once "config-admin.php";

// ----------------------------------------------------------------------------
// 🔐 VERIFICACIÓN DE SEGURIDAD
// ----------------------------------------------------------------------------
// Llamamos a requerirAdmin() que está definida en config-admin.php.
// Esta función revisa si existe $_SESSION['admin_id'].
// Si NO existe (o sea, no hay admin logueado), te manda al login y para todo.
// Es como un guardia de seguridad que no deja pasar a nadie sin credenciales.
requerirAdmin();

// ----------------------------------------------------------------------------
// 👤 OBTENER DATOS DEL ADMINISTRADOR LOGUEADO
// ----------------------------------------------------------------------------
// Aquí sacamos la información del admin que inició sesión.
// Esta info se guardó en $_SESSION cuando hizo login exitosamente.
$admin_id = $_SESSION['admin_id'];          // El ID único del admin (número)
$admin_nombre = $_SESSION['admin_nombre'];  // Su nombre completo
$admin_nivel = $_SESSION['admin_nivel'];    // Su nivel: 'admin' o 'superadmin'

// ----------------------------------------------------------------------------
// 🔄 PROCESAR ACCIONES SOBRE USUARIOS (POST)
// ----------------------------------------------------------------------------
// ============================================================================
// 📌 EXPLICACIÓN DE $_SERVER["REQUEST_METHOD"]
// ============================================================================
// $_SERVER es un array superglobal que contiene información del servidor y del entorno.
// "REQUEST_METHOD" nos dice qué método HTTP se usó: GET, POST, PUT, DELETE, etc.
// Verificamos si es "POST" porque los formularios envían datos con este método.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ========================================================================
    // ACCIÓN 1: CREAR NUEVO USUARIO
    // ========================================================================
    // ====================================================================
    // 📌 EXPLICACIÓN DE isset()
    // ====================================================================
    // isset() verifica si una variable existe y no es NULL.
    // Aquí verificamos si se envió el botón con name="crear_usuario".
    // Cada botón de formulario tiene un name único para identificar qué acción se ejecutó.
    if (isset($_POST['crear_usuario'])) {
        
        // Obtenemos y limpiamos los datos del formulario
        // trim() elimina espacios en blanco al inicio y final
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $password = $_POST['password'];
        
        // ================================================================
        // 📌 EXPLICACIÓN DE VALIDACIONES
        // ================================================================
        // Antes de insertar en la BD, validamos que los datos sean correctos.
        // Esto previene errores y datos basura en la base de datos.
        
        // Validar que los campos no estén vacíos
        if (empty($nombre) || empty($correo) || empty($password)) {
            $mensaje = "Todos los campos son obligatorios";
            $exito = false;
        }
        // Validar formato de correo electrónico
        // filter_var() con FILTER_VALIDATE_EMAIL verifica que sea un email válido
        elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El formato del correo electrónico no es válido";
            $exito = false;
        }
        // Verificar que el correo no esté ya registrado
        elseif (usuarioExiste($correo, $conn)) {
            $mensaje = "El correo electrónico ya está registrado";
            $exito = false;
        }
        // Si todas las validaciones pasan, creamos el usuario
        else {
            // Preparamos el array con los datos
            $datos = [
                'nombre' => $nombre,
                'correo' => $correo,
                'password' => $password
            ];
            
            // Llamamos a la función crearUsuario() de config-admin.php
            if (crearUsuario($datos, $conn)) {
                $mensaje = "Usuario creado exitosamente";
                $exito = true;
            } else {
                $mensaje = "Error al crear el usuario";
                $exito = false;
            }
        }
    }
    
    // ========================================================================
    // ACCIÓN 2: EDITAR USUARIO EXISTENTE
    // ========================================================================
    if (isset($_POST['editar_usuario'])) {
        
        // ====================================================================
        // 📌 EXPLICACIÓN DE intval()
        // ====================================================================
        // intval() convierte un valor a número entero.
        // Es una medida de SEGURIDAD importante: asegura que el ID sea un número
        // y no código SQL malicioso (previene Inyección SQL).
        $usuario_id = intval($_POST['usuario_id']);
        $nombre = trim($_POST['nombre']);
        $correo = trim($_POST['correo']);
        $password = trim($_POST['password'] ?? ''); // Operador ?? devuelve '' si no existe
        
        // Validar campos obligatorios
        if (empty($nombre) || empty($correo)) {
            $mensaje = "El nombre y correo son obligatorios";
            $exito = false;
        }
        // Validar formato de correo
        elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $mensaje = "El formato del correo electrónico no es válido";
            $exito = false;
        }
        // Verificar que el correo no esté usado por otro usuario
        // Pasamos $usuario_id para excluirlo de la búsqueda (puede mantener su propio correo)
        elseif (usuarioExiste($correo, $conn, $usuario_id)) {
            $mensaje = "El correo electrónico ya está registrado por otro usuario";
            $exito = false;
        }
        // Si todo está bien, editamos el usuario
        else {
            $datos = [
                'nombre' => $nombre,
                'correo' => $correo
            ];
            
            // Solo agregamos la contraseña si se proporcionó una nueva
            if (!empty($password)) {
                $datos['password'] = $password;
            }
            
            // Llamamos a la función editarUsuario()
            if (editarUsuario($usuario_id, $datos, $conn)) {
                $mensaje = "Usuario actualizado exitosamente";
                $exito = true;
            } else {
                $mensaje = "Error al actualizar el usuario";
                $exito = false;
            }
        }
    }
    
    // ========================================================================
    // ACCIÓN 3: ELIMINAR USUARIO
    // ========================================================================
    if (isset($_POST['eliminar_usuario'])) {
        
        $usuario_id = intval($_POST['usuario_id']);
        
        // Llamamos a la función eliminarUsuario()
        if (eliminarUsuario($usuario_id, $conn)) {
            $mensaje = "Usuario eliminado exitosamente";
            $exito = true;
        } else {
            $mensaje = "Error al eliminar el usuario";
            $exito = false;
        }
    }
}

// ----------------------------------------------------------------------------
// 📊 OBTENER ESTADÍSTICAS DE USUARIOS
// ----------------------------------------------------------------------------
// Llamamos a obtenerEstadisticasAdmin($conn) que viene de config-admin.php.
// Esta función hace varias consultas COUNT(*) a la base de datos.
// Nos devuelve un array con números: cuántos usuarios hay, publicadores, etc.
$stats = obtenerEstadisticasAdmin($conn);

// ----------------------------------------------------------------------------
// 📋 OBTENER TODOS LOS USUARIOS NORMALES
// ----------------------------------------------------------------------------
// Llamamos a la función obtenerUsuariosNormales() de config-admin.php.
// Esta función ejecuta un SELECT en la tabla 'usuarios' y nos devuelve
// un array con todos los usuarios registrados (no publicadores ni admins).
// Cada usuario es un array asociativo con: id, nombre, correo, fecha_registro, etc.
$usuarios_normales = obtenerUsuariosNormales($conn);

// ----------------------------------------------------------------------------
// 📊 CALCULAR ESTADÍSTICAS ADICIONALES DE USUARIOS
// ----------------------------------------------------------------------------
// Vamos a calcular algunas estadísticas extras que no vienen en $stats.

// ====================================================================
// 📌 EXPLICACIÓN DE count()
// ====================================================================
// count() cuenta cuántos elementos hay en un array.
// Si $usuarios_normales tiene 50 usuarios, count() devuelve 50.
$total_usuarios = count($usuarios_normales);

// Inicializamos contadores en cero para ir sumando
$usuarios_mes_actual = 0;  // Usuarios registrados este mes
$usuarios_hoy = 0;         // Usuarios registrados hoy

// ====================================================================
// 📌 EXPLICACIÓN DE date() y time()
// ====================================================================
// date() formatea una fecha/hora según el formato que le pidas.
// time() devuelve el timestamp actual (segundos desde 1970-01-01).
// 'Y-m' da el año y mes actual, ejemplo: "2025-11"
$mes_actual = date('Y-m');  // Ejemplo: "2025-11"
$fecha_hoy = date('Y-m-d'); // Ejemplo: "2025-11-25"

// ====================================================================
// 📌 EXPLICACIÓN DE foreach
// ====================================================================
// foreach es un bucle especial para recorrer arrays.
// En cada vuelta, $usuario toma el valor de un elemento del array.
// Es como revisar una lista de personas, una por una.
foreach($usuarios_normales as $usuario) {
    
    // ================================================================
    // 📌 EXPLICACIÓN DE substr()
    // ================================================================
    // substr() extrae una parte de un string.
    // substr($usuario['fecha_registro'], 0, 7) toma los primeros 7 caracteres.
    // Si fecha_registro es "2025-11-25 14:30:00", substr da "2025-11"
    $fecha_registro_mes = substr($usuario['fecha_registro'], 0, 7);
    
    // Si el mes de registro coincide con el mes actual, sumamos 1
    if($fecha_registro_mes == $mes_actual) {
        $usuarios_mes_actual++;
    }
    
    // Extraemos solo la fecha (sin hora) para comparar con hoy
    $fecha_registro_dia = substr($usuario['fecha_registro'], 0, 10);
    
    // Si se registró hoy, sumamos 1
    if($fecha_registro_dia == $fecha_hoy) {
        $usuarios_hoy++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- ================================================================ -->
    <!-- CONFIGURACIÓN BÁSICA DEL DOCUMENTO HTML -->
    <!-- ================================================================ -->
    <!-- charset="UTF-8" permite usar acentos y caracteres especiales -->
    <meta charset="UTF-8">
    
    <!-- viewport hace que la página se vea bien en móviles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- El título que aparece en la pestaña del navegador -->
    <title>Usuarios Registrados - Panel Admin</title>
    
    <!-- ================================================================ -->
    <!-- FUENTES DE GOOGLE FONTS -->
    <!-- ================================================================ -->
    <!-- Cargamos fuentes bonitas desde Google para que el texto se vea profesional -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    <!-- ================================================================ -->
    <!-- CSS DE VENDORS (LIBRERÍAS EXTERNAS) -->
    <!-- ================================================================ -->
    <!-- Bootstrap: framework CSS para diseño responsive y componentes -->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons: iconos vectoriales para usar en la interfaz -->
    <link href="../../assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    
    <!-- AOS: librería para animaciones al hacer scroll -->
    <link href="../../assets/vendor/aos/aos.css" rel="stylesheet">

    <!-- ================================================================ -->
    <!-- CSS PERSONALIZADO -->
    <!-- ================================================================ -->
    <!-- main.css: estilos generales del sitio -->
    <link href="../../assets/css/main.css" rel="stylesheet">
    
    <!-- admin.css: estilos específicos para el panel de administración -->
    <link rel="stylesheet" href="../../assets/css-admins/admin.css">
    
    <!-- ================================================================ -->
    <!-- DATATABLES CSS -->
    <!-- ================================================================ -->
    <!-- DataTables es una librería que agrega búsqueda, ordenamiento y paginación a las tablas -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="admin-page">

    <!-- ================================================================ -->
    <!-- HEADER (ENCABEZADO) -->
    <!-- ================================================================ -->
    <header id="header" class="header position-relative">
        <div class="container-fluid container-xl position-relative">
            <div class="top-row d-flex align-items-center justify-content-between">
                
                <!-- Logo y nombre del sitio -->
                <a href="../../index.php" class="logo d-flex align-items-end">
                    <img src="../../assets/img/logo/nuevologo.ico" alt="logo-lab">
                    <h1 class="sitename">Lab-Explorer</h1><span></span>
                </a>

                <!-- Información del admin y botón de cerrar sesión -->
                <div class="d-flex align-items-center">
                    <div class="social-links">
                        <!-- ==================================================== -->
                        <!-- 📌 EXPLICACIÓN DE htmlspecialchars() -->
                        <!-- ==================================================== -->
                        <!-- htmlspecialchars() convierte caracteres especiales a entidades HTML. -->
                        <!-- Por ejemplo: < se convierte en &lt; -->
                        <!-- Esto es SÚPER IMPORTANTE para seguridad (previene ataques XSS). -->
                        <!-- XSS = Cross-Site Scripting, cuando alguien inyecta código malicioso. -->
                        <!-- SIEMPRE usa htmlspecialchars() al mostrar datos de usuarios o BD. -->
                        <span class="saludo">👨‍💼 Hola, <?= htmlspecialchars($admin_nombre) ?> (<?= $admin_nivel ?>)</span>
                        
                        <!-- Botón para cerrar sesión -->
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
                <!-- SIDEBAR (MENÚ LATERAL DE NAVEGACIÓN) -->
                <!-- ======================================================== -->
                <div class="col-md-3 mb-4">
                    <div class="sidebar-nav">
                        <div class="list-group">
                            <!-- Enlace a la página principal del sitio -->
                            <a href="../../index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-house-door me-2"></i>Página Principal
                            </a>
                            
                            <!-- Enlace al dashboard principal de admin -->
                            <a href="index-admin.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i>Panel Principal
                            </a>
                            
                            <!-- Enlace a moderación automática con IA -->
                            <a href="../../ollama_ia/panel-moderacion.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-robot me-2"></i>Moderación Automática
                            </a>

                            <!-- Enlace a gestión de publicadores -->
                            <a href="gestionar_publicadores.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-people me-2"></i>Gestionar Publicadores
                            </a>
                            
                            <!-- Enlace a usuarios (ESTA PÁGINA - por eso está activa) -->
                            <a href="usuarios.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-person-badge me-2"></i>Usuarios Registrados
                            </a>
                            
                            <!-- Enlace a gestión de publicaciones -->
                            <a href="gestionar-publicaciones.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-file-text me-2"></i>Gestionar Publicaciones
                            </a>
                            
                            <!-- Enlace a categorías -->
                            <a href="./categorias/listar_categorias.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-tags me-2"></i>Categorías
                            </a>
                            
                            <!-- ================================================ -->
                            <!-- 📌 EXPLICACIÓN DE if($admin_nivel == 'superadmin') -->
                            <!-- ================================================ -->
                            <!-- Este if verifica si el admin es un superadmin. -->
                            <!-- Solo los superadmins pueden gestionar otros admins. -->
                            <!-- Si es admin normal, este enlace no se muestra. -->
                            <?php if($admin_nivel == 'superadmin'): ?>
                            <a href="admins.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-shield-check me-2"></i>Administradores
                            </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- ================================================ -->
                        <!-- TARJETA DE RESUMEN RÁPIDO -->
                        <!-- ================================================ -->
                        <!-- Esta tarjeta muestra estadísticas generales del sistema -->
                        <div class="quick-stats-card mt-4">
                            <div class="card-header">
                                <h6 class="card-title mb-0">Resumen del Sistema</h6>
                            </div>
                            <div class="card-body">
                                <!-- Mostramos el total de usuarios -->
                                <div class="stat-item">
                                    <small class="text-muted">Usuarios: <?= $stats['total_usuarios'] ?></small>
                                </div>
                                <!-- Mostramos el total de publicadores -->
                                <div class="stat-item">
                                    <small class="text-muted">Publicadores: <?= $stats['total_publicadores'] ?></small>
                                </div>
                                <!-- Mostramos el total de publicaciones -->
                                <div class="stat-item">
                                    <small class="text-muted">Publicaciones: <?= $stats['total_publicaciones'] ?></small>
                                </div>
                                <!-- Mostramos cuántos publicadores están pendientes de aprobación -->
                                <div class="stat-item">
                                    <small class="text-muted">Pendientes: <?= $stats['publicadores_pendientes'] ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- CONTENIDO PRINCIPAL DERECHO -->
                <!-- ======================================================== -->
                <div class="col-md-9">
                    
                    <!-- ================================================ -->
                    <!-- MENSAJES DE ALERTA -->
                    <!-- ================================================ -->
                    <!-- ============================================ -->
                    <!-- 📌 EXPLICACIÓN DE isset($mensaje) -->
                    <!-- ============================================ -->
                    <!-- isset() verifica si la variable $mensaje existe. -->
                    <!-- Si existe, significa que se realizó alguna acción (crear, editar, eliminar) -->
                    <!-- y debemos mostrar el resultado al usuario. -->
                    <?php if(isset($mensaje)): ?>
                    <!-- ============================================ -->
                    <!-- 📌 EXPLICACIÓN DEL OPERADOR TERNARIO -->
                    <!-- ============================================ -->
                    <!-- $exito ? 'success' : 'error' es un operador ternario. -->
                    <!-- Funciona así: condición ? valor_si_true : valor_si_false -->
                    <!-- Si $exito es true, la clase será 'success' (verde). -->
                    <!-- Si $exito es false, la clase será 'error' (rojo). -->
                    <div class="alert-message <?= $exito ? 'success' : 'error' ?>" data-aos="fade-up">
                        <?= htmlspecialchars($mensaje) ?>
                        <!-- Botón para cerrar la alerta -->
                        <button type="button" class="close-btn">&times;</button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Título de la sección con animación -->
                    <div class="section-title" data-aos="fade-up">
                        <h2>Gestión de Usuarios Registrados</h2>
                        <p>Aquí puedes ver todos los usuarios normales que se han registrado en el sistema</p>
                    </div>
                    
                    <!-- ================================================ -->
                    <!-- TARJETAS DE ESTADÍSTICAS DE USUARIOS -->
                    <!-- ================================================ -->
                    <!-- data-aos="fade-up" hace que aparezca con animación al hacer scroll -->
                    <!-- data-aos-delay="100" retrasa la animación 100ms -->
                    <div class="row stats-grid mb-4" data-aos="fade-up" data-aos-delay="100">
                        
                        <!-- Tarjeta 1: Total de usuarios -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card primary">
                                <div class="stat-content text-center">
                                    <!-- ======================================== -->
                                    <!-- 📌 EXPLICACIÓN DE LA SINTAXIS CORTA DE PHP -->
                                    <!-- ======================================== -->
                                    <!-- La sintaxis corta de apertura y cierre de PHP -->
                                    <!-- es lo mismo que usar php echo con la variable -->
                                    <!-- Es una forma corta de imprimir valores en HTML -->
                                    <h4><?= $total_usuarios ?></h4>
                                    <small>Total de Usuarios</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta 2: Usuarios del mes actual -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card success">
                                <div class="stat-content text-center">
                                    <h4><?= $usuarios_mes_actual ?></h4>
                                    <small>Nuevos este Mes</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta 3: Usuarios registrados hoy -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card info">
                                <div class="stat-content text-center">
                                    <h4><?= $usuarios_hoy ?></h4>
                                    <small>Registrados Hoy</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tarjeta 4: Todos activos (por ahora todos los usuarios están activos) -->
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stat-card warning">
                                <div class="stat-content text-center">
                                    <h4><?= $total_usuarios ?></h4>
                                    <small>Usuarios Activos</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================ -->
                    <!-- TABLA DE USUARIOS REGISTRADOS -->
                    <!-- ================================================ -->
                    <div class="admin-card" data-aos="fade-up">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-person-badge me-2"></i>
                                Listado Completo de Usuarios
                            </h5>
                            <!-- ============================================ -->
                            <!-- 📌 BOTÓN PARA CREAR NUEVO USUARIO -->
                            <!-- ============================================ -->
                            <!-- data-bs-toggle="modal" activa un modal de Bootstrap -->
                            <!-- data-bs-target="#modalCrearUsuario" indica qué modal abrir -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCrearUsuario">
                                <i class="bi bi-plus-circle me-1"></i> Crear Nuevo Usuario
                            </button>
                        </div>
                        <div class="card-body">
                            <!-- ============================================ -->
                            <!-- 📌 EXPLICACIÓN DE empty() -->
                            <!-- ============================================ -->
                            <!-- empty() verifica si una variable está vacía. -->
                            <!-- Devuelve true si: -->
                            <!-- - La variable no existe -->
                            <!-- - Es null -->
                            <!-- - Es false -->
                            <!-- - Es 0 o "0" -->
                            <!-- - Es un string vacío "" -->
                            <!-- - Es un array vacío [] -->
                            <?php if(empty($usuarios_normales)): ?>
                                <!-- Si no hay usuarios, mostramos este mensaje -->
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay usuarios registrados en el sistema todavía.
                                </div>
                            <?php else: ?>
                                <!-- Si SÍ hay usuarios, mostramos la tabla -->
                                <div class="table-responsive">
                                    <!-- ======================================== -->
                                    <!-- 📌 EXPLICACIÓN DE id="tablaUsuarios" -->
                                    <!-- ======================================== -->
                                    <!-- Le ponemos un ID a la tabla para que DataTables -->
                                    <!-- la pueda identificar y agregarle funcionalidades -->
                                    <!-- como búsqueda, ordenamiento y paginación. -->
                                    <table class="admin-table" id="tablaUsuarios">
                                        <thead>
                                            <tr>
                                                <!-- Columna de ID -->
                                                <th>ID</th>
                                                <!-- Columna de Nombre -->
                                                <th>Nombre</th>
                                                <!-- Columna de Email -->
                                                <th>Email</th>
                                                <!-- Columna de Fecha de Registro -->
                                                <th>Fecha Registro</th>
                                                <!-- Columna de Estado -->
                                                <th>Estado</th>
                                                <!-- Columna de Acciones (Editar/Eliminar) -->
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- ======================================== -->
                                            <!-- 📌 EXPLICACIÓN DE foreach -->
                                            <!-- ======================================== -->
                                            <!-- foreach recorre el array $usuarios_normales. -->
                                            <!-- En cada vuelta, $usuario contiene los datos -->
                                            <!-- de un usuario (id, nombre, correo, etc.) -->
                                            <?php foreach($usuarios_normales as $usuario): ?>
                                            <tr>
                                                <!-- Mostramos el ID del usuario -->
                                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                                
                                                <!-- Mostramos el nombre del usuario -->
                                                <!-- htmlspecialchars() protege contra ataques XSS -->
                                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                                
                                                <!-- Mostramos el email del usuario -->
                                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                                
                                                <!-- ================================ -->
                                                <!-- 📌 EXPLICACIÓN DE date() y strtotime() -->
                                                <!-- ================================ -->
                                                <!-- strtotime() convierte un string de fecha -->
                                                <!-- a un timestamp Unix (número de segundos). -->
                                                <!-- date() formatea ese timestamp al formato que queramos. -->
                                                <!-- 'd/m/Y' significa: día/mes/año (25/11/2025) -->
                                                <!-- 'd/m/Y H:i' sería: 25/11/2025 14:30 -->
                                                <td><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                                                
                                                <!-- Estado del usuario (por ahora todos están activos) -->
                                                <td>
                                                    <!-- ================================ -->
                                                    <!-- 📌 EXPLICACIÓN DE span con clase -->
                                                    <!-- ================================ -->
                                                    <!-- Usamos un span con clase "status-badge active" -->
                                                    <!-- para que se vea como una etiqueta verde bonita. -->
                                                    <!-- El CSS de admin.css le da el estilo. -->
                                                    <span class="status-badge active">Activo</span>
                                                </td>
                                                
                                                <!-- Columna de acciones con botones -->
                                                <td>
                                                    <div class="action-buttons">
                                                        <!-- ================================ -->
                                                        <!-- 📌 BOTÓN EDITAR -->
                                                        <!-- ================================ -->
                                                        <!-- Abre el modal de edición para este usuario específico -->
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEditar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-pencil"></i> Editar
                                                        </button>
                                                        
                                                        <!-- ================================ -->
                                                        <!-- 📌 BOTÓN ELIMINAR -->
                                                        <!-- ================================ -->
                                                        <!-- Abre el modal de confirmación de eliminación -->
                                                        <button type="button" class="btn btn-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#modalEliminar<?= $usuario['id'] ?>">
                                                            <i class="bi bi-trash"></i> Eliminar
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- ============================================ -->
                                            <!-- MODAL PARA EDITAR USUARIO -->
                                            <!-- ============================================ -->
                                            <!-- Cada usuario tiene su propio modal con un ID único -->
                                            <div class="modal fade" id="modalEditar<?= $usuario['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Editar Usuario</h5>
                                                            <!-- Botón X para cerrar el modal -->
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <!-- Formulario de edición -->
                                                        <form method="POST">
                                                            <!-- Campo oculto con el ID del usuario -->
                                                            <input type="hidden" name="usuario_id" value="<?= $usuario['id'] ?>">
                                                            
                                                            <div class="modal-body">
                                                                <!-- Campo Nombre -->
                                                                <div class="mb-3">
                                                                    <label for="nombre" class="form-label">Nombre Completo</label>
                                                                    <!-- value="" prellenamos el campo con el valor actual -->
                                                                    <input type="text" class="form-control" name="nombre" 
                                                                           value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                                                                </div>
                                                                
                                                                <!-- Campo Correo -->
                                                                <div class="mb-3">
                                                                    <label for="correo" class="form-label">Correo Electrónico</label>
                                                                    <input type="email" class="form-control" name="correo" 
                                                                           value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                                                                </div>
                                                                
                                                                <!-- Campo Contraseña (opcional al editar) -->
                                                                <div class="mb-3">
                                                                    <label for="password" class="form-label">Nueva Contraseña (dejar en blanco para no cambiar)</label>
                                                                    <input type="password" class="form-control" name="password" 
                                                                           placeholder="Dejar vacío para mantener la actual">
                                                                    <small class="text-muted">Solo completa este campo si deseas cambiar la contraseña</small>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="modal-footer">
                                                                <!-- Botón cancelar -->
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                                <!-- Botón guardar cambios -->
                                                                <button type="submit" name="editar_usuario" class="btn btn-primary">Guardar Cambios</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ============================================ -->
                                            <!-- MODAL PARA ELIMINAR USUARIO -->
                                            <!-- ============================================ -->
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

    <!-- ================================================================ -->
    <!-- MODAL PARA CREAR NUEVO USUARIO -->
    <!-- ================================================================ -->
    <!-- Este modal se abre cuando se hace click en "Crear Nuevo Usuario" -->
    <div class="modal fade" id="modalCrearUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-plus me-2"></i>
                        Crear Nuevo Usuario
                    </h5>
                    <!-- Botón X para cerrar el modal -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <!-- ======================================================== -->
                <!-- 📌 FORMULARIO DE CREACIÓN -->
                <!-- ======================================================== -->
                <!-- method="POST" envía los datos al servidor -->
                <!-- Los datos se envían de forma segura (no aparecen en la URL) -->
                <form method="POST">
                    <div class="modal-body">
                        <!-- ================================================ -->
                        <!-- CAMPO: NOMBRE COMPLETO -->
                        <!-- ================================================ -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">
                                Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <!-- ======================================== -->
                            <!-- 📌 EXPLICACIÓN DE required -->
                            <!-- ======================================== -->
                            <!-- required es un atributo HTML5 que hace que el campo sea obligatorio. -->
                            <!-- El navegador no permitirá enviar el formulario si este campo está vacío. -->
                            <input type="text" 
                                   class="form-control" 
                                   name="nombre" 
                                   placeholder="Ej: Juan Pérez García"
                                   required>
                        </div>
                        
                        <!-- ================================================ -->
                        <!-- CAMPO: CORREO ELECTRÓNICO -->
                        <!-- ================================================ -->
                        <div class="mb-3">
                            <label for="correo" class="form-label">
                                Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <!-- ======================================== -->
                            <!-- 📌 EXPLICACIÓN DE type="email" -->
                            <!-- ======================================== -->
                            <!-- type="email" valida automáticamente que el formato sea de email. -->
                            <!-- El navegador verifica que contenga @ y un dominio válido. -->
                            <input type="email" 
                                   class="form-control" 
                                   name="correo" 
                                   placeholder="ejemplo@correo.com"
                                   required>
                            <small class="text-muted">
                                El usuario usará este correo para iniciar sesión
                            </small>
                        </div>
                        
                        <!-- ================================================ -->
                        <!-- CAMPO: CONTRASEÑA -->
                        <!-- ================================================ -->
                        <div class="mb-3">
                            <label for="password" class="form-label">
                                Contraseña <span class="text-danger">*</span>
                            </label>
                            <!-- ======================================== -->
                            <!-- 📌 EXPLICACIÓN DE type="password" -->
                            <!-- ======================================== -->
                            <!-- type="password" oculta los caracteres que se escriben. -->
                            <!-- En lugar de mostrar "abc123", muestra "••••••" -->
                            <!-- minlength="6" requiere al menos 6 caracteres -->
                            <input type="password" 
                                   class="form-control" 
                                   name="password" 
                                   placeholder="Mínimo 6 caracteres"
                                   minlength="6"
                                   required>
                            <small class="text-muted">
                                La contraseña debe tener al menos 6 caracteres
                            </small>
                        </div>
                        
                        <!-- Nota informativa -->
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <!-- Botón para cancelar y cerrar el modal -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i> Cancelar
                        </button>
                        
                        <!-- ============================================ -->
                        <!-- 📌 BOTÓN DE ENVÍO DEL FORMULARIO -->
                        <!-- ============================================ -->
                        <!-- type="submit" envía el formulario -->
                        <!-- name="crear_usuario" identifica esta acción en el PHP -->
                        <button type="submit" name="crear_usuario" class="btn btn-success">
                            <i class="bi bi-check-circle me-1"></i> Crear Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- BOTÓN SCROLL TO TOP -->
    <!-- ================================================================ -->
    <!-- Este botón aparece cuando haces scroll hacia abajo -->
    <!-- Al hacer click, te lleva de vuelta arriba de la página -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
        <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- ================================================================ -->
    <!-- SCRIPTS DE JAVASCRIPT -->
    <!-- ================================================================ -->
    
    <!-- jQuery: librería necesaria para DataTables -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap Bundle: incluye JavaScript de Bootstrap (modales, dropdowns, etc.) -->
    <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <!-- AOS: librería para animaciones al hacer scroll -->
    <script src="../../assets/vendor/aos/aos.js"></script>
    
    <!-- Main.js: JavaScript personalizado del sitio -->
    <script src="../../assets/js/main.js"></script>
    
    <!-- DataTables: librería para búsqueda, ordenamiento y paginación de tablas -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // ================================================================
        // 📌 EXPLICACIÓN DE AOS.init()
        // ================================================================
        // AOS = Animate On Scroll (Animar al hacer scroll)
        // Inicializamos la librería AOS con configuración personalizada
        AOS.init({
            duration: 1000,  // Duración de las animaciones en milisegundos (1 segundo)
            once: true       // Las animaciones solo se ejecutan una vez (no se repiten al volver a hacer scroll)
        });

        // ================================================================
        // 📌 EXPLICACIÓN DE $(document).ready()
        // ================================================================
        // Esta es sintaxis de jQuery.
        // $(document).ready() ejecuta el código cuando el DOM está completamente cargado.
        // DOM = Document Object Model (la estructura HTML de la página).
        // Es como decir: "cuando la página esté lista, ejecuta esto"
        $(document).ready(function() {
            
            // ============================================================
            // 📌 EXPLICACIÓN DE $('#tablaUsuarios').DataTable()
            // ============================================================
            // $('#tablaUsuarios') selecciona el elemento con id="tablaUsuarios"
            // .DataTable() convierte esa tabla normal en una tabla interactiva
            // con búsqueda, ordenamiento y paginación automáticos.
            $('#tablaUsuarios').DataTable({
                
                // ========================================================
                // CONFIGURACIÓN DE IDIOMA (ESPAÑOL)
                // ========================================================
                // Por defecto DataTables está en inglés, aquí lo traducimos
                language: {
                    // Texto del campo de búsqueda
                    search: "Buscar:",
                    
                    // Texto cuando no hay resultados
                    zeroRecords: "No se encontraron usuarios",
                    
                    // Texto de información de registros
                    info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
                    
                    // Texto cuando la tabla está vacía
                    infoEmpty: "Mostrando 0 a 0 de 0 usuarios",
                    
                    // Texto cuando se filtra la búsqueda
                    infoFiltered: "(filtrado de _MAX_ usuarios totales)",
                    
                    // Texto del selector de cantidad de registros
                    lengthMenu: "Mostrar _MENU_ usuarios por página",
                    
                    // Textos de los botones de paginación
                    paginate: {
                        first: "Primero",    // Botón para ir a la primera página
                        last: "Último",      // Botón para ir a la última página
                        next: "Siguiente",   // Botón para ir a la siguiente página
                        previous: "Anterior" // Botón para ir a la página anterior
                    }
                },
                
                // ========================================================
                // CONFIGURACIÓN DE ORDENAMIENTO
                // ========================================================
                // order: [[0, 'desc']] significa:
                // - Ordenar por la columna 0 (ID)
                // - 'desc' = descendente (del más nuevo al más viejo)
                // Si quisieras ascendente sería 'asc'
                order: [[0, 'desc']],
                
                // ========================================================
                // CONFIGURACIÓN DE PAGINACIÓN
                // ========================================================
                // pageLength: cuántos registros mostrar por página
                pageLength: 10,
                
                // ========================================================
                // CONFIGURACIÓN DE OPCIONES DE LONGITUD
                // ========================================================
                // lengthMenu: opciones para el selector de "mostrar X registros"
                // El usuario puede elegir entre 10, 25, 50 o 100 registros por página
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                
                // ========================================================
                // RESPONSIVE
                // ========================================================
                // responsive: true hace que la tabla se adapte a pantallas pequeñas
                responsive: true
            });
        });
    </script>
</body>
</html>
