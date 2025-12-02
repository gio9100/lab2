<?php
// ============================================================================
// 📄 ARCHIVO: guardar_publicacion.php
// ============================================================================
// PROPÓSITO: Procesar y guardar nuevas publicaciones creadas por publicadores
//
// FLUJO DEL ARCHIVO:
// 1. Verificar que el publicador esté autenticado
// 2. Validar los datos del formulario
// 3. Guardar la publicación en la base de datos
// 4. Si el estado es 'revision', notificar a los administradores por correo
// 5. Redirigir al panel del publicador con mensaje de éxito/error
//
// SEGURIDAD:
// - Requiere sesión activa de publicador
// - Validación de campos obligatorios
// - Sanitización de datos con trim()
// - Conversión segura de IDs con intval()
// ============================================================================

// Iniciamos la sesión para acceder a los datos del publicador logueado
session_start();

// Incluimos el archivo de configuración con las funciones de base de datos
require_once __DIR__ . '/config-publicadores.php';

// ====================================================================
// INCLUIR EMAILHELPER PARA ENVÍO DE CORREOS
// ====================================================================
require_once __DIR__ . '/../EmailHelper.php';

// ====================================================================
// VERIFICAR AUTENTICACIÓN
// ====================================================================
// Si no hay un publicador logueado, redirigimos al login
if (!isset($_SESSION['publicador_id'])) {
    header('Location: login.php');
    exit();
}

// ====================================================================
// PROCESAR EL FORMULARIO (solo si es POST)
// ====================================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // ================================================================
    // PASO 1: OBTENER Y LIMPIAR LOS DATOS DEL FORMULARIO
    // ================================================================
    $titulo = trim($_POST["titulo"] ?? "");              // Título de la publicación
    $contenido = trim($_POST["contenido"] ?? "");        // Contenido HTML completo
    $resumen = trim($_POST["resumen"] ?? "");            // Resumen breve
    $categoria_id = intval($_POST["categoria_id"] ?? 0); // ID de la categoría
    $tipo = $_POST["tipo"] ?? "articulo";                // Tipo: articulo, caso_clinico, etc.
    $tags = trim($_POST["tags"] ?? "");                  // Etiquetas separadas por comas
    $estado = $_POST["estado"] ?? "borrador";            // Estado: borrador, revision, publicado
    $publicador_id = intval($_POST["publicador_id"] ?? 0); // ID del publicador
    $publicador_nombre = $_SESSION['publicador_nombre'] ?? 'Un publicador'; // Nombre del publicador
    
    // ================================================================
    // PASO 2: VALIDAR CAMPOS OBLIGATORIOS
    // ================================================================
    if ($titulo === "" || $contenido === "" || $categoria_id === 0) {
        // Si falta algún campo obligatorio, mostramos error
        $_SESSION['publicador_mensaje'] = "Completa todos los campos obligatorios";
        $_SESSION['publicador_tipo_mensaje'] = "error";
        header("Location: crear_nueva_publicacion.php");
        exit();
    }
    
    // ================================================================
    // PASO 3: PREPARAR DATOS PARA INSERTAR EN LA BASE DE DATOS
    // ================================================================
    $datos_publicacion = [
        'titulo' => $titulo,
        'contenido' => $contenido,
        'resumen' => $resumen,
        'publicador_id' => $publicador_id,
        'categoria_id' => $categoria_id,
        'tipo' => $tipo,
        'tags' => $tags,
        'estado' => $estado
    ];
    
    // ================================================================
    // PASO 4: INTENTAR GUARDAR LA PUBLICACIÓN
    // ================================================================
    if (crearPublicacion($datos_publicacion, $conn)) {
        
        // ============================================================
        // PASO 5: SI EL ESTADO ES 'REVISION', NOTIFICAR A LOS ADMINS
        // ============================================================
        // Solo enviamos correo si el publicador envió la publicación para revisión
        if ($estado === 'revision') {
            enviarNotificacionAdmin($titulo, $publicador_nombre, $tipo, $conn);
        }
        
        // ================================================================
        // PASO 6: REDIRECCIONAR AL DASHBOARD CON MENSAJE DE ÉXITO
        // ================================================================
        $_SESSION['publicador_mensaje'] = "Publicación creada exitosamente. " . ($estado == 'revision' ? "Enviada para revisión." : "Guardada como borrador.");
        $_SESSION['publicador_tipo_mensaje'] = "success";
        
        header("Location: index-publicadores.php");
        exit();
        
    } else {
        // Si hubo error al guardar, mostramos mensaje de error
        $_SESSION['publicador_mensaje'] = "Error al crear la publicación";
        $_SESSION['publicador_tipo_mensaje'] = "error";
        header("Location: crear_nueva_publicacion.php");
        exit();
    }
    
} else {
    // Si no es POST, redirigimos al formulario
    header("Location: crear_nueva_publicacion.php");
    exit();
}

/**
 * ============================================================================
 * FUNCIÓN: enviarNotificacionAdmin
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Envía un correo a TODOS los administradores activos notificándoles que
 * hay una nueva publicación pendiente de revisión
 * 
 * ¿CUÁNDO SE USA?
 * Se llama automáticamente cuando un publicador envía una publicación con
 * estado 'revision' (línea 50)
 * 
 * PARÁMETROS:
 * @param string $titulo_publicacion - Título de la publicación enviada
 * @param string $nombre_publicador - Nombre del publicador que la envió
 * @param string $tipo_contenido - Tipo de contenido (articulo, caso_clinico, etc.)
 * @param mysqli $conn - Conexión a la base de datos
 * 
 * RETORNA:
 * void - No retorna nada, solo envía correos
 * 
 * EJEMPLO DE USO:
 * enviarNotificacionAdmin('Nuevos avances en hematología', 'Dr. Juan Pérez', 'articulo', $conn);
 */
function enviarNotificacionAdmin($titulo_publicacion, $nombre_publicador, $tipo_contenido, $conn) {
    
    // ====================================================================
    // PASO 1: OBTENER TODOS LOS ADMINISTRADORES ACTIVOS
    // ====================================================================
    $query = "SELECT email, nombre FROM admins WHERE estado = 'activo'";
    $result = $conn->query($query);
    
    // Si no hay administradores activos, salimos
    if (!$result || $result->num_rows === 0) {
        return;
    }
    
    // ====================================================================
    // PASO 2: PREPARAR EL CONTENIDO DEL CORREO
    // ====================================================================
    $asunto = "📝 Nueva Publicación Pendiente de Revisión";
    
    $mensaje_html = "
        <p>Se ha enviado una nueva publicación para revisión en Lab Explorer.</p>
        <h3>📋 Detalles de la Publicación:</h3>
        <ul>
            <li><strong>Título:</strong> " . htmlspecialchars($titulo_publicacion) . "</li>
            <li><strong>Publicador:</strong> " . htmlspecialchars($nombre_publicador) . "</li>
            <li><strong>Tipo:</strong> " . htmlspecialchars(ucfirst($tipo_contenido)) . "</li>
            <li><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</li>
        </ul>
        <p>Por favor, revisa la publicación desde el panel de administración y procede con su aprobación o rechazo.</p>
    ";
    
    // ====================================================================
    // PASO 3: ENVIAR CORREO A CADA ADMINISTRADOR
    // ====================================================================
    while ($admin = $result->fetch_assoc()) {
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