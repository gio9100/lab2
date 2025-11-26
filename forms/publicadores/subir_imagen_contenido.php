<?php
// Abrimos PHP
session_start();
// Iniciamos la sesión
require_once __DIR__ . '/config-publicadores.php';
// Traemos la configuración de publicadores
// __DIR__ es una constante que contiene el directorio actual del archivo

// Verificamos que el publicador esté logueado
if (!isset($_SESSION['publicador_id'])) {
    // Si no hay sesión
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    // json_encode() convierte un array de PHP a formato JSON
    // JSON es un formato de texto para intercambiar datos
    // Ejemplo: ['success' => false] se vuelve {"success":false}
    exit();
    // Detenemos el código
}

// Verificamos que se recibió un archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    // $_FILES['imagen']['error'] contiene el código de error de la subida
    // UPLOAD_ERR_OK es una constante que significa "sin errores" (valor 0)
    echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen']);
    exit();
}

$archivo = $_FILES['imagen'];
// Guardamos la info del archivo en una variable

// Validamos el tipo de archivo
$tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
// Array con los tipos MIME que aceptamos
if (!in_array($archivo['type'], $tipos_permitidos)) {
    // in_array() busca si el tipo está en la lista
    echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG, GIF y WEBP']);
    exit();
}

// Validamos el tamaño (máximo 5MB)
$tamano_maximo = 5 * 1024 * 1024;
// 5 * 1024 * 1024 = 5,242,880 bytes = 5MB
// 1024 bytes = 1 KB, 1024 KB = 1 MB
if ($archivo['size'] > $tamano_maximo) {
    // Si el archivo es muy grande
    echo json_encode(['success' => false, 'error' => 'La imagen es demasiado grande. Máximo 5MB']);
    exit();
}

// Creamos el directorio si no existe
$directorio_destino = __DIR__ . '/../../uploads/contenido/';
// __DIR__ es el directorio actual
// /../../ sube dos niveles (de publicadores a forms a Lab)
if (!file_exists($directorio_destino)) {
    // Si la carpeta no existe
    mkdir($directorio_destino, 0755, true);
    // mkdir() crea una carpeta
    // 0755 son los permisos (lectura/escritura para el dueño, lectura para otros)
    // true hace que cree carpetas intermedias si no existen
}

// Generamos un nombre único para el archivo
$extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
// pathinfo() extrae información de una ruta
// PATHINFO_EXTENSION saca solo la extensión (jpg, png, etc)
$nombre_archivo = 'img_' . time() . '_' . uniqid() . '.' . $extension;
// time() devuelve la fecha/hora actual en segundos (timestamp)
// uniqid() genera un ID único basado en el tiempo en microsegundos
// Esto hace nombres únicos como: img_1700000000_abc123def.jpg
$ruta_completa = $directorio_destino . $nombre_archivo;
// Ruta completa donde guardaremos la imagen

// Movemos el archivo a su ubicación final
if (move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
    // move_uploaded_file() mueve el archivo temporal a la carpeta final
    // Es más seguro que copy() porque verifica que sea un archivo subido por POST
    
    // Ruta relativa para usar en el contenido
    $ruta_relativa = 'uploads/contenido/' . $nombre_archivo;
    // Esta es la ruta que se usará en el HTML del contenido
    
    echo json_encode([
        'success' => true,
        'url' => $ruta_relativa,
        'filename' => $nombre_archivo
    ]);
    // Devolvemos JSON con la información del archivo subido
} else {
    // Si falló al mover el archivo
    echo json_encode(['success' => false, 'error' => 'Error al guardar la imagen']);
}
?>
