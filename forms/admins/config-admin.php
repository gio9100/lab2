<?php
// Configuración principal del sistema de administración
// Contiene la conexión a la base de datos y funciones reutilizables
// para login, registro, estadísticas y gestión de publicadores

// Configuración de la base de datos
$servername = "localhost";  // Servidor MySQL (localhost en desarrollo)
$username = "root";         // Usuario de MySQL
$password = "";             // Contraseña (vacía por defecto en XAMPP)
$dbname = "lab_exp_db";     // Nombre de la base de datos

// Creamos la conexión a MySQL con los datos de acceso
$conn = new mysqli($servername, $username, $password, $dbname);

// Si hay error de conexión, detenemos todo el script
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configuramos UTF-8 para que funcionen tildes, ñ y emojis correctamente
$conn->set_charset("utf8mb4");

// Zona horaria de México para que las fechas se guarden correctamente
date_default_timezone_set('America/Mexico_City');

// Clave maestra para autorizar la creación de administradores
define('CLAVE_MAESTRA_ADMIN', 'labexplorer2025');

// Funciones reutilizables

// Verifica las credenciales de un administrador
// Retorna los datos del admin si es correcto, o false si falla
function loginAdmin($email, $password, $conn) {
    // Usamos ? como marcadores para prevenir inyección SQL
    $query = "SELECT * FROM admins WHERE email = ? AND estado = 'activo'";
    
    // Preparamos y ejecutamos la consulta de forma segura
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email); // "s" = string
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si encontramos exactamente un usuario
    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc(); // Obtenemos sus datos
        
        // Verificamos que la contraseña coincida con el hash guardado
        if (password_verify($password, $admin['password'])) {
            // Actualizamos la fecha de último acceso
            $update_query = "UPDATE admins SET ultimo_acceso = NOW() WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("i", $admin['id']); // "i" = integer
            $update_stmt->execute();
            
            return $admin;
        }
    }
    
    return false;
}

// Crea un nuevo administrador en la base de datos
function registrarAdmin($datos, $conn) {
    $query = "INSERT INTO admins (nombre, email, password, nivel) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // Hasheamos la contraseña (NUNCA guardarla en texto plano)
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    
    // "ssss" = 4 strings: nombre, email, password_hash, nivel
    $stmt->bind_param("ssss", 
        $datos['nombre'],
        $datos['email'],
        $password_hash,
        $datos['nivel']
    );
    
    return $stmt->execute();
}

// Verifica si un email ya está registrado como administrador
function adminExiste($email, $conn) {
    $query = "SELECT id FROM admins WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Si num_rows > 0, significa que ya existe
    return $result->num_rows > 0;
}

// Obtiene estadísticas generales para el dashboard
function obtenerEstadisticasAdmin($conn) {
    // Inicializamos con valores en 0 por si algo falla
    $stats = [
        'total_usuarios' => 0,
        'total_publicadores' => 0,
        'publicadores_pendientes' => 0,
        'total_publicaciones' => 0,
        'total_admins' => 0
    ];
    
    // COUNT(*) cuenta el total de filas
    
    // Total Usuarios
    $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
    if ($result) {
        $stats['total_usuarios'] = $result->fetch_assoc()['total'];
    }
    
    // Total Publicadores
    $result = $conn->query("SELECT COUNT(*) as total FROM publicadores");
    if ($result) {
        $stats['total_publicadores'] = $result->fetch_assoc()['total'];
    }
    
    // Publicadores Pendientes
    $result = $conn->query("SELECT COUNT(*) as total FROM publicadores WHERE estado = 'pendiente'");
    if ($result) {
        $stats['publicadores_pendientes'] = $result->fetch_assoc()['total'];
    }
    
    // Total Publicaciones
    $result = $conn->query("SELECT COUNT(*) as total FROM publicaciones");
    if ($result) {
        $stats['total_publicaciones'] = $result->fetch_assoc()['total'];
    }
    
    // Total Admins Activos
    $result = $conn->query("SELECT COUNT(*) as total FROM admins WHERE estado = 'activo'");
    if ($result) {
        $stats['total_admins'] = $result->fetch_assoc()['total'];
    }
    
    return $stats;
}

// Obtiene estadísticas de reportes
function obtenerEstadisticasReportes($conn) {
    $stats = [
        'pendientes' => 0,
        'revisados' => 0,
        'total' => 0
    ];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'pendiente'");
    if ($result) {
        $stats['pendientes'] = $result->fetch_assoc()['total'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'revisado'");
    if ($result) {
        $stats['revisados'] = $result->fetch_assoc()['total'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes");
    if ($result) {
        $stats['total'] = $result->fetch_assoc()['total'];
    }
    
    return $stats;
}

// Obtiene publicadores pendientes de aprobación
function obtenerPublicadoresPendientes($conn) {
    $query = "SELECT * FROM publicadores WHERE estado = 'pendiente' ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtiene todos los publicadores
function obtenerTodosPublicadores($conn) {
    $query = "SELECT * FROM publicadores ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtiene usuarios normales
function obtenerUsuariosNormales($conn) {
    $query = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Aprueba un publicador
function aprobarPublicador($id, $conn) {
    $query = "UPDATE publicadores SET estado = 'activo', fecha_aprobacion = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Rechaza un publicador
function rechazarPublicador($id, $motivo, $conn) {
    $query = "UPDATE publicadores SET estado = 'rechazado', motivo_rechazo = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $motivo, $id);
    return $stmt->execute();
}

// Suspende un publicador
function suspenderPublicador($id, $motivo, $conn) {
    $query = "UPDATE publicadores SET estado = 'suspendido', motivo_suspension = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $motivo, $id);
    return $stmt->execute();
}

// Activa un publicador suspendido
function activarPublicador($id, $conn) {
    $query = "UPDATE publicadores SET estado = 'activo', motivo_suspension = NULL WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Verifica si el usuario está logueado como admin
function requerirAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login-admin.php");
        exit;
    }
}
?>
