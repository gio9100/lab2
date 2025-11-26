<?php
// =============================================================================
// ARCHIVO: config-publicadores.php
// CONFIGURACIÓN: Para el panel de publicadores
// =============================================================================

// 1. CONFIGURACIÓN DE LA BASE DE DATOS
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// 2. CREAR LA CONEXIÓN A LA BASE DE DATOS
$conn = new mysqli($servername, $username, $password, $dbname);

// 3. VERIFICAR SI HUBO ERROR EN LA CONEXIÓN
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// 4. CONFIGURAR EL JUEGO DE CARACTERES
$conn->set_charset("utf8mb4");

// 5. CONFIGURACIÓN DE ZONA HORARIA
date_default_timezone_set('America/Mexico_City');

// =============================================================================
// FUNCIONES PARA PUBLICADORES
// =============================================================================

/**
 * FUNCIÓN: loginPublicador
 * PROPÓSITO: Verificar si el email y password son correctos para publicadores
 */
function loginPublicador($email, $password, $conn) {
    // Preparar la consulta SQL para buscar publicador por email
    $query = "SELECT * FROM publicadores WHERE email = ? AND estado = 'activo'";
    
    // Preparar la sentencia (evita inyecciones SQL)
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verificar si encontró exactamente 1 publicador
    if ($result->num_rows === 1) {
        $publicador = $result->fetch_assoc();
        
        // Verificar si la contraseña coincide con el hash en la BD
        if (password_verify($password, $publicador['password'])) {
            
            // ACTUALIZAR EL ÚLTIMO ACCESO
            $update_query = "UPDATE publicadores SET ultimo_acceso = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $publicador['id']);
            $update_stmt->execute();
            
            return $publicador;
        }
    }
    
    return false;
}

/**
 * FUNCIÓN: registrarPublicador
 * PROPÓSITO: Crear un nuevo publicador en la base de datos
 */
function registrarPublicador($datos, $conn) {
    $query = "INSERT INTO publicadores (
        nombre, 
        email, 
        password, 
        especialidad, 
        titulo_academico, 
        institucion, 
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = $conn->prepare($query);
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    
    $stmt->bind_param("ssssss", 
        $datos['nombre'],
        $datos['email'],
        $password_hash,
        $datos['especialidad'],
        $datos['titulo_academico'],
        $datos['institucion']
    );
    
    return $stmt->execute();
}

/**
 * FUNCIÓN: emailExiste
 * PROPÓSITO: Verificar si un email ya está registrado en publicadores
 */
function emailExiste($email, $conn) {
    $query = "SELECT id FROM publicadores WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * FUNCIÓN: estaLogueado
 * PROPÓSITO: Verificar si el publicador ha iniciado sesión
 */
function estaLogueado() {
    return isset($_SESSION['publicador_id']);
}

/**
 * FUNCIÓN: requerirLogin
 * PROPÓSITO: Redirigir al login si no está autenticado
 */
function requerirLogin() {
    if (!estaLogueado()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * FUNCIÓN: obtenerPublicacionesPublicador
 * PROPÓSITO: Obtener las publicaciones de un publicador específico
 */
function obtenerPublicacionesPublicador($publicador_id, $conn) {
    $query = "SELECT p.*, c.nombre as categoria_nombre 
              FROM publicaciones p 
              LEFT JOIN categorias c ON p.categoria_id = c.id 
              WHERE p.publicador_id = ? 
              ORDER BY p.fecha_creacion DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $publicador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * FUNCIÓN: obtenerCategorias
 * PROPÓSITO: Obtener todas las categorías activas
 */
function obtenerCategorias($conn) {
    // Consulta mejorada que maneja estados NULL o vacíos
    $query = "SELECT id, nombre FROM categorias 
              WHERE (estado = 'activa' OR estado IS NULL OR estado = '') 
              ORDER BY nombre";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}

/**
 * FUNCIÓN: obtenerTodosPublicadores
 * PROPÓSITO: Obtener todos los publicadores con información de categoría
 */
function obtenerTodosPublicadores($conn) {
    // Verificar si existe la columna categoria_id en la tabla publicadores
    $check_column = $conn->query("SHOW COLUMNS FROM publicadores LIKE 'categoria_id'");
    
    if ($check_column->num_rows > 0) {
        // Si existe categoria_id, hacer JOIN con categorías
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM publicadores p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  ORDER BY p.fecha_registro DESC";
    } else {
        // Si no existe categoria_id, solo obtener publicadores
        $query = "SELECT p.*, NULL as categoria_nombre 
                  FROM publicadores p 
                  ORDER BY p.fecha_registro DESC";
    }
    
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * FUNCIÓN: crearPublicacion
 * PROPÓSITO: Crear una nueva publicación
 */
function crearPublicacion($datos, $conn) {
    // Crear slug único
    $slug = crearSlug($datos['titulo']);
    
    // Usamos el estado proporcionado o 'borrador' por defecto
    $estado = $datos['estado'] ?? 'borrador';
    
    $query = "INSERT INTO publicaciones (
        titulo, slug, contenido, resumen, publicador_id, 
        categoria_id, estado, tipo, tags
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    // Convertir tags a JSON si se proporcionaron
    $tags_json = !empty($datos['tags']) ? json_encode(explode(',', $datos['tags'])) : null;
    
    $stmt->bind_param("ssssissss", 
        $datos['titulo'],
        $slug,
        $datos['contenido'],
        $datos['resumen'],
        $datos['publicador_id'],
        $datos['categoria_id'],
        $estado, // Usamos la variable $estado
        $datos['tipo'],
        $tags_json
    );
    
    return $stmt->execute();
}

/**
 * FUNCIÓN: crearSlug
 * PROPÓSITO: Crear un slug único para las URLs
 */
function crearSlug($texto) {
    // Convertir a minúsculas
    $slug = strtolower($texto);
    
    // Reemplazar caracteres especiales por guiones
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    
    // Eliminar guiones al inicio y final
    $slug = trim($slug, '-');
    
    // Eliminar guiones múltiples
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Hacer único agregando timestamp
    $slug_unico = $slug . '-' . time();
    
    return $slug_unico;
}

/**
 * FUNCIÓN: obtenerEstadisticasPublicador
 * PROPÓSITO: Obtener estadísticas de un publicador
 */
function obtenerEstadisticasPublicador($publicador_id, $conn) {
    $stats = [
        'total_publicaciones' => 0,
        'publicadas' => 0,
        'borradores' => 0,
        'en_revision' => 0
    ];
    
    $query = "SELECT estado, COUNT(*) as total 
              FROM publicaciones 
              WHERE publicador_id = ? 
              GROUP BY estado";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $publicador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $stats[$row['estado']] = $row['total'];
        $stats['total_publicaciones'] += $row['total'];
    }
    
    return $stats;
}

// =============================================================================
// CONFIGURACIONES ADICIONALES
// =============================================================================

// Configurar para mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar límites para subida de archivos (si se usará)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
?>