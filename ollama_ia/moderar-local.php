<?php
// =============================================================================
// 📄 ARCHIVO: moderar-local.php
// =============================================================================
// 
// 🎯 PROPÓSITO:
// Este archivo es el ENDPOINT (punto de entrada) para el sistema de moderación
// LOCAL. Recibe peticiones AJAX desde el panel de moderación y procesa las
// publicaciones usando el ModeradorLocal (sin IA externa como Ollama).
//
// 📥 ENTRADA:
// - Recibe POST con 'publicacion_id'
// - Solo admins autenticados pueden usarlo
//
// 📤 SALIDA:
// - JSON con el resultado del análisis
// - Incluye: decision, razon, confianza, mensaje, icono
// =============================================================================

// -----------------------------------------------------------------------------
// PASO 1: Iniciar sesión de PHP
// -----------------------------------------------------------------------------
// session_start() permite acceder a variables de sesión como $_SESSION['admin_id']
// Esto es necesario para verificar si el usuario es administrador
session_start();

// -----------------------------------------------------------------------------
// PASO 2: Incluir dependencias necesarias
// -----------------------------------------------------------------------------
// config-admin.php: Contiene funciones como esAdministrador() y la conexión $conn
require_once '../forms/admins/config-admin.php';

// ModeradorLocal.php: La clase que hace el análisis de publicaciones
require_once 'ModeradorLocal.php';

// -----------------------------------------------------------------------------
// PASO 3: Configurar respuesta como JSON
// -----------------------------------------------------------------------------
// Le dice al navegador que la respuesta será JSON, no HTML
// Esto es importante para que JavaScript pueda parsear la respuesta correctamente
header('Content-Type: application/json');

// -----------------------------------------------------------------------------
// FUNCIÓN AUXILIAR: enviarRespuesta
// -----------------------------------------------------------------------------
// Esta función simplifica el envío de respuestas JSON
// En lugar de escribir echo json_encode() cada vez, usamos esta función
//
// @param array $datos - Array asociativo con los datos a enviar
// @return void - Termina la ejecución del script
function enviarRespuesta($datos) {
    // json_encode(): Convierte array PHP a formato JSON
    // JSON_UNESCAPED_UNICODE: Permite caracteres especiales (ñ, tildes, emojis)
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    
    // exit(): Termina la ejecución del script inmediatamente
    // Esto evita que se ejecute código adicional después de enviar la respuesta
    exit();
}

// -----------------------------------------------------------------------------
// PASO 4: Verificar que sea una petición POST
// -----------------------------------------------------------------------------
// $_SERVER['REQUEST_METHOD']: Variable global que contiene el método HTTP usado
// Solo aceptamos POST porque es más seguro para operaciones que modifican datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    enviarRespuesta([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}

// -----------------------------------------------------------------------------
// PASO 5: Verificar que el usuario sea administrador
// -----------------------------------------------------------------------------
// esAdministrador(): Función de config-admin.php que verifica $_SESSION['admin_id']
// Si no es admin, enviamos error y terminamos
if (!esAdministrador()) {
    enviarRespuesta([
        'success' => false,
        'error' => 'No tienes permisos'
    ]);
}

// -----------------------------------------------------------------------------
// PASO 6: Obtener y validar el ID de la publicación
// -----------------------------------------------------------------------------
// $_POST['publicacion_id']: Dato enviado desde el formulario/AJAX
// ?? null: Operador de fusión null - si no existe, asigna null
$publicacion_id = $_POST['publicacion_id'] ?? null;

// empty(): Verifica si está vacío (null, "", 0, false, etc.)
if (empty($publicacion_id)) {
    enviarRespuesta([
        'success' => false,
        'error' => 'No se especificó el ID de la publicación'
    ]);
}

// intval(): Convierte a entero, evita inyección SQL
// Ejemplo: intval("5abc") = 5, intval("abc") = 0
$publicacion_id = intval($publicacion_id);

// Verificar que sea un ID válido (mayor a 0)
if ($publicacion_id <= 0) {
    enviarRespuesta([
        'success' => false,
        'error' => 'ID de publicación inválido'
    ]);
}

// -----------------------------------------------------------------------------
// PASO 7: Procesar la moderación
// -----------------------------------------------------------------------------
// try-catch: Manejo de errores - captura excepciones si algo falla
try {
    // Crear instancia del moderador local
    // $conn: Conexión a la base de datos (viene de config-admin.php)
    $moderador = new ModeradorLocal($conn);
    
    // Analizar la publicación
    // Este método hace TODO el trabajo: validaciones, análisis, actualización BD, correos
    $resultado = $moderador->analizarPublicacion($publicacion_id);
    
    // Verificar si hubo error en el análisis
    if (!$resultado['success']) {
        enviarRespuesta([
            'success' => false,
            'error' => $resultado['error'] ?? 'Error desconocido'
        ]);
    }
    
    // ---------------------------------------------------------------------
    // PASO 8: Preparar mensajes para mostrar al usuario
    // ---------------------------------------------------------------------
    // global: Permite acceder a variables globales definidas en config-admin.php
    global $MENSAJE_APROBACION, $MENSAJE_RECHAZO, $MENSAJE_REVISION_MANUAL;
    
    // Variables para el mensaje e icono que se mostrará en el modal
    $mensaje = '';
    $icono = '';
    
    // switch: Estructura de control para múltiples casos
    // Según la decisión del moderador, asignamos mensaje e icono apropiados
    switch ($resultado['decision']) {
        case 'publicado':
            // ?? : Si $MENSAJE_APROBACION no existe, usa el texto por defecto
            $mensaje = $MENSAJE_APROBACION ?? '✅ Publicación aprobada';
            $icono = '✅';
            break; // Salir del switch
            
        case 'rechazada':
            $mensaje = $MENSAJE_RECHAZO ?? '❌ Publicación rechazada';
            $icono = '❌';
            break;
            
        case 'en_revision':
            $mensaje = $MENSAJE_REVISION_MANUAL ?? '⏳ Requiere revisión manual';
            $icono = '⏳';
            break;
            
        default:
            // Caso por defecto si no coincide con ninguno anterior
            $mensaje = 'Análisis completado';
            $icono = 'ℹ️';
    }
    
    // ---------------------------------------------------------------------
    // PASO 9: Enviar respuesta exitosa
    // ---------------------------------------------------------------------
    enviarRespuesta([
        'success' => true,                          // Indica que todo salió bien
        'decision' => $resultado['decision'],       // 'publicado', 'rechazada', 'en_revision'
        'razon' => $resultado['razon'],             // Explicación detallada
        'confianza' => $resultado['confianza'],     // Puntuación 0-100
        'detalles' => [],                           // Array vacío (por compatibilidad)
        'mensaje' => $mensaje,                      // Mensaje para mostrar en el modal
        'icono' => $icono,                          // Emoji para el modal
        'tipo_analisis' => $resultado['tipo_analisis'] // 'moderacion_local' o 'validacion_local'
    ]);
    
} catch (Exception $e) {
    // ---------------------------------------------------------------------
    // MANEJO DE ERRORES
    // ---------------------------------------------------------------------
    // Si ocurre cualquier error (BD, PHP, etc.), lo capturamos aquí
    // $e->getMessage(): Obtiene el mensaje de error de la excepción
    enviarRespuesta([
        'success' => false,
        'error' => 'Error interno: ' . $e->getMessage()
    ]);
}

?>
