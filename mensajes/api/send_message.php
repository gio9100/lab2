<?php
// API endpoint para enviar un nuevo mensaje

require_once '../init.php';
checkAuth();

// file_get_contents('php://input') = lee datos JSON enviados por JavaScript
// json_decode() = convierte JSON a array PHP
$data = json_decode(file_get_contents('php://input'), true);

$contact_id = $data['contact_id'] ?? null;
$contact_type = $data['contact_type'] ?? null;
$message = $data['message'] ?? '';

// Validar datos obligatorios
if (!$contact_id || !$contact_type || empty(trim($message))) {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit;
}

// Insertar mensaje en la base de datos
// NOW() = función MySQL que inserta fecha/hora actual
$query = "INSERT INTO mensajes (remitente_id, remitente_tipo, destinatario_id, destinatario_tipo, mensaje, fecha_envio) 
          VALUES (?, ?, ?, ?, ?, NOW())";
          
$stmt = $conn->prepare($query);

$stmt->bind_param("isiss", 
    $current_user_id,
    $current_user_role,
    $contact_id,
    $contact_type,
    $message
);

header('Content-Type: application/json');

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}
?>
