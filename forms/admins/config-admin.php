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
        'resueltos' => 0,
        'descartados' => 0,
        'total' => 0
    ];
    
    // Reportes pendientes
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'pendiente'");
    if ($result) {
        $stats['pendientes'] = $result->fetch_assoc()['total'];
    }
    
    // Reportes resueltos (aprobados)
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'resuelto'");
    if ($result) {
        $stats['resueltos'] = $result->fetch_assoc()['total'];
    }
    
    // Reportes descartados (ignorados)
    $result = $conn->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'ignorado'");
    if ($result) {
        $stats['descartados'] = $result->fetch_assoc()['total'];
    }
    
    // Total de reportes
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
    $query = "UPDATE publicadores SET estado = 'activo' WHERE id = ?";
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
    // Primero obtenemos los datos del publicador para enviar el correo
    $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
    $stmt_datos = $conn->prepare($query_datos);
    $stmt_datos->bind_param("i", $id);
    $stmt_datos->execute();
    $result_datos = $stmt_datos->get_result();
    $publicador_datos = $result_datos->fetch_assoc();
    
    // Actualizamos el estado a suspendido
    $query = "UPDATE publicadores SET estado = 'suspendido', motivo_suspension = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $motivo, $id);
    $exito = $stmt->execute();
    
    // Si se suspendió exitosamente, enviamos correo
    if ($exito && $publicador_datos) {
        require_once __DIR__ . '/../EmailHelper.php';
        
        $asunto = "⚠️ Tu cuenta de publicador ha sido suspendida";
        
        $mensaje_html = "
            <p>Lamentamos informarte que tu cuenta de publicador en <strong>Lab Explorer</strong> ha sido <strong>suspendida</strong>.</p>
            <h3>📋 Motivo de la suspensión:</h3>
            <p style='background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px;'>
                " . htmlspecialchars($motivo) . "
            </p>
            <h3>ℹ️ ¿Qué significa esto?</h3>
            <ul>
                <li>No podrás acceder a tu panel de publicador</li>
                <li>Tus publicaciones existentes permanecen visibles</li>
                <li>No podrás crear nuevas publicaciones</li>
            </ul>
            <p>Si consideras que esto es un error o deseas apelar esta decisión, por favor contacta al equipo de administración.</p>
        ";
        
        EmailHelper::enviarCorreo(
            $publicador_datos['email'],
            $asunto,
            $mensaje_html
        );
    }
    
    return $exito;
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

