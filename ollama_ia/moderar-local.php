<?php
// Endpoint para moderar publicaciones usando el sistema de moderación local
// Requiere permisos de administrador

// ob_start() = inicia buffer de salida para capturar texto no deseado
ob_start();
session_start();
require_once '../forms/admins/config-admin.php';

// header() = configura respuesta como JSON con UTF-8
header('Content-Type: application/json; charset=utf-8');

// Verificar permisos de administrador
requerirAdmin();

// try-catch = manejo de errores
try {
    // mysqli->ping() = verifica si la conexión está activa
    if (!isset($conn) || !$conn->ping()) {
        throw new Exception('Error de conexión a la base de datos');
    }

    if (!isset($_POST['publicacion_id']) || empty($_POST['publicacion_id'])) {
        throw new Exception('No se recibió el ID de la publicación');
    }

    // intval() = convierte a entero para seguridad
    $publicacion_id = intval($_POST['publicacion_id']);

    if (!file_exists('ModeradorLocal.php')) {
        throw new Exception('No se encuentra el archivo de la clase ModeradorLocal.php');
    }

    require_once 'ModeradorLocal.php';

    // Crear instancia del moderador y analizar publicación
    $moderador = new ModeradorLocal($conn);
    $resultado = $moderador->analizarPublicacion($publicacion_id);

    // ob_clean() = limpia el buffer de salida
    ob_clean();

    // JSON_UNESCAPED_UNICODE = muestra acentos correctamente
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Throwable = captura Exception y Error
    ob_clean();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}

exit();
?>