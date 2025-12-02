<?php
// Incluimos el archivo de inicialización que conecta a la base de datos y gestiona la sesión
require_once '../init.php';

// Verificamos que el usuario esté logueado, si no, se detiene todo aquí
checkAuth();

// Inicializamos el array donde guardaremos todos los contactos encontrados
$contacts = [];

// Reglas de visibilidad:
// - Publicadores: Solo ven admins
// - Admins: Ven otros admins y publicadores (excepto a sí mismos)

if ($current_user_role === 'publicador') {
    // CASO 1: SI SOY PUBLICADOR
    // Solo puedo ver a los administradores activos para pedir ayuda o soporte
    $query = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
              FROM admins 
              WHERE estado = 'activo'";
    
    // Ejecutamos la consulta directa
    $res = $conn->query($query);
    if ($res) {
        // Guardamos todos los admins encontrados en nuestro array de contactos
        $contacts = $res->fetch_all(MYSQLI_ASSOC);
    }

} elseif ($current_user_role === 'admin') {
    // CASO 2: SI SOY ADMINISTRADOR
    // Puedo ver a otros administradores y a todos los publicadores
    
    // 1. Obtener otros administradores (excluyéndome a mí mismo)
    $queryAdmins = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
                    FROM admins 
                    WHERE estado = 'activo' AND id != ?";
    $stmt = $conn->prepare($queryAdmins);
    // Vinculamos mi propio ID para excluirme de la lista
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $resAdmins = $stmt->get_result();
    
    if ($resAdmins) {
        // Convertimos los resultados a array
        $admins = $resAdmins->fetch_all(MYSQLI_ASSOC);
        // Los agregamos a la lista general de contactos
        $contacts = array_merge($contacts, $admins);
    }

    // 2. Obtener todos los publicadores activos
    $queryPubs = "SELECT id, nombre, email, 'publicador' as rol_detalle, ultimo_acceso, 'publicador' as tipo 
                  FROM publicadores 
                  WHERE estado = 'activo'";
    $resPubs = $conn->query($queryPubs);
    
    if ($resPubs) {
        // Convertimos los resultados a array
        $pubs = $resPubs->fetch_all(MYSQLI_ASSOC);
        // Los agregamos también a la lista general
        $contacts = array_merge($contacts, $pubs);
    }
}

// Ahora recorremos cada contacto encontrado para agregarle datos del chat
// Usamos &$contact (por referencia) para poder modificar el array original directamente

foreach ($contacts as &$contact) {
    
    // --- A. Contar mensajes no leídos ---
    // Buscamos cuántos mensajes me ha enviado este contacto que yo no he leído aún
    $queryUnread = "SELECT COUNT(*) as total FROM mensajes 
                    WHERE remitente_id = ? AND remitente_tipo = ? 
                    AND destinatario_id = ? AND destinatario_tipo = ? 
                    AND leido = 0";
    $stmtUnread = $conn->prepare($queryUnread);
    // Parámetros: ID del contacto, Tipo del contacto, Mi ID, Mi Tipo
    $stmtUnread->bind_param("isis", $contact['id'], $contact['tipo'], $current_user_id, $current_user_role);
    $stmtUnread->execute();
    $resUnread = $stmtUnread->get_result();
    // Guardamos el número total en el array del contacto
    $contact['mensajes_no_leidos'] = $resUnread->fetch_assoc()['total'];

    // --- B. Obtener el último mensaje de la conversación ---
    // Buscamos el mensaje más reciente entre nosotros dos (ya sea enviado por mí o por él)
    // Esto sirve para mostrar la vista previa en la lista de chats
    $queryLast = "SELECT mensaje, fecha_envio FROM mensajes 
                  WHERE (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                  OR (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                  ORDER BY fecha_envio DESC LIMIT 1";
    $stmtLast = $conn->prepare($queryLast);
    // Tenemos que pasar los IDs dos veces porque la condición es (A envia a B) O (B envia a A)
    $stmtLast->bind_param("isisisis", 
        $contact['id'], $contact['tipo'], $current_user_id, $current_user_role,
        $current_user_id, $current_user_role, $contact['id'], $contact['tipo']
    );
    $stmtLast->execute();
    $resLast = $stmtLast->get_result();
    
    // Si encontramos un mensaje previo
    if ($lastMsg = $resLast->fetch_assoc()) {
        $contact['ultimo_mensaje'] = $lastMsg['mensaje'];
        $contact['fecha_ultimo_mensaje'] = $lastMsg['fecha_envio'];
    } else {
        // Si nunca hemos hablado
        $contact['ultimo_mensaje'] = "";
        $contact['fecha_ultimo_mensaje'] = null;
    }

    // --- C. Calcular Estado Online ---
    // Verificamos si su última actividad fue hace menos de 5 minutos (300 segundos)
    if ($contact['ultimo_acceso']) {
        $lastAccess = strtotime($contact['ultimo_acceso']);
        $now = time();
        // Si la diferencia es menor a 300 segundos, lo consideramos online
        $contact['online'] = ($now - $lastAccess) < 300;
    } else {
        $contact['online'] = false;
    }
    
    // Inicializamos el avatar en null (el frontend se encargará de poner uno por defecto)
    $contact['avatar'] = null;
}

// Ordenamos los contactos para que los que tienen mensajes más recientes aparezcan primero
usort($contacts, function($a, $b) {
    // Convertimos las fechas a timestamp (números) para comparar
    $t1 = $a['fecha_ultimo_mensaje'] ? strtotime($a['fecha_ultimo_mensaje']) : 0;
    $t2 = $b['fecha_ultimo_mensaje'] ? strtotime($b['fecha_ultimo_mensaje']) : 0;
    // Restamos B - A para orden descendente (el más nuevo primero)
    return $t2 - $t1;
});

// Indicamos que devolvemos JSON y enviamos el array final
header('Content-Type: application/json');
echo json_encode($contacts);
?>
