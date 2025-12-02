<?php
// Verificamos si la sesión de PHP no está iniciada aún
if (session_status() === PHP_SESSION_NONE) {
    // Si no está iniciada, la arrancamos para poder leer las variables $_SESSION
    session_start();
}

// Incluimos el archivo que tiene la conexión real a la base de datos
require_once __DIR__ . '/db.php';

// Inicializamos las variables del usuario actual en nulo por seguridad
$current_user_id = null;
$current_user_role = null;
$current_user_nivel = null;

// Buscamos si en la URL viene un parámetro "?as=..." (ej: ?as=admin o ?as=publicador)
// Esto sirve porque un mismo navegador puede tener abiertas sesiones de ambos tipos
$force_role = $_GET['as'] ?? null;

// CASO A: Nos piden explícitamente ser 'publicador' y tenemos una sesión de publicador activa
if ($force_role === 'publicador' && isset($_SESSION['publicador_id'])) {
    $current_user_id = $_SESSION['publicador_id'];
    $current_user_role = 'publicador';
    
// CASO B: Nos piden explícitamente ser 'admin' y tenemos una sesión de admin activa
} elseif ($force_role === 'admin' && isset($_SESSION['admin_id'])) {
    $current_user_id = $_SESSION['admin_id'];
    $current_user_role = 'admin';
    
    // Si es admin, buscamos su nivel (superadmin o normal) en la base de datos
    $stmt = $conn->prepare("SELECT nivel FROM admins WHERE id = ?");
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $current_user_nivel = $row['nivel'];
    }
    $stmt->close();
    
} else {
    // CASO C: No nos especificaron nada en la URL.
    // Tenemos que adivinar o priorizar.
    
    // Si hay sesión de admin, le damos prioridad (el admin manda)
    if (isset($_SESSION['admin_id'])) {
        $current_user_id = $_SESSION['admin_id'];
        $current_user_role = 'admin';
        
        // Buscamos el nivel del admin
        $stmt = $conn->prepare("SELECT nivel FROM admins WHERE id = ?");
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $current_user_nivel = $row['nivel'];
        }
        $stmt->close();
        
    // Si no es admin, pero sí es publicador
    } elseif (isset($_SESSION['publicador_id'])) {
        $current_user_id = $_SESSION['publicador_id'];
        $current_user_role = 'publicador';
    }
}

// Función de seguridad para bloquear accesos no autorizados
// Se usa en los archivos de la API para asegurarse de que nadie entre sin loguearse
function checkAuth() {
    global $current_user_id;
    // Si no pudimos determinar quién es el usuario (no hay ID)
    if (!$current_user_id) {
        // Enviamos un error 403 (Prohibido)
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'No autorizado']);
        // Detenemos la ejecución del script inmediatamente
        exit;
    }
}
?>
