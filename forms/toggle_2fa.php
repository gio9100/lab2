<?php
// Activar o desactivar 2FA desde el perfil
session_start();
require_once 'conexion.php';

// Verificar que haya sesión activa
if (!isset($_SESSION['usuario_id']) && !isset($_SESSION['publicador_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'No tienes sesión activa']);
    exit();
}

// Determinar tipo de usuario y su ID
if (isset($_SESSION['usuario_id'])) {
    $userType = 'usuario';
    $userId = $_SESSION['usuario_id'];
    $tabla = 'usuarios';
} elseif (isset($_SESSION['publicador_id'])) {
    $userType = 'publicador';
    $userId = $_SESSION['publicador_id'];
    $tabla = 'publicadores';
} elseif (isset($_SESSION['admin_id'])) {
    $userType = 'admin';
    $userId = $_SESSION['admin_id'];
    $tabla = 'admins';
}

// Obtener acción
$accion = $_POST['accion'] ?? '';

if ($accion == 'verificar') {
    // Verificar estado actual de 2FA
    $stmt = $conexion->prepare("SELECT two_factor_enabled FROM $tabla WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $enabled = ($row['two_factor_enabled'] == 1);
        echo json_encode(['success' => true, 'enabled' => $enabled]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    
} elseif ($accion == 'activar') {
    // Actualizar BD para activar 2FA
    $stmt = $conexion->prepare("UPDATE $tabla SET two_factor_enabled = 1 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '✅ Verificación en 2 pasos ACTIVADA']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al activar']);
    }
    
} elseif ($accion == 'desactivar') {
    // Actualizar BD para desactivar 2FA
    $stmt = $conexion->prepare("UPDATE $tabla SET two_factor_enabled = 0 WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '⚠️ Verificación en 2 pasos DESACTIVADA']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al desactivar']);
    }
    
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
?>
