<?php
// Iniciamos el buffer de salida para capturar cualquier texto no deseado antes del JSON
ob_start();

// Iniciamos la sesión para poder acceder a las variables de sesión del usuario
session_start();

// Incluimos el archivo de configuración que valida si es administrador
require_once '../forms/admins/config-admin.php';

// Configuramos el encabezado para indicar que la respuesta es JSON y usa UTF-8
header('Content-Type: application/json; charset=utf-8');

// Ejecutamos la función que verifica si el usuario tiene permisos de administrador
requerirAdmin();

// Iniciamos un bloque try para capturar cualquier error que ocurra durante el proceso
try {
    // Verificamos si la variable de conexión $conn existe y si la conexión está viva
    if (!isset($conn) || !$conn->ping()) {
        // Si no hay conexión, lanzamos una excepción con un mensaje descriptivo
        throw new Exception('Error de conexión a la base de datos');
    }

    // Verificamos si se recibió el ID de la publicación mediante el método POST
    if (!isset($_POST['publicacion_id']) || empty($_POST['publicacion_id'])) {
        // Si falta el ID, lanzamos una excepción indicando el error
        throw new Exception('No se recibió el ID de la publicación');
    }

    // Convertimos el ID recibido a un número entero para evitar inyecciones y errores
    $publicacion_id = intval($_POST['publicacion_id']);

    // Verificamos si el archivo de la clase ModeradorLocal existe antes de incluirlo
    if (!file_exists('ModeradorLocal.php')) {
        // Si no existe el archivo, lanzamos un error crítico
        throw new Exception('No se encuentra el archivo de la clase ModeradorLocal.php');
    }

    // Incluimos el archivo que contiene la lógica de moderación
    require_once 'ModeradorLocal.php';

    // Creamos una nueva instancia de la clase ModeradorLocal, pasando la conexión a la BD
    $moderador = new ModeradorLocal($conn);

    // Llamamos al método analizarPublicacion para procesar la publicación con el ID dado
    $resultado = $moderador->analizarPublicacion($publicacion_id);

    // Limpiamos el buffer de salida para eliminar cualquier espacio o warning previo
    ob_clean();

    // Codificamos el resultado en formato JSON, asegurando que los caracteres especiales se vean bien
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    // Capturamos cualquier Excepción o Error fatal que haya ocurrido en el bloque try
    
    // Limpiamos el buffer de salida para asegurar que solo enviamos el JSON de error
    ob_clean();
    
    // Construimos y enviamos una respuesta JSON indicando que hubo un error
    echo json_encode([
        'success' => false, // Indicador de que la operación falló
        'error' => $e->getMessage() // El mensaje técnico del error para depuración
    ], JSON_UNESCAPED_UNICODE);
}

// Terminamos la ejecución del script para asegurar que no se envíe nada más
exit();
?>