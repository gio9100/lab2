<?php
// Incluimos el archivo de inicialización que conecta a la base de datos y gestiona la sesión
require_once '../init.php';

// Verificamos que el usuario esté logueado, si no, se detiene todo aquí
checkAuth();

// Indicamos al navegador que la respuesta será en formato JSON (texto estructurado para datos)
header('Content-Type: application/json');

// Solo permitimos que este archivo sea llamado mediante el método POST (envío de datos)
// Si intentan entrar directo por la URL (GET), les decimos que no está permitido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leemos los datos que nos envía el JavaScript (el ID del mensaje a borrar)
// file_get_contents('php://input') lee el cuerpo crudo de la petición
$data = json_decode(file_get_contents('php://input'), true);

// Extraemos el ID del mensaje y nos aseguramos que sea un número entero
$message_id = intval($data['message_id'] ?? 0);

// Si el ID es 0 o inválido, devolvemos error
if (!$message_id) {
    echo json_encode(['success' => false, 'error' => 'ID de mensaje inválido']);
    exit;
}

// Preparamos la consulta para buscar el mensaje por su ID
$query = "SELECT remitente_id, remitente_tipo FROM mensajes WHERE id = ?";
$stmt = $conn->prepare($query);
// Vinculamos el parámetro (la "i" significa que es un entero)
$stmt->bind_param("i", $message_id);
// Ejecutamos la búsqueda
$stmt->execute();
// Obtenemos el resultado
$result = $stmt->get_result();

// Si no encontramos ningún mensaje con ese ID, devolvemos error
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Mensaje no encontrado']);
    exit;
}

// Convertimos el resultado de la base de datos a un array asociativo de PHP
$message = $result->fetch_assoc();

// Comparamos el ID y tipo del remitente del mensaje con el usuario actual
// $current_user_id y $current_user_role vienen definidos desde init.php
if ($message['remitente_id'] != $current_user_id || $message['remitente_tipo'] != $current_user_role) {
    // Si no coinciden, significa que está intentando borrar un mensaje ajeno
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar este mensaje']);
    exit;
}

// Preparamos la consulta de eliminación
$deleteQuery = "DELETE FROM mensajes WHERE id = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("i", $message_id);

// Ejecutamos la eliminación
if ($deleteStmt->execute()) {
    // Si todo salió bien, devolvemos éxito
    echo json_encode(['success' => true]);
} else {
    // Si falló la base de datos, devolvemos error
    echo json_encode(['success' => false, 'error' => 'Error al eliminar el mensaje']);
}
?>
