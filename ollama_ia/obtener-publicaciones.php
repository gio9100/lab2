<?php
// Endpoint para obtener publicaciones pendientes de moderación
// Retorna lista de publicaciones en estado borrador/revision

session_start();
require_once '../forms/admins/config-admin.php';

header('Content-Type: application/json; charset=utf-8');

requerirAdmin();

if (!isset($conn) || !$conn->ping()) {
    echo json_encode([
        'success' => false,
        'error' => 'Error de conexión a la base de datos'
    ]);
    exit();
}

try {
    // DATE_FORMAT() = función MySQL para formatear fechas
    // COALESCE() = retorna primer valor no-null
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
    // while = recorre todos los resultados
    while ($fila = $resultado->fetch_assoc()) {
        $publicaciones[] = $fila;
    }
    
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

if (isset($conn)) {
    $conn->close();
}
exit();
?>