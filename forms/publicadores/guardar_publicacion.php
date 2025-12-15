<?php
// Archivo que procesa el formulario de crear nueva publicación
// Guarda la publicación en la base de datos y maneja la imagen principal

// session_start() = inicia sesión para acceder a datos del publicador logueado
session_start();
// require_once = incluye archivos necesarios solo una vez
require_once __DIR__ . '/config-publicadores.php';  // Funciones de publicadores
require_once __DIR__ . '/../EmailHelper.php';        // Para enviar correos

// Verificar que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    // header() = redirige a otra página
    header('Location: login.php');
    exit(); // Detiene el script
}

// Procesar solo si el formulario se envió por POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Obtener y limpiar datos del formulario
    // trim() = quita espacios al inicio y final
    $titulo = trim($_POST["titulo"] ?? "");
    $contenido = trim($_POST["contenido"] ?? "");
    $resumen = trim($_POST["resumen"] ?? "");
    // intval() = convierte a número entero (seguridad)
    $categoria_id = intval($_POST["categoria_id"] ?? 0);
    $tipo = $_POST["tipo"] ?? "articulo";  // Tipo de publicación
    $tags = trim($_POST["tags"] ?? "");
    $estado = $_POST["estado"] ?? "borrador";  // borrador, revision, publicado
    $publicador_id = $_SESSION['publicador_id'];
    $publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Un publicador';

    // ==========================================
    // MODERACIÓN AUTOMÁTICA
    // ==========================================
    require_once dirname(__DIR__) . '/funciones_moderacion.php';
    
    $texto_a_revisar = $titulo . " " . strip_tags($contenido);
    $resultado_moderacion = moderarContenido($texto_a_revisar, $conn);

    if ($resultado_moderacion['accion'] === 'rechazar') {
        $_SESSION['publicador_mensaje'] = "Publicación rechazada automáticamente: " . $resultado_moderacion['motivo'];
        $_SESSION['publicador_tipo_mensaje'] = "error";
        header("Location: crear_nueva_publicacion.php");
        exit();
    }

    if ($resultado_moderacion['accion'] === 'asteriscos') {
        $titulo = moderarContenido($titulo, $conn)['texto'];
        // Nota: moderar contenido HTML con regex simple puede ser riesgoso, pero aceptable para MVP
        $contenido = moderarContenido($contenido, $conn)['texto'];
    }
    // ==========================================
    
    // Validar que los campos obligatorios no estén vacíos
    // El contenido puede estar vacío si se sube un archivo
    $tiene_archivo = isset($_FILES['archivo_contenido']) && $_FILES['archivo_contenido']['error'] === UPLOAD_ERR_OK;
    
    if ($titulo === "" || ($contenido === "" && !$tiene_archivo) || $categoria_id === 0) {
        // Guardar mensaje de error en sesión para mostrarlo después
        $_SESSION['publicador_mensaje'] = "Completa todos los campos obligatorios (Texto o Archivo)";
        $_SESSION['publicador_tipo_mensaje'] = "error";
        header("Location: crear_nueva_publicacion.php");
        exit();
    }
    
    // Procesar imagen principal si el usuario subió una
    $imagen_principal = null;
    // $_FILES = array con archivos subidos
    // UPLOAD_ERR_OK = constante que indica subida exitosa
    if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
        // __DIR__ = directorio actual del archivo
        $upload_dir = __DIR__ . '/../../uploads/';
        
        // file_exists() = verifica si existe un archivo/carpeta
        if (!file_exists($upload_dir)) {
            // mkdir() = crea carpeta
            // 0755 = permisos (lectura/escritura para dueño)
            // true = crea carpetas intermedias si no existen
            mkdir($upload_dir, 0755, true);
        }
        
        // pathinfo() = obtiene información de una ruta
        // PATHINFO_EXTENSION = solo la extensión (.jpg, .png, etc)
        // strtolower() = convierte a minúsculas
        $file_extension = strtolower(pathinfo($_FILES['imagen_principal']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // in_array() = verifica si un valor está en un array
        if (in_array($file_extension, $allowed_extensions)) {
            // time() = timestamp actual (segundos desde 1970)
            // uniqid() = genera ID único
            $new_filename = 'pub_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            // move_uploaded_file() = mueve archivo temporal a ubicación final
            if (move_uploaded_file($_FILES['imagen_principal']['tmp_name'], $upload_path)) {
                $imagen_principal = $new_filename;
            }
        }
    }
    
    // Procesar archivo de contenido (PDF, Doc, Imagen) si se subió
    $archivo_url = null;
    $tipo_archivo = null;
    
    // Verificar si se subió archivo_contenido
    if (isset($_FILES['archivo_contenido']) && $_FILES['archivo_contenido']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../uploads/';
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = $_FILES['archivo_contenido']['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_doc_extensions = ['pdf', 'doc', 'docx'];
        $allowed_img_extensions = ['jpg', 'jpeg', 'png', 'webp'];
        
        // Determinar tipo de archivo
        if (in_array($file_extension, $allowed_doc_extensions)) {
            $tipo_archivo = $file_extension; // 'pdf', 'doc'
        } elseif (in_array($file_extension, $allowed_img_extensions)) {
            $tipo_archivo = 'imagen_contenido'; // Distinguir de imagen principal
        }
        
        if ($tipo_archivo) {
            $new_filename = 'doc_' . time() . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['archivo_contenido']['tmp_name'], $upload_path)) {
                $archivo_url = $new_filename;
            }
        }
    }
    
    // Preparar array con todos los datos de la publicación
    $datos_publicacion = [
        'titulo' => $titulo,
        'contenido' => $contenido,
        'resumen' => $resumen,
        'publicador_id' => $publicador_id,
        'categoria_id' => $categoria_id,
        'tipo' => $tipo,
        'tags' => $tags,
        'estado' => $estado,
        'imagen_principal' => $imagen_principal,
        'archivo_url' => $archivo_url,
        'tipo_archivo' => $tipo_archivo
    ];
    
    // Intentar guardar la publicación en la base de datos
    if (crearPublicacion($datos_publicacion, $conn)) {
        
        // Si el estado es 'revision', notificar a los administradores
        if ($estado === 'revision') {
            enviarNotificacionAdmin($titulo, $publicador_nombre, $tipo, $conn);
        }
        
        // Mensaje de éxito según el estado
        // Operador ternario: condición ? si_true : si_false
        $_SESSION['publicador_mensaje'] = "Publicación creada exitosamente. " . ($estado == 'revision' ? "Enviada para revisión." : "Guardada como borrador.");
        $_SESSION['publicador_tipo_mensaje'] = "success";
        header("Location: index-publicadores.php");
        exit();
        
    } else {
        $_SESSION['publicador_mensaje'] = "Error al crear la publicación";
        $_SESSION['publicador_tipo_mensaje'] = "error";
        header("Location: crear_nueva_publicacion.php");
        exit();
    }
    
} else {
    // Si no es POST, redirigir al formulario
    header("Location: crear_nueva_publicacion.php");
    exit();
}

