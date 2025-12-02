<?php
 /* 🚫 FUNCIÓN: filtrarMalasPalabras
 * PROPÓSITO: Reemplaza palabras ofensivas con asteriscos para mantener un ambiente respetuoso
 * PARÁMETROS:
 *   - $texto: El texto del comentario a filtrar
 * RETORNA: El texto limpio con *** en lugar de malas palabras
 */
function filtrarMalasPalabras($texto) {
    // Lista de palabras prohibidas (filtro completo de moderación)
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
    
    // Recorremos cada palabra prohibida
    foreach($palabras_prohibidas as $palabra) {
        // str_ireplace reemplaza sin importar mayúsculas/minúsculas
        // Ejemplo: "IDIOTA", "Idiota", "idiota" todas se reemplazan
        $texto = str_ireplace($palabra, '***', $texto);
    }
    
    return $texto;
}

/**
 * 💬 FUNCIÓN: agregarComentario
 * PROPÓSITO: Guarda un nuevo comentario en la base de datos con filtro de malas palabras
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación donde se comenta
 *   - $usuario_id: ID del usuario que hace el comentario
 *   - $contenido: Texto del comentario
 *   - $conexion: Conexión a la base de datos
 * RETORNA: true si se guardó correctamente, false si hubo error
 */
function agregarComentario($publicacion_id, $usuario_id, $contenido, $conexion) {
    // Primero filtramos las malas palabras del comentario
    $contenido = filtrarMalasPalabras($contenido);
    
    // Preparamos la consulta SQL para insertar el comentario
    // NOW() guarda la fecha y hora actual automáticamente
    $query = "INSERT INTO comentarios (publicacion_id, usuario_id, contenido, fecha_creacion, estado) 
              VALUES (?, ?, ?, NOW(), 'activo')";
    
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    // Vinculamos los parámetros: i=integer, s=string
    $stmt->bind_param("iis", $publicacion_id, $usuario_id, $contenido);
    
    // Ejecutamos y retornamos el resultado
    return $stmt->execute();
}

/**
 * 📖 FUNCIÓN: obtenerComentarios
 * PROPÓSITO: Obtiene todos los comentarios activos de una publicación
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $conexion: Conexión a la BD
 * RETORNA: Array con los comentarios (incluye nombre e imagen del usuario)
 */
function obtenerComentarios($publicacion_id, $conexion) {
    // Hacemos JOIN con usuarios para obtener nombre e imagen del comentarista
    $query = "SELECT c.*, u.nombre as usuario_nombre, u.imagen as usuario_imagen
              FROM comentarios c
              LEFT JOIN usuarios u ON c.usuario_id = u.id
              WHERE c.publicacion_id = ? AND c.estado = 'activo'
              ORDER BY c.fecha_creacion DESC";
    
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return []; // Retornamos array vacío si hay error
    }
    
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // fetch_all devuelve todos los resultados como array
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * 👍 FUNCIÓN: agregarLike
 * PROPÓSITO: Agrega, cambia o elimina un like/dislike (funciona como toggle)
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $usuario_id: ID del usuario
 *   - $tipo: 'like' o 'dislike'
 *   - $conexion: Conexión a la BD
 * RETORNA: true si se procesó correctamente
 * 
 * LÓGICA:
 * - Si el usuario ya dio like y vuelve a dar like → se elimina (toggle)
 * - Si el usuario dio like y ahora da dislike → se cambia a dislike
 * - Si el usuario no ha votado → se agrega el voto
 */
function agregarLike($publicacion_id, $usuario_id, $tipo, $conexion) {
    // Primero verificamos si el usuario ya votó en esta publicación
    $query_check = "SELECT id, tipo FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        // Ya votó anteriormente
        $voto_actual = $result->fetch_assoc();
        
        if ($voto_actual['tipo'] == $tipo) {
            // Si es el mismo voto, lo eliminamos (toggle)
            // Ejemplo: dio like, vuelve a dar like → se quita el like
            $query = "DELETE FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("ii", $publicacion_id, $usuario_id);
        } else {
            // Si es diferente, lo actualizamos
            // Ejemplo: dio like, ahora da dislike → cambia a dislike
            $query = "UPDATE likes SET tipo = ? WHERE publicacion_id = ? AND usuario_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->bind_param("sii", $tipo, $publicacion_id, $usuario_id);
        }
    } else {
        // No ha votado, insertamos nuevo voto
        $query = "INSERT INTO likes (publicacion_id, usuario_id, tipo, fecha_creacion) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("iis", $publicacion_id, $usuario_id, $tipo);
    }
    
    return $stmt->execute();
}

/**
 * 📊 FUNCIÓN: contarLikes
 * PROPÓSITO: Cuenta cuántos likes y dislikes tiene una publicación
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $conexion: Conexión a la BD
 * RETORNA: Array con 'likes' y 'dislikes' (números)
 */