// Obtiene todos los reportes con filtros opcionales
function obtenerTodosReportes($tipo = null, $estado = null, $conn) {
    $query = "SELECT r.*, 
              u.nombre as usuario_nombre,
              c.contenido as comentario_contenido,
              uc.nombre as comentario_autor_nombre
              FROM reportes r
              LEFT JOIN usuarios u ON r.usuario_id = u.id
              LEFT JOIN comentarios c ON r.tipo = 'comentario' AND r.referencia_id = c.id
              LEFT JOIN usuarios uc ON c.usuario_id = uc.id
              WHERE 1=1";
    
    $params = [];
    $types = "";
    
    if ($tipo) {
        $query .= " AND r.tipo = ?";
        $params[] = $tipo;
        $types .= "s";
    }
    
    if ($estado) {
        $query .= " AND r.estado = ?";
        $params[] = $estado;
        $types .= "s";
    }
    
    $query .= " ORDER BY r.fecha_creacion DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Procesa un reporte (aprobar o rechazar)
function procesarReporte($reporte_id, $accion, $admin_id, $conn) {
    // Obtener información del reporte
    $query = "SELECT r.tipo, r.referencia_id, r.motivo, r.usuario_id 
              FROM reportes r 
              WHERE r.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $reporte_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $reporte = $result->fetch_assoc();
    
    if ($accion === 'aprobar') {
        // Obtener datos del contenido reportado ANTES de eliminarlo
        $datos_contenido = null;
        if ($reporte['tipo'] === 'publicacion') {
            $query_pub = "SELECT p.titulo, p.publicador_id, pub.nombre as publicador_nombre, pub.email as publicador_email
                          FROM publicaciones p
                          INNER JOIN publicadores pub ON p.publicador_id = pub.id
                          WHERE p.id = ?";
            $stmt_pub = $conn->prepare($query_pub);
            $stmt_pub->bind_param("i", $reporte['referencia_id']);
            $stmt_pub->execute();
            $result_pub = $stmt_pub->get_result();
            $datos_contenido = $result_pub->fetch_assoc();
            
            // Eliminar la publicación
            $delete_query = "DELETE FROM publicaciones WHERE id = ?";
        } else {
            // Para comentarios, también obtenemos datos
            $query_com = "SELECT c.contenido, c.usuario_id, u.nombre as usuario_nombre, u.correo as usuario_email
                          FROM comentarios c
                          INNER JOIN usuarios u ON c.usuario_id = u.id
                          WHERE c.id = ?";
            $stmt_com = $conn->prepare($query_com);
            $stmt_com->bind_param("i", $reporte['referencia_id']);
            $stmt_com->execute();
            $result_com = $stmt_com->get_result();
            $datos_contenido = $result_com->fetch_assoc();
            
            // Eliminar el comentario
            $delete_query = "DELETE FROM comentarios WHERE id = ?";
        }
        
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bind_param("i", $reporte['referencia_id']);
        $delete_stmt->execute();
        
        // Enviar correo al publicador/usuario notificando la eliminación
        if ($datos_contenido) {
            require_once __DIR__ . '/../EmailHelper.php';
            
            if ($reporte['tipo'] === 'publicacion') {
                $asunto = "⚠️ Tu publicación ha sido eliminada por reportes";
                
                $mensaje_html = "
                    <p>Lamentamos informarte que tu publicación ha sido <strong>eliminada</strong> debido a reportes de usuarios.</p>
                    <h3>📋 Detalles:</h3>
                    <ul>
                        <li><strong>Publicación:</strong> " . htmlspecialchars($datos_contenido['titulo']) . "</li>
                        <li><strong>Motivo del reporte:</strong> " . htmlspecialchars($reporte['motivo'] ?? 'No especificado') . "</li>
                        <li><strong>Fecha:</strong> " . date('d/m/Y H:i') . "</li>
                    </ul>
                    <h3>ℹ️ ¿Qué significa esto?</h3>
                    <ul>
                        <li>La publicación ha sido eliminada permanentemente</li>
                        <li>Fue reportada por violar las normas de la comunidad</li>
                        <li>Puedes crear nuevas publicaciones respetando las normas</li>
                    </ul>
                    <p>Por favor, asegúrate de que tus futuras publicaciones cumplan con nuestras políticas de contenido.</p>
                ";
                
                EmailHelper::enviarCorreo(
                    $datos_contenido['publicador_email'],
                    $asunto,
                    $mensaje_html
                );
            }
        }
        
        // Actualizar estado del reporte
        $update_query = "UPDATE reportes SET estado = 'resuelto', admin_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $admin_id, $reporte_id);
        return $update_stmt->execute();
    } else {
        // Rechazar el reporte
        $update_query = "UPDATE reportes SET estado = 'ignorado', admin_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $admin_id, $reporte_id);
        return $update_stmt->execute();
    }
}

// Verifica si un usuario existe por email
function usuarioExiste($email, $conn, $excluir_id = null) {
    if ($excluir_id) {
        // Si se proporciona un ID, excluirlo de la búsqueda (para edición)
        $query = "SELECT id FROM usuarios WHERE correo = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $excluir_id);
    } else {
        // Búsqueda simple
        $query = "SELECT id FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Crea un nuevo usuario
function crearUsuario($datos, $conn) {
    $query = "INSERT INTO usuarios (nombre, correo, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    
    // Hashear la contraseña
    $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
    
    $stmt->bind_param("sss", 
        $datos['nombre'],
        $datos['correo'],
        $password_hash
    );
    
    return $stmt->execute();
}

// Edita un usuario existente
function editarUsuario($id, $datos, $conn) {
    // Si se proporciona nueva contraseña, actualizarla también
    if (isset($datos['password']) && !empty($datos['password'])) {
        $query = "UPDATE usuarios SET nombre = ?, correo = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt->bind_param("sssi", 
            $datos['nombre'],
            $datos['correo'],
            $password_hash,
            $id
        );
    } else {
        // Solo actualizar nombre y correo
        $query = "UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", 
            $datos['nombre'],
            $datos['correo'],
            $id
        );
    }
    
    return $stmt->execute();
}

// Elimina un usuario
function eliminarUsuario($id, $conn) {
    $query = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}
?>
