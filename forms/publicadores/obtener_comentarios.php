<?php
session_start();
require_once '../conexion.php';

header('Content-Type: application/json');

// Verificar si es publicador o admin (seguridad básica)
if (!isset($_SESSION['publicador_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'Falta ID']);
    exit();
}

$publicacion_id = intval($_GET['id']);

$query = "SELECT c.id, c.contenido, c.fecha_creacion, u.nombre as usuario_nombre 
          FROM comentarios c
          LEFT JOIN usuarios u ON c.usuario_id = u.id
          WHERE c.publicacion_id = ? AND c.estado = 'activo'
          ORDER BY c.fecha_creacion DESC";

$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $publicacion_id);
$stmt->execute();
$result = $stmt->get_result();

$comentarios = [];
while ($row = $result->fetch_assoc()) {
    // Formatear fecha opcionalmente
    $row['fecha_creacion'] = date('d/m/Y H:i', strtotime($row['fecha_creacion']));
    $comentarios[] = $row;
}

echo json_encode($comentarios);
?>
