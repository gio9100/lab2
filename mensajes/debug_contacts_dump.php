<?php
// Debug script for contacts
require_once 'init.php';
header('Content-Type: text/html');

echo "<h1>Debug Contacts</h1>";
echo "Current User ID: " . ($current_user_id ?? 'NULL') . "<br>";
echo "Current Role: " . ($current_user_role ?? 'NULL') . "<br>";

echo "<h2>Raw Database Admins</h2>";
$res = $conn->query("SELECT id, nombre, email, estado, ultimo_acceso FROM admins");
echo "<table border=1><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Estado</th><th>Ultimo Acceso</th></tr>";
while($row = $res->fetch_assoc()) {
    echo "<tr>";
    foreach($row as $k => $v) echo "<td>$v</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>API Simulation (Your view)</h2>";
// Simulate logic from get_contacts.php
$contacts = [];
if ($current_user_role === 'publicador') {
    $query = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo FROM admins WHERE estado = 'activo'";
    $res = $conn->query($query);
    if($res) $contacts = $res->fetch_all(MYSQLI_ASSOC);
} elseif ($current_user_role === 'admin') {
    $queryAdmins = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo FROM admins WHERE estado = 'activo' AND id != ?";
    $stmt->execute();
    $resAdmins = $stmt->get_result();
    if($resAdmins) $contacts = array_merge($contacts, $resAdmins->fetch_all(MYSQLI_ASSOC));
    $stmt->close(); // Close admin stmt

    // Publisher Query Simulation
    $queryPubs = "SELECT id, nombre, email, 'publicador' as rol_detalle, ultimo_acceso, 'publicador' as tipo FROM publicadores WHERE estado = 'activo'";
    $resPubs = $conn->query($queryPubs);
    if ($resPubs) {
        $pubs = $resPubs->fetch_all(MYSQLI_ASSOC);
        $contacts = array_merge($contacts, $pubs);
    } else {
        echo "Error fetching pubs: " . $conn->error . "<br>";
    }
}

echo "<h3>Merged Contacts (Before Enrichment): " . count($contacts) . "</h3>";

// Enrichment Loop Simulation
echo "<h2>Enrichment Loop Test</h2>";
foreach ($contacts as &$contact) {
    echo "Processing ID: " . $contact['id'] . " (" . $contact['tipo'] . ")... ";
    
    try {
        // 1. Unread Count
        $queryUnread = "SELECT COUNT(*) as total FROM mensajes 
                        WHERE remitente_id = ? AND remitente_tipo = ? 
                        AND destinatario_id = ? AND destinatario_tipo = ? 
                        AND leido = 0";
        $stmtUnread = $conn->prepare($queryUnread);
        if (!$stmtUnread) throw new Exception("Prepare Unread failed: " . $conn->error);
        
        $stmtUnread->bind_param("isis", $contact['id'], $contact['tipo'], $current_user_id, $current_user_role);
        $stmtUnread->execute();
        $resUnread = $stmtUnread->get_result();
        $total = $resUnread->fetch_assoc()['total'];
        $stmtUnread->close(); // <--- FIX
        echo "[Unread: $total] ";
        
        // 2. Last Message
        $queryLast = "SELECT mensaje, fecha_envio FROM mensajes 
                      WHERE (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                      OR (remitente_id = ? AND remitente_tipo = ? AND destinatario_id = ? AND destinatario_tipo = ?)
                      ORDER BY fecha_envio DESC LIMIT 1";
        $stmtLast = $conn->prepare($queryLast);
        if (!$stmtLast) throw new Exception("Prepare LastMsg failed: " . $conn->error);
        
        $stmtLast->bind_param("isisisis", 
            $contact['id'], $contact['tipo'], $current_user_id, $current_user_role,
            $current_user_id, $current_user_role, $contact['id'], $contact['tipo']
        );
        $stmtLast->execute();
        echo "[LastMsg Query OK] ";
        $stmtLast->close(); // <--- FIX
        
    } catch (Exception $e) {
        echo "<b style='color:red'>ERROR: " . $e->getMessage() . "</b>";
    }
    echo "<br>";
}

echo "<h2>Final Data Structure</h2>";
echo "<pre>" . print_r($contacts, true) . "</pre>";
?>