// Función que envía correo a todos los administradores activos
// Se ejecuta cuando una publicación se envía para revisión
function enviarNotificacionAdmin($titulo_publicacion, $nombre_publicador, $tipo_contenido, $conn) {
    // Obtener todos los admins activos de la base de datos
    $query = "SELECT email, nombre FROM admins WHERE estado = 'activo'";
    // query() = ejecuta consulta SQL directa (sin parámetros)
    $result = $conn->query($query);
    
    // num_rows = cantidad de resultados
    if (!$result || $result->num_rows === 0) {
        return; // Salir si no hay admins
    }
    
    $asunto = "📝 Nueva Publicación Pendiente de Revisión";
    
    // Construir mensaje HTML con los detalles
    // htmlspecialchars() = convierte caracteres especiales a HTML seguro
    // ucfirst() = primera letra en mayúscula
    $mensaje_html = "
        <p>Se ha enviado una nueva publicación para revisión en Lab Explora.</p>
        <h3>📋 Detalles de la Publicación:</h3>
        <ul>
            <li><strong>Título:</strong> " . htmlspecialchars($titulo_publicacion) . "</li>
            <li><strong>Publicador:</strong> " . htmlspecialchars($nombre_publicador) . "</li>
            <li><strong>Tipo:</strong> " . htmlspecialchars(ucfirst($tipo_contenido)) . "</li>
            <li><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</li>
        </ul>
        <p>Por favor, revisa la publicación desde el panel de administración.</p>
    ";
    
    // while = bucle que se repite mientras haya resultados
    // fetch_assoc() = obtiene siguiente fila como array
    while ($admin = $result->fetch_assoc()) {
        // Enviar correo a cada administrador
        EmailHelper::enviarCorreo(
            $admin['email'],
            $asunto,
            $mensaje_html,
            'Ver Publicaciones Pendientes',
            'http://localhost/lab/forms/admins/gestionar-publicaciones.php'
        );
    }
}
?>