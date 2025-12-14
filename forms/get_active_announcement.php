<?php
// forms/get_active_announcement.php
require_once 'conexion.php';

header('Content-Type: application/json');

try {
    // Buscar anuncio activo
    // activo = 1
    // fecha_fin > NOW() o fecha_fin IS NULL
    $query = "SELECT id, mensaje, tipo, fecha_inicio, fecha_fin 
              FROM anuncios_sistema 
              WHERE activo = 1 
              AND (fecha_fin IS NULL OR fecha_fin > NOW()) 
              ORDER BY id DESC LIMIT 1";
              
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $anuncio = $result->fetch_assoc();
        echo json_encode(['success' => true, 'anuncio' => $anuncio]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No active announcements']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
