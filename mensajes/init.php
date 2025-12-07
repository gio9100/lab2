<?php
// Inicialización del sistema de mensajería
// Detecta si el usuario es admin o publicador y configura variables globales

// session_status() = verifica estado de la sesión
// PHP_SESSION_NONE = constante que indica que no hay sesión activa
if (session_status() === PHP_SESSION_NONE) {
    // session_start() = inicia o continúa la sesión
    session_start();
}

// __DIR__ = constante que contiene la ruta del directorio actual
// require_once = incluye archivo solo una vez
require_once __DIR__ . '/db.php';

// Variables globales del usuario actual
$current_user_id = null;      // ID del usuario (admin o publicador)
$current_user_role = null;    // Rol: 'admin' o 'publicador'
$current_user_nivel = null;   // Nivel del admin: 'superadmin' o 'admin'

// Detectar rol desde URL (?as=admin o ?as=publicador)
// $_GET = array con parámetros de la URL
// ?? null = operador null coalescing, si no existe usa null
$force_role = $_GET['as'] ?? null;

// Verificar si se especificó rol en la URL
// === = comparación estricta (valor y tipo)
// && = operador AND lógico
if ($force_role === 'publicador' && isset($_SESSION['publicador_id'])) {
    // Asignar ID y rol de publicador
    $current_user_id = $_SESSION['publicador_id'];
    $current_user_role = 'publicador';
    
} elseif ($force_role === 'admin' && isset($_SESSION['admin_id'])) {
    // Asignar ID y rol de admin
    $current_user_id = $_SESSION['admin_id'];
    $current_user_role = 'admin';
    
    // Obtener nivel del admin desde la base de datos
    // prepare() = prepara consulta SQL (previene inyección SQL)
    $stmt = $conn->prepare("SELECT nivel FROM admins WHERE id = ?");
    // bind_param() = vincula variables a los ? de la consulta
    // "i" = tipo integer (entero)
    $stmt->bind_param("i", $current_user_id);
    // execute() = ejecuta la consulta
    $stmt->execute();
    // get_result() = obtiene resultados de la consulta
    $res = $stmt->get_result();
    // fetch_assoc() = convierte resultado en array asociativo
    if ($row = $res->fetch_assoc()) {
        // Guardar nivel del admin
        $current_user_nivel = $row['nivel'];
    }
    // close() = cierra la consulta preparada
    $stmt->close();
    
} else {
    // Sin parámetro ?as, priorizar admin sobre publicador
    // Verificar si hay sesión de admin
    if (isset($_SESSION['admin_id'])) {
        $current_user_id = $_SESSION['admin_id'];
        $current_user_role = 'admin';
        
        // Obtener nivel del admin
        $stmt = $conn->prepare("SELECT nivel FROM admins WHERE id = ?");
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $current_user_nivel = $row['nivel'];
        }
        $stmt->close();
        
    } elseif (isset($_SESSION['publicador_id'])) {
        // Si no es admin, verificar si es publicador
        $current_user_id = $_SESSION['publicador_id'];
        $current_user_role = 'publicador';
    }
}

// ACTUALIZAR ÚLTIMO ACCESO
// Si hay un usuario identificado, actualizamos su fecha de actividad
if ($current_user_id && $current_user_role) {
    $table = ($current_user_role === 'admin') ? 'admins' : 'publicadores';
    // NOW() = hora actual del servidor DB
    $updateQuery = "UPDATE $table SET ultimo_acceso = NOW() WHERE id = ?";
    $stmtUpdate = $conn->prepare($updateQuery);
    if ($stmtUpdate) {
        $stmtUpdate->bind_param("i", $current_user_id);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }
}

// Función de seguridad para bloquear accesos no autorizados
// function = declara una función
function checkAuth() {
    // global = permite acceder a variables globales dentro de la función
    global $current_user_id;
    
    // ! = operador NOT lógico
    if (!$current_user_id) {
        // header() = envía encabezado HTTP
        // HTTP/1.1 403 Forbidden = código de error "prohibido"
        header('HTTP/1.1 403 Forbidden');
        // json_encode() = convierte array PHP a JSON
        // => = operador de asignación en arrays asociativos
        echo json_encode(['error' => 'No autorizado']);
        // exit = detiene la ejecución del script
        exit;
    }
}
?>
