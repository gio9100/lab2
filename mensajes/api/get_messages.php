<?php
// API endpoint para obtener mensajes de una conversación específica
// Marca mensajes como leídos automáticamente

require_once '../init.php';
checkAuth();

$contact_id = $_GET['contact_id'] ?? null;
$contact_type = $_GET['contact_type'] ?? null;

if (!$contact_id || !$contact_type) {
    echo json_encode([]);
    exit;
}

// Obtener mensajes entre yo y el contacto (en ambas direcciones)
$query = "SELECT * FROM mensajes 
          WHERE (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
          OR (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
          ORDER BY fecha_envio ASC";

$stmt = $conn->prepare($query);

// bind_param() con 8 parámetros (yo->contacto, contacto->yo)
$stmt->bind_param("isisisis", 
    $current_user_id, $current_user_role, $contact_id, $contact_type,
    $contact_id, $contact_type, $current_user_id, $current_user_role
);

$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Marcar como leídos los mensajes que me envió el contacto
$queryUpdate = "UPDATE mensajes SET leido = 1 
                WHERE remitente_id = ? AND remitente_tipo = ? 
                AND destinatario_id = ? AND destinatario_tipo = ? 
                AND leido = 0";
$stmtUpdate = $conn->prepare($queryUpdate);
$stmtUpdate->bind_param("isis", $contact_id, $contact_type, $current_user_id, $current_user_role);
$stmtUpdate->execute();

header('Content-Type: application/json');
echo json_encode($messages);
?>
