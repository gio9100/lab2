<?php
// mensajes/debug_contacts.php
// Script para depurar qué contactos se están mostrando

require_once 'init.php';

echo "<h2>Debug de Contactos</h2>";

echo "<h3>Usuario Actual:</h3>";
echo "ID: " . $current_user_id . "<br>";
echo "Rol: " . $current_user_role . "<br>";
echo "Nivel: " . ($current_user_nivel ?? 'N/A') . "<br><br>";

echo "<h3>Parámetro ?as=</h3>";
echo "Valor: " . ($_GET['as'] ?? 'NO DEFINIDO') . "<br><br>";

// Simular la lógica de get_contacts.php
$contacts = [];

if ($current_user_role === 'publicador') {
    echo "<h3>Lógica: PUBLICADOR (solo debe ver admins)</h3>";
    
    $query = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
              FROM admins 
              WHERE estado = 'activo'";
    
    $res = $conn->query($query);
    if ($res) {
        $contacts = $res->fetch_all(MYSQLI_ASSOC);
    }

} elseif ($current_user_role === 'admin') {
    echo "<h3>Lógica: ADMIN (ve otros admins y publicadores)</h3>";
    
    // Otros admins
    $queryAdmins = "SELECT id, nombre, email, nivel as rol_detalle, ultimo_acceso, 'admin' as tipo 
                    FROM admins 
                    WHERE estado = 'activo' AND id != ?";
    $stmt = $conn->prepare($queryAdmins);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $resAdmins = $stmt->get_result();
    if ($resAdmins) {
        $admins = $resAdmins->fetch_all(MYSQLI_ASSOC);
        $contacts = array_merge($contacts, $admins);
    }

    // Publicadores
    $queryPubs = "SELECT id, nombre, email, 'publicador' as rol_detalle, ultimo_acceso, 'publicador' as tipo 
                  FROM publicadores 
                  WHERE estado = 'activo'";
    $resPubs = $conn->query($queryPubs);
    if ($resPubs) {
        $pubs = $resPubs->fetch_all(MYSQLI_ASSOC);
        $contacts = array_merge($contacts, $pubs);
    }
}

echo "<h3>Contactos Obtenidos (" . count($contacts) . "):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Rol Detalle</th></tr>";
foreach ($contacts as $contact) {
    echo "<tr>";
    echo "<td>" . $contact['id'] . "</td>";
    echo "<td>" . $contact['nombre'] . "</td>";
    echo "<td>" . $contact['tipo'] . "</td>";
    echo "<td>" . ($contact['rol_detalle'] ?? 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
