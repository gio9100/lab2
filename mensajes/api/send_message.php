<?php
// Incluimos el archivo de inicialización que conecta a la base de datos y gestiona la sesión
require_once '../init.php';

// Verificamos que el usuario esté logueado, si no, se detiene todo aquí
checkAuth();

// Leemos el cuerpo de la petición que viene en formato JSON
// file_get_contents('php://input') lee lo que envió el JavaScript
$data = json_decode(file_get_contents('php://input'), true);

// Extraemos los datos necesarios usando el operador ?? null por si faltan
$contact_id = $data['contact_id'] ?? null;      // A quién se lo enviamos (ID)
$contact_type = $data['contact_type'] ?? null;  // Qué tipo de usuario es (admin/publicador)
$message = $data['message'] ?? '';              // El texto del mensaje

// Validamos que tengamos todo lo necesario y que el mensaje no esté vacío (solo espacios)
if (!$contact_id || !$contact_type || empty(trim($message))) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Preparamos la consulta SQL para insertar el nuevo mensaje
// NOW() es una función de SQL que pone la fecha y hora actual automáticamente
$query = "INSERT INTO mensajes (remitente_id, remitente_tipo, destinatario_id, destinatario_tipo, mensaje, fecha_envio) 
          VALUES (?, ?, ?, ?, ?, NOW())";
          
$stmt = $conn->prepare($query);

// Vinculamos los parámetros a los signos de interrogación (?)
// "isiss" significa: integer, string, integer, string, string
$stmt->bind_param("isiss", 
    $current_user_id,   // Yo soy el remitente (ID)
    $current_user_role, // Mi rol (admin/publicador)
    $contact_id,        // El destinatario (ID)
    $contact_type,      // El rol del destinatario
    $message            // El contenido del mensaje
);

// Indicamos que vamos a responder con JSON
header('Content-Type: application/json');

// Ejecutamos la inserción
if ($stmt->execute()) {
    // Si todo salió bien, respondemos con éxito
    echo json_encode(['success' => true]);
} else {
    // Si hubo un error en la base de datos, lo informamos
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>
