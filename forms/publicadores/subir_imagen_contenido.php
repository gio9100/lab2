<?php
// Iniciar buffer de salida inmediatamente para capturar cualquier error/warning
ob_start();

// Desactivar visualización de errores en la respuesta (rompen el JSON)
ini_set('display_errors', 0);
error_reporting(0);

// Archivo que maneja la subida de imágenes para el contenido de publicaciones
// Se usa desde el editor Quill cuando el publicador inserta una imagen

session_start();
require_once __DIR__ . '/config-publicadores.php';

// Asegurar header JSON
header('Content-Type: application/json');

// Verificar que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    ob_end_clean(); // Limpiar lo que haya
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Verificar que se recibió un archivo
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen o hubo un error en la subida']);
    exit();
}

$archivo = $_FILES['image'];

// Validar tipo de archivo permitido
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($archivo['type'], $tipos_permitidos)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF y WEBP']);
    exit();
}

// Validar tamaño máximo (5MB)
$tamano_maximo = 5 * 1024 * 1024;
if ($archivo['size'] > $tamano_maximo) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'La imagen es demasiado grande. Máximo 5MB']);
    exit();
}

// Crear directorio si no existe
// Usamos ruta absoluta basada en __DIR__ para evitar problemas de "../"
$upload_root = dirname(dirname(__DIR__)); // c:\xampp\htdocs\lab2
$directorio_destino = $upload_root . '/uploads/contenido/';

// Asegurar que la carpeta uploads base exista
if (!file_exists($upload_root . '/uploads')) {
    mkdir($upload_root . '/uploads', 0755, true);
}

if (!file_exists($directorio_destino)) {
    if (!mkdir($directorio_destino, 0755, true)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'No se pudo crear el directorio: ' . $directorio_destino]);
        exit();
    }
}

// Generar nombre único
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
$nombre_archivo = 'img_' . time() . '_' . uniqid() . '.' . $extension;
$ruta_completa = $directorio_destino . $nombre_archivo;

// Mover archivo
if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    // Generar ruta absoluta web para que funcione desde cualquier nivel de directorio (Viewer y Editor)
    // $_SERVER['SCRIPT_NAME'] devuelve la ruta web del script actual, ej: /lab2/forms/publicadores/subir_imagen_contenido.php
    // Necesitamos subir 3 niveles para llegar a la raíz: /lab2
    $ruta_web_script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $ruta_base = dirname(dirname(dirname($ruta_web_script)));
    
    // Asegurar que no quede un punto si estamos en raíz local
    if ($ruta_base === '/' || $ruta_base === '\\') { 
        $ruta_base = ''; 
    }
    
    // Construir la ruta final: /lab2/uploads/contenido/archivo.jpg
    $ruta_relativa = $ruta_base . '/uploads/contenido/' . $nombre_archivo;
    
    // Limpiar posibles dobles slashes
    $ruta_relativa = str_replace('//', '/', $ruta_relativa);
    
    ob_end_clean(); // Limpiar cualquier salida previa
    echo json_encode([
        'success' => true,
        'url' => $ruta_relativa,
        'filename' => $nombre_archivo
    ]);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Error al mover el archivo al servidor']);
}
?>
