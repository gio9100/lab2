<?php
// Configuración y funciones para el sistema de publicadores

// Conexión a MySQL
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
date_default_timezone_set('America/Mexico_City');

// Verifica credenciales de publicador y retorna sus datos
function loginPublicador($email, $password, $conn) {
    // Primero buscamos el publicador sin importar el estado
    $query = "SELECT * FROM publicadores WHERE email = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $publicador = $result->fetch_assoc();
        
        // password_verify() = compara contraseña con hash
        if (password_verify($password, $publicador['password'])) {
            // Verificar el estado del publicador
            if ($publicador['estado'] === 'activo') {
                // Actualizar último acceso
                $update_query = "UPDATE publicadores SET ultimo_acceso = NOW() WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("i", $publicador['id']);
                $update_stmt->execute();
                
                return $publicador;
            } else {
                // Retornar array con información del estado
                // Retornar array con información detalalda del estado
                return [
                    'estado_cuenta' => $publicador['estado'],
                    'nombre' => $publicador['nombre'],
                    'email' => $publicador['email'],
                    // Incluimos los motivos para mostrarlos en el login
                    'motivo_suspension' => $publicador['motivo_suspension'] ?? '',
                    'motivo_rechazo' => $publicador['motivo_rechazo'] ?? ''
                ];
            }
        }
    }
    
    return false;
}

// Crea un nuevo publicador en la base de datos
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
    // password_hash() = encripta contraseña de forma segura
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

// Verifica si un email ya está registrado
function emailExiste($email, $conn) {
    $query = "SELECT id FROM publicadores WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Verifica si el publicador está logueado
function estaLogueado() {
    return isset($_SESSION['publicador_id']);
}

// Redirige al login si no está autenticado
function requerirLogin() {
    if (!estaLogueado()) {
        header('Location: login.php');
        exit();
    }
}

// Obtiene publicaciones de un publicador específico
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

// Obtiene todas las categorías activas
function obtenerCategorias($conn) {
    $query = "SELECT id, nombre FROM categorias 
              WHERE (estado = 'activa' OR estado IS NULL OR estado = '') 
              ORDER BY nombre";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    return [];
}

// Obtiene todos los publicadores con información de categoría
function obtenerTodosPublicadores($conn) {
    // Verificar si existe columna categoria_id
    $check_column = $conn->query("SHOW COLUMNS FROM publicadores LIKE 'categoria_id'");
    
    if ($check_column->num_rows > 0) {
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM publicadores p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  ORDER BY p.fecha_registro DESC";
    } else {
        $query = "SELECT p.*, NULL as categoria_nombre 
                  FROM publicadores p 
                  ORDER BY p.fecha_registro DESC";
    }
    
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Crea una nueva publicación en la base de datos
function crearPublicacion($datos, $conn) {
    // Generar slug único para la URL
    $slug = crearSlug($datos['titulo']);
    
    // Obtener estado, por defecto 'borrador'
    $estado = $datos['estado'] ?? 'borrador';
    
    // Preparar la consulta SQL para insertar la nueva publicación
    // Se agregan las columnas 'archivo_url' y 'tipo_archivo'
    $query = "INSERT INTO publicaciones (
        titulo, slug, contenido, resumen, publicador_id, 
        categoria_id, estado, tipo, tags, imagen_principal,
        archivo_url, tipo_archivo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    
    // json_encode() = convierte array a JSON para guardar los tags
    $tags_json = !empty($datos['tags']) ? json_encode(explode(',', $datos['tags'])) : null;
    
    // Obtener datos opcionales
    $imagen_principal = $datos['imagen_principal'] ?? null;
    $archivo_url = $datos['archivo_url'] ?? null;
    $tipo_archivo = $datos['tipo_archivo'] ?? null;
    
    // Vincular parámetros a la consulta
    // s = string, i = integer
    // Total: 12 parámetros
    $stmt->bind_param("ssssisssssss", 
        $datos['titulo'],
        $slug,
        $datos['contenido'],
        $datos['resumen'],
        $datos['publicador_id'],
        $datos['categoria_id'],
        $estado,
        $datos['tipo'],
        $tags_json,
        $imagen_principal,
        $archivo_url,       // Nuevo: URL del archivo adjunto
        $tipo_archivo       // Nuevo: Tipo de archivo (pdf, doc, etc.)
    );
    
    // Ejecutar la consulta y retornar resultado
    return $stmt->execute();
}

// Crea un slug único para URLs amigables
function crearSlug($texto) {
    // strtolower() = convierte a minúsculas
    $slug = strtolower($texto);
    
    // preg_replace() = reemplaza usando expresiones regulares
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $slug = preg_replace('/-+/', '-', $slug);
    
    // time() = timestamp actual
    $slug_unico = $slug . '-' . time();
    
    return $slug_unico;
}

// Obtiene estadísticas de un publicador
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

// Configuración de límites para subida de archivos
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
