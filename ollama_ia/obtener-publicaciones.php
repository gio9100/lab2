<?php
// =============================================================================
// ARCHIVO: obtener-publicaciones.php
// PROPÓSITO: Endpoint para obtener publicaciones pendientes de moderación
// IMPORTANTE: Guardar como UTF-8 SIN BOM
// =============================================================================

// Iniciar sesión
session_start();

// Incluir configuración de admin
require_once '../forms/admins/config-admin.php';

// Configurar headers primero
header('Content-Type: application/json; charset=utf-8');

// Verificar que sea administrador
requerirAdmin();

// Verificar conexión a base de datos
if (!isset($conn) || !$conn->ping()) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos'
    ]);
    exit();
}

try {
    // Consulta SQL para obtener publicaciones pendientes
    $sql = "SELECT 
                p.id,
                p.titulo,
                p.contenido,
                p.resumen,
                p.estado,
                DATE_FORMAT(p.fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                COALESCE(pub.nombre, 'Desconocido') as autor
            FROM publicaciones p
            LEFT JOIN publicadores pub ON p.publicador_id = pub.id
            WHERE p.estado IN ('borrador', 'revision', 'en_revision', 'pendiente')
            AND p.estado NOT IN ('rechazada', 'publicado')
            ORDER BY p.fecha_creacion DESC
            LIMIT 50";
    
    $resultado = $conn->query($sql);
    
    if (!$resultado) {
        throw new Exception('Error en la consulta SQL: ' . $conn->error);
    }
    
    $publicaciones = [];
    while ($fila = $resultado->fetch_assoc()) {
        $publicaciones[] = $fila;
    }
    
    // Enviar respuesta JSON
    echo json_encode([
        'success' => true,
        'publicaciones' => $publicaciones,
        'total' => count($publicaciones)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Cerrar conexión si existe
if (isset($conn)) {
    $conn->close();
}
exit();
?>