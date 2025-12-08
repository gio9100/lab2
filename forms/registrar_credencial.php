<?php
// Registrar credencial emitida en base de datos
session_start();
require_once 'conexion.php';

// Obtener datos JSON del request
$data = json_decode(file_get_contents('php://input'), true);

// Validar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['error' => 'No autorizado']));
}

// Validar que sea el usuario correcto
if ($data['usuario_id'] != $_SESSION['usuario_id']) {
    die(json_encode(['error' => 'No autorizado']));
}

try {
    // Insertar credencial en BD
    $stmt = $conexion->prepare("
        INSERT INTO credenciales_emitidas 
        (usuario_id, user_type, hash_verificacion, fecha_expiracion, ip_generacion) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $ip = $_SERVER['REMOTE_ADDR']; // IP del cliente
    
    $stmt->bind_param("issss", 
        $data['usuario_id'], 
        $data['user_type'], 
        $data['hash'], 
        $data['fecha_expiracion'],
        $ip
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'hash' => $data['hash'],
            'id' => $conexion->insert_id
        ]);
    } else {
        echo json_encode(['error' => 'Error al guardar en BD']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
