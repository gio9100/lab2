<?php
// Iniciamos la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluimos la conexión a la base de datos
require_once __DIR__ . "/conexion.php";

// Variables globales para el usuario
$usuario_logueado = false;
$usuario = null;

// Verificamos si hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    $usuario_logueado = true;
    
    // Obtenemos los datos del usuario de la base de datos
    $query = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }
}

// ============================================================================
// FUNCIONES DE INTERACCIÓN CON PUBLICACIONES
// ============================================================================

/**
 * Filtra palabras ofensivas reemplazándolas con asteriscos
 */
function filtrarMalasPalabras($texto) {
    $palabras_prohibidas = [
        // Groserías comunes en español (variantes y conjugaciones)
        'puto', 'puta', 'putas', 'putos', 'putita', 'putito',
        'pendejo', 'pendeja', 'pendejos', 'pendejas', 'pendejada', 'pendejadas',
        'cabrón', 'cabrona', 'cabrones', 'cabronas', 'cabronada', 'cabron',
        'chingar', 'chingada', 'chingado', 'chingón', 'chingona', 'chinga', 'chingas',
        'verga', 'vergón', 'vergota', 'averga',
        'mierda', 'mierdas', 'mierdero',
        'coño', 'coñazo', 'coñazos',
        'joder', 'jodido', 'jodida', 'jodete', 'jódete',
        'carajo', 'carajos', 'me cago', 'cagada', 'cagado',
        'hijo de puta', 'hijueputa', 'hp', 'hdp', 'hijo puta',
        'mamada', 'mamadas', 'mamar', 'mamón', 'mamona',
        'huevón', 'huevona', 'huevones', 'güey', 'wey', 'guey',
        'culero', 'culera', 'culo', 'ojete', 'ojetes',
        'pinche', 'pinches', 'pinchi',
        'perra', 'perro', 'perras', 'perros',
        'zorra', 'zorras', 'zorro',
        'pija', 'pijas', 'pijudo',
        'concha', 'conchas', 'conchudo',
        'boludo', 'boluda', 'boludos', 'boludez',
        'pelotudo', 'pelotuda', 'pelotudez',
        'gilipollas', 'gilipolla', 'gili',
        'imbécil', 'imbecil', 'idiota', 'estúpido', 'estupido', 'tonto',
        'marica', 'maricon', 'maricón', 'maricona',
        'panocha', 'papaya', 'chocha',
        'chupame', 'chúpame', 'chupala', 'chúpala',
        'vete a la mierda', 'vete al carajo', 'vete a la verga',
        'me vale verga', 'me vale madre', 'me vale madres',
        'culiao', 'culiado', 'conchesumadre', 'conchetumare',
        'la puta madre', 'puta madre', 'putamadre',
        'malparido', 'malparida', 'gonorrea',
        'hijueperra', 'hijo de perra',
        'chucha', 'chuchas', 'chupavergas',
        'vergas', 'a la verga', 'que vergas',
        'maldito', 'maldita', 'malditos', 'maldición',
        'perra vida', 'perra mierda',
        'chingadazo', 'chingadazos', 'chingadera',
        'putazo', 'putazos', 'putada',
        'mamerto', 'mamerta',
        'pendejete', 'pendejón',
        'culeros',
        'vergazo', 'vergazos', 'vergueada',
        'chingaquedito', 'chingue', 'chinguense',
        'joto', 'jotos', 'jota',
        'puto amo', 'puta vida',
        'cagón', 'cagona', 'cagones',
        'mierdon', 'mierdón',
        'recontra', 'requete', 'recontraputamadre'
    ];
    foreach($palabras_prohibidas as $palabra) {
        $texto = str_ireplace($palabra, '***', $texto);
    }
    return $texto;
}

/**
 * Agrega un comentario a una publicación
 */
function agregarComentario($publicacion_id, $usuario_id, $contenido, $conexion) {
    $contenido = filtrarMalasPalabras($contenido);
    $query = "INSERT INTO comentarios (publicacion_id, usuario_id, contenido, fecha_creacion, estado) 
              VALUES (?, ?, ?, NOW(), 'activo')";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return false;
    $stmt->bind_param("iis", $publicacion_id, $usuario_id, $contenido);
    return $stmt->execute();
}

/**
 * Elimina un comentario (solo si es del usuario actual)
 */
