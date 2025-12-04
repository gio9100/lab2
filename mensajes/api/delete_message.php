<?php
// API endpoint para eliminar un mensaje (solo el remitente puede eliminarlo)

require_once '../init.php';
checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// intval() = convierte a entero para seguridad
$message_id = intval($data['message_id'] ?? 0);

if (!$message_id) {
    echo json_encode(['success' => false, 'error' => 'ID de mensaje inválido']);
    exit;
}

// Buscar el mensaje para verificar propiedad
$query = "SELECT remitente_id, remitente_tipo FROM mensajes WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $message_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Mensaje no encontrado']);
    exit;
}

$message = $result->fetch_assoc();

// Verificar que el usuario actual sea el remitente
if ($message['remitente_id'] != $current_user_id || $message['remitente_tipo'] != $current_user_role) {
    echo json_encode(['success' => false, 'error' => 'No tienes permiso para eliminar este mensaje']);
    exit;
}

// Eliminar mensaje
$deleteQuery = "DELETE FROM mensajes WHERE id = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("i", $message_id);

if ($deleteStmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar el mensaje']);
}
?>