function contarLikes($publicacion_id, $conexion) {
    // Usamos CASE WHEN para contar likes y dislikes en una sola query
    $query = "SELECT 
                SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN tipo = 'dislike' THEN 1 ELSE 0 END) as dislikes
              FROM likes 
              WHERE publicacion_id = ?";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $publicacion_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $conteo = $result->fetch_assoc();
    
    // Retornamos array con los conteos (0 si no hay votos)
    return [
        'likes' => $conteo['likes'] ?? 0,
        'dislikes' => $conteo['dislikes'] ?? 0
    ];
}

/**
 * 🔖 FUNCIÓN: guardarParaLeerMasTarde
 * PROPÓSITO: Guarda o quita una publicación de la lista "leer más tarde"
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $usuario_id: ID del usuario
 *   - $conexion: Conexión a la BD
 * RETORNA: true si se procesó correctamente
 * 
 * LÓGICA: Funciona como toggle (si está guardada la quita, si no está la agrega)
 */
function guardarParaLeerMasTarde($publicacion_id, $usuario_id, $conexion) {
    // Verificamos si ya está guardada
    $query_check = "SELECT id FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt_check = $conexion->prepare($query_check);
    $stmt_check->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        // Ya está guardada, la eliminamos (toggle)
        $query = "DELETE FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    } else {
        // No está guardada, la agregamos
        $query = "INSERT INTO leer_mas_tarde (publicacion_id, usuario_id, fecha_agregado) 
                  VALUES (?, ?, NOW())";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    }
    
    return $stmt->execute();
}

/**
 * 📚 FUNCIÓN: obtenerLeerMasTarde
 * PROPÓSITO: Obtiene la lista de publicaciones guardadas de un usuario
 * PARÁMETROS:
 *   - $usuario_id: ID del usuario
 *   - $conexion: Conexión a la BD
 * RETORNA: Array con las publicaciones guardadas (incluye datos de la publicación)
 */
function obtenerLeerMasTarde($usuario_id, $conexion) {
    // JOIN con publicaciones y publicadores para traer toda la info
    $query = "SELECT p.*, lmt.fecha_agregado, pub.nombre as publicador_nombre
              FROM leer_mas_tarde lmt
              LEFT JOIN publicaciones p ON lmt.publicacion_id = p.id
              LEFT JOIN publicadores pub ON p.publicador_id = pub.id
              WHERE lmt.usuario_id = ?
              ORDER BY lmt.fecha_agregado DESC";
    
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * ⚠️ FUNCIÓN: crearReporte
 * PROPÓSITO: Crea un reporte de publicación o comentario inapropiado
 * PARÁMETROS:
 *   - $tipo: 'publicacion' o 'comentario'
 *   - $referencia_id: ID de la publicación o comentario reportado
 *   - $usuario_id: ID del usuario que hace el reporte
 *   - $motivo: Categoría del reporte (ej: "Contenido ofensivo")
 *   - $descripcion: Descripción adicional (opcional)
 *   - $conexion: Conexión a la BD
 * RETORNA: true si se guardó el reporte
 */
function crearReporte($tipo, $referencia_id, $usuario_id, $motivo, $descripcion, $conexion) {
    $query = "INSERT INTO reportes (tipo, referencia_id, usuario_id, motivo, descripcion, estado, fecha_creacion) 
              VALUES (?, ?, ?, ?, ?, 'pendiente', NOW())";
    
    $stmt = $conexion->prepare($query);
    
    if (!$stmt) {
        return false;
    }
    
    // s=string, i=integer
    $stmt->bind_param("siiss", $tipo, $referencia_id, $usuario_id, $motivo, $descripcion);
    
    return $stmt->execute();
}

/**
 * 🔍 FUNCIÓN: verificarSiGuardada
 * PROPÓSITO: Verifica si una publicación está en la lista "leer más tarde" del usuario
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $usuario_id: ID del usuario
 *   - $conexion: Conexión a la BD
 * RETORNA: true si está guardada, false si no
 */
function verificarSiGuardada($publicacion_id, $usuario_id, $conexion) {
    $query = "SELECT id FROM leer_mas_tarde WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si num_rows > 0, significa que está guardada
    return $result->num_rows > 0;
}

/**
 * 👍 FUNCIÓN: obtenerVotoUsuario
 * PROPÓSITO: Obtiene el voto actual del usuario en una publicación
 * PARÁMETROS:
 *   - $publicacion_id: ID de la publicación
 *   - $usuario_id: ID del usuario
 *   - $conexion: Conexión a la BD
 * RETORNA: 'like', 'dislike' o null si no ha votado
 */
function obtenerVotoUsuario($publicacion_id, $usuario_id, $conexion) {
    $query = "SELECT tipo FROM likes WHERE publicacion_id = ? AND usuario_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("ii", $publicacion_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $voto = $result->fetch_assoc();
        return $voto['tipo']; // Retorna 'like' o 'dislike'
    }
    
    return null; // No ha votado
}
?>
