<?php
// Incluimos el archivo de inicialización que conecta a la base de datos y gestiona la sesión
require_once '../init.php';

// Verificamos que el usuario esté logueado, si no, se detiene todo aquí
checkAuth();

// Obtenemos el ID y el tipo (admin/publicador) de la persona con la que queremos chatear
// Usamos el operador ?? null para evitar errores si no se envían estos datos
$contact_id = $_GET['contact_id'] ?? null;
$contact_type = $_GET['contact_type'] ?? null;

// Si falta alguno de los datos obligatorios, devolvemos una lista vacía y terminamos
if (!$contact_id || !$contact_type) {
    echo json_encode([]);
    exit;
}

// Preparamos la consulta SQL para buscar los mensajes.
// Necesitamos los mensajes donde:
// A) Yo soy el remitente Y el contacto es el destinatario (Mensajes que envié)
// O (OR)
// B) El contacto es el remitente Y yo soy el destinatario (Mensajes que recibí)
// Los ordenamos por fecha de envío ascendente (del más viejo al más nuevo) para leerlos en orden
$query = "SELECT * FROM mensajes 
          WHERE (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
          OR (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
          ORDER BY fecha_envio ASC";

$stmt = $conn->prepare($query);

// Vinculamos los parámetros a la consulta.
// "isisisis" significa: integer, string, integer, string (repetido dos veces)
// Grupo 1: Yo -> Contacto
// Grupo 2: Contacto -> Yo
$stmt->bind_param("isisisis", 
    $current_user_id, $current_user_role, $contact_id, $contact_type,
    $contact_id, $contact_type, $current_user_id, $current_user_role
);

// Ejecutamos la consulta y obtenemos los resultados
$stmt->execute();
$result = $stmt->get_result();
// Convertimos todos los mensajes a un array asociativo
$messages = $result->fetch_all(MYSQLI_ASSOC);

// Si estoy abriendo el chat, significa que estoy leyendo los mensajes.
// Así que actualizamos la base de datos para marcar como leídos (leido = 1)
// SOLO los mensajes que el contacto me envió a mí y que aún no estaban leídos.
$queryUpdate = "UPDATE mensajes SET leido = 1 
                WHERE remitente_id = ? AND remitente_tipo = ? 
                AND destinatario_id = ? AND destinatario_tipo = ? 
                AND leido = 0";
$stmtUpdate = $conn->prepare($queryUpdate);
// Parámetros: ID del contacto (remitente), Tipo del contacto, Mi ID (destinatario), Mi Tipo
$stmtUpdate->bind_param("isis", $contact_id, $contact_type, $current_user_id, $current_user_role);
$stmtUpdate->execute();

// Indicamos que devolvemos JSON y enviamos los mensajes encontrados
header('Content-Type: application/json');
echo json_encode($messages);
?>
