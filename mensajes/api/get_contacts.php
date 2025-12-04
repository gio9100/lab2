<?php
// API endpoint para obtener lista de contactos disponibles para chat
// Publicadores ven solo admins, Admins ven otros admins y publicadores

require_once '../init.php';
checkAuth();

$contacts = [];

// Reglas de visibilidad según rol
if ($current_user_role === 'publicador') {
    // Publicadores solo ven administradores activos
    $query = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
              FROM admins 
              WHERE estado = 'activo'";
    
    $res = $conn->query($query);
    if ($res) {
        $contacts = $res->fetch_all(MYSQLI_ASSOC);
    }

} elseif ($current_user_role === 'admin') {
    // Admins ven otros admins y todos los publicadores
    
    // Obtener otros administradores (excluyéndome)
    $queryAdmins = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
                    FROM admins 
                    WHERE estado = 'activo' AND id != ?";
    $stmt = $conn->prepare($queryAdmins);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $resAdmins = $stmt->get_result();
    
    if ($resAdmins) {
        $admins = $resAdmins->fetch_all(MYSQLI_ASSOC);
        // array_merge() = une dos arrays
        $contacts = array_merge($contacts, $admins);
    }

    // Obtener todos los publicadores activos
    $queryPubs = "SELECT id, nombre, email, 'publicador' as rol_detalle, ultimo_acceso, 'publicador' as tipo 
                  FROM publicadores 
                  WHERE estado = 'activo'";
    $resPubs = $conn->query($queryPubs);
    
    if ($resPubs) {
        $pubs = $resPubs->fetch_all(MYSQLI_ASSOC);
        $contacts = array_merge($contacts, $pubs);
    }
}

// Agregar datos de chat a cada contacto
// &$contact = por referencia para modificar el array original
foreach ($contacts as &$contact) {
    
    // Contar mensajes no leídos de este contacto
    $queryUnread = "SELECT COUNT(*) as total FROM mensajes 
                    WHERE remitente_id = ? AND remitente_tipo = ? 
                    AND destinatario_id = ? AND destinatario_tipo = ? 
                    AND leido = 0";
    $stmtUnread = $conn->prepare($queryUnread);
    $stmtUnread->bind_param("isis", $contact['id'], $contact['tipo'], $current_user_id, $current_user_role);
    $stmtUnread->execute();
    $resUnread = $stmtUnread->get_result();
    $contact['mensajes_no_leidos'] = $resUnread->fetch_assoc()['total'];

    // Obtener último mensaje de la conversación
    $queryLast = "SELECT mensaje, fecha_envio FROM mensajes 
                  WHERE (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                  OR (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                  ORDER BY fecha_envio DESC LIMIT 1";
    $stmtLast = $conn->prepare($queryLast);
    $stmtLast->bind_param("isisisis", 
        $contact['id'], $contact['tipo'], $current_user_id, $current_user_role,
        $current_user_id, $current_user_role, $contact['id'], $contact['tipo']
    );
    $stmtLast->execute();
    $resLast = $stmtLast->get_result();
    
    if ($lastMsg = $resLast->fetch_assoc()) {
        $contact['ultimo_mensaje'] = $lastMsg['mensaje'];
        $contact['fecha_ultimo_mensaje'] = $lastMsg['fecha_envio'];
    } else {
        $contact['ultimo_mensaje'] = "";
        $contact['fecha_ultimo_mensaje'] = null;
    }

    // Calcular estado online (última actividad < 5 minutos)
    if ($contact['ultimo_acceso']) {
        // strtotime() = convierte fecha a timestamp
        $lastAccess = strtotime($contact['ultimo_acceso']);
        $now = time();
        $contact['online'] = ($now - $lastAccess) < 300;
    } else {
        $contact['online'] = false;
    }
    
    $contact['avatar'] = null;
}

// Ordenar contactos por mensaje más reciente primero
// usort() = ordena array usando función personalizada
usort($contacts, function($a, $b) {
    $t1 = $a['fecha_ultimo_mensaje'] ? strtotime($a['fecha_ultimo_mensaje']) : 0;
    $t2 = $b['fecha_ultimo_mensaje'] ? strtotime($b['fecha_ultimo_mensaje']) : 0;
    return $t2 - $t1;
});

header('Content-Type: application/json');
echo json_encode($contacts);
?>