function eliminarComentario($comentario_id, $usuario_id, $conexion) {
    // Verificamos que el comentario pertenezca al usuario
    $query = "UPDATE comentarios SET estado = 'eliminado' 
              WHERE id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return false;
    $stmt->bind_param("ii", $comentario_id, $usuario_id);
    return $stmt->execute();
}

/**
 * Obtiene todos los comentarios activos de una publicación
 */
function obtenerComentarios($publicacion_id, $conexion) {
    $query = "SELECT c.*, u.nombre as usuario_nombre, u.imagen as usuario_imagen
              FROM comentarios c
              LEFT JOIN usuarios u ON c.usuario_id = u.id
              WHERE c.publicacion_id = ? AND c.estado = 'activo'
              ORDER BY c.fecha_creacion DESC";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return [];
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Agrega, cambia o elimina un like/dislike (funciona como toggle)
 */
function agregarLike($publicacion_id, $usuario_id, $tipo, $conexion) {
    $query_check = "SELECT id, tipo FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $voto_actual = $result->fetch_assoc();
        if ($voto_actual['tipo'] == $tipo) {
            $query = "DELETE FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        } else {
            $query = "UPDATE likes SET tipo = ? WHERE publicacion_id = ? AND usuario_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("sii", $tipo, $publicacion_id, $usuario_id);
        }
    } else {
        $query = "INSERT INTO likes (publicacion_id, usuario_id, tipo, fecha_creacion) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iis", $publicacion_id, $usuario_id, $tipo);
    }
    return $stmt->execute();
}

/**
 * Cuenta cuántos likes y dislikes tiene una publicación
 */
function contarLikes($publicacion_id, $conexion) {
    $query = "SELECT 
                SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN tipo = 'dislike' THEN 1 ELSE 0 END) as dislikes
              FROM likes WHERE publicacion_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $conteo = $stmt->get_result()->fetch_assoc();
    return [
        'likes' => $conteo['likes'] ?? 0,
        'dislikes' => $conteo['dislikes'] ?? 0
    ];
}

/**
 * Guarda o quita una publicación de la lista "leer más tarde"
 */
function guardarParaLeerMasTarde($publicacion_id, $usuario_id, $conexion) {
    $query_check = "SELECT id FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        $query = "DELETE FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    } else {
        $query = "INSERT INTO leer_mas_tarde (publicacion_id, usuario_id, fecha_agregado) 
                  VALUES (?, ?, NOW())";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    }
    return $stmt->execute();
}

/**
 * Obtiene la lista de publicaciones guardadas de un usuario
 */
function obtenerLeerMasTarde($usuario_id, $conexion) {
    $query = "SELECT p.*, lmt.fecha_agregado, pub.nombre as publicador_nombre
              FROM leer_mas_tarde lmt
              LEFT JOIN publicaciones p ON lmt.publicacion_id = p.id
              LEFT JOIN publicadores pub ON p.publicador_id = pub.id
              WHERE lmt.usuario_id = ? AND p.id IS NOT NULL
              ORDER BY lmt.fecha_agregado DESC";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Crea un reporte de publicación o comentario inapropiado
 */
function crearReporte($tipo, $referencia_id, $usuario_id, $motivo, $descripcion, $conexion) {
    $query = "INSERT INTO reportes (tipo, referencia_id, usuario_id, motivo, descripcion, estado, fecha_creacion) 
              VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return false;
    $stmt->bind_param("siiss", $tipo, $referencia_id, $usuario_id, $motivo, $descripcion);
    return $stmt->execute();
}

/**
 * Verifica si una publicación está en la lista "leer más tarde" del usuario
 */
function verificarSiGuardada($publicacion_id, $usuario_id, $conexion) {
    $query = "SELECT id FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Obtiene el voto actual del usuario en una publicación
 */
function obtenerVotoUsuario($publicacion_id, $usuario_id, $conexion) {
    $query = "SELECT tipo FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['tipo'];
    }
    return null;
}

/**
 * Verifica si un correo ya existe en la base de datos
 */
function correoExiste($correo, $conexion) {
    $query = "SELECT id FROM usuarios WHERE correo = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->num_rows > 0;
}

/**
 * Obtiene los correos de todos los administradores
 */
function obtenerCorreosAdmins($conexion) {
    $query = "SELECT email FROM admins WHERE estado = 'activo'";
    $stmt = $conexion->prepare($query);
    if (!$stmt) return [];
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}