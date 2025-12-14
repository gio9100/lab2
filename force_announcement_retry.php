<?php
// force_announcement_retry.php
require 'forms/admins/config-admin.php'; 

// 1. Limpiar anteriores
$conn->query("DELETE FROM anuncios_sistema");

// 2. Insertar uno nuevo y activo
$sql = "INSERT INTO anuncios_sistema (mensaje, tipo, activo, fecha_fin) VALUES ('¡Anuncio de Prueba! Si ves esto, el sistema funciona.', 'success', 1, DATE_ADD(NOW(), INTERVAL 1 DAY))";

if ($conn->query($sql)) {
    echo "Anuncio de prueba creado exitosamente.";
} else {
    echo "Error: " . $conn->error;
}
?>
