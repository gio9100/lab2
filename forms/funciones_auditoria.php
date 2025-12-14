<?php
// forms/funciones_auditoria.php

/**
 * Registra una acción administrativa en la base de datos
 */
function registrarLogAuditoria($conn, $admin_id, $accion, $tipo_objeto, $objeto_id, $detalles = '') {
    if (!$conn || empty($admin_id) || empty($accion)) return false;

    // Obtener IP del cliente
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    $stmt = $conn->prepare("INSERT INTO logs_auditoria (admin_id, accion, tipo_objeto, objeto_id, detalles, ip_origen) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $admin_id, $accion, $tipo_objeto, $objeto_id, $detalles, $ip);
    
    return $stmt->execute();
}
?>
