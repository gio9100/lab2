<?php
// Archivo que maneja la subida de imágenes para el contenido de publicaciones
// Se usa desde el editor Quill cuando el publicador inserta una imagen

session_start();
require_once __DIR__ . '/config-publicadores.php';

// Verificar que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    // JSON = JavaScript Object Notation, formato de texto para intercambiar datos entre servidor y navegador
    // json_encode() = convierte array PHP a texto JSON que JavaScript puede leer
    // Ejemplo: ['success' => false] se convierte en {"success":false}
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit();
}

// Verificar que se recibió un archivo
// $_FILES = array global con información de archivos subidos
// UPLOAD_ERR_OK = constante que indica subida exitosa (valor 0)
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen']);
    exit();
}

$archivo = $_FILES['imagen'];

// Validar tipo de archivo permitido
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
// in_array() = verifica si un valor existe en un array
if (!in_array($archivo['type'], $tipos_permitidos)) {
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido. Solo JPG, PNG, GIF y WEBP']);
    exit();
}

// Validar tamaño máximo (5MB)
$tamano_maximo = 5 * 1024 * 1024;  // 1024 bytes = 1KB, 1024KB = 1MB
if ($archivo['size'] > $tamano_maximo) {
    echo json_encode(['success' => false, 'error' => 'La imagen es demasiado grande. Máximo 5MB']);
    exit();
}

// Crear directorio si no existe
// __DIR__ = directorio actual del archivo
$directorio_destino = __DIR__ . '/../../uploads/contenido/';
if (!file_exists($directorio_destino)) {
    // mkdir() = crea carpeta
    // 0755 = permisos (lectura/escritura para dueño, solo lectura para otros)
    // true = crea carpetas intermedias si no existen
    mkdir($directorio_destino, 0755, true);
}

// Generar nombre único para evitar sobrescribir archivos
// pathinfo() = extrae información de una ruta de archivo
// PATHINFO_EXTENSION = obtiene solo la extensión (.jpg, .png, etc)
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
// time() = timestamp actual en segundos desde 1970
// uniqid() = genera ID único basado en microsegundos
$nombre_archivo = 'img_' . time() . '_' . uniqid() . '.' . $extension;
$ruta_completa = $directorio_destino . $nombre_archivo;

// Mover archivo temporal a ubicación final
// move_uploaded_file() = mueve archivo subido (más seguro que copy())
if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    // Ruta relativa para usar en el HTML del editor
    $ruta_relativa = 'uploads/contenido/' . $nombre_archivo;
    
    // Devolver JSON con la URL de la imagen subida
    // El editor Quill usará esta URL para mostrar la imagen
    echo json_encode([
        'success' => true,
        'url' => $ruta_relativa,
        'filename' => $nombre_archivo
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen']);
}
?>
