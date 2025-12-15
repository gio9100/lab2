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

// Requiere que el usuario esté autenticado como admin
function requerirAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: login-admin.php");
        exit;
    }
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
    // Primero obtenemos los datos del publicador para enviar el correo
    $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
    $stmt_datos = $conn->prepare($query_datos);
    $stmt_datos->bind_param("i", $id);
    $stmt_datos->execute();
    $result_datos = $stmt_datos->get_result();
    $publicador_datos = $result_datos->fetch_assoc();
    
    // Actualizamos el estado a activo
    $query = "UPDATE publicadores SET estado = 'activo' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $exito = $stmt->execute();
    
    // Si se aprobó exitosamente, enviamos correo de bienvenida
    if ($exito && $publicador_datos) {
        require_once __DIR__ . '/../EmailHelper.php';
        
        $asunto = "🎉 ¡Bienvenido a Lab-Explora como Publicador!";
        
        $mensaje_html = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <div style='background: linear-gradient(135deg, #7390A0 0%, #5a7080 100%); padding: 30px; text-align: center; color: white; border-radius: 10px 10px 0 0;'>
                    <h1 style='margin: 0; font-size: 28px;'>🎉 ¡Felicidades!</h1>
                </div>
                <div style='padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px;'>
                    <p style='font-size: 16px; color: #333;'>Hola <strong>" . htmlspecialchars($publicador_datos['nombre']) . "</strong>,</p>
                    
                    <p style='font-size: 16px; color: #333;'>¡Excelentes noticias! Tu solicitud para ser <strong>publicador en Lab-Explora</strong> ha sido <strong style='color: #28a745;'>APROBADA</strong>. 🎊</p>
                    
                    <div style='background: #e8f5e9; padding: 20px; border-left: 4px solid #28a745; border-radius: 4px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #2e7d32;'>✅ ¿Qué puedes hacer ahora?</h3>
                        <ul style='color: #333; line-height: 1.8;'>
                            <li>Crear y publicar artículos científicos</li>
                            <li>Compartir tu conocimiento con la comunidad</li>
                            <li>Acceder a herramientas de edición profesionales</li>
                            <li>Obtener estadísticas de tus publicaciones</li>
                            <li>Descargar tu credencial digital oficial</li>
                        </ul>
                    </div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px; margin: 20px 0;'>
                        <h3 style='margin-top: 0; color: #856404;'>📋 Próximos pasos:</h3>
                        <ol style='color: #333; line-height: 1.8;'>
                            <li>Inicia sesión en tu panel de publicador</li>
                            <li>Completa tu perfil profesional</li>
                            <li>Crea tu primera publicación</li>
                            <li>Comparte conocimiento con la comunidad</li>
                        </ol>
                    </div>
                    
                    <p style='font-size: 16px; color: #333;'>Estamos emocionados de tenerte como parte de nuestra comunidad científica. 🔬</p>
                    
                    <p style='font-size: 16px; color: #333;'><strong>¡Bienvenido a Lab-Explora!</strong></p>
                    
                    <div style='text-align: center; margin-top: 30px;'>
                        <a href='http://localhost/lab2/forms/publicadores/login.php' style='background: linear-gradient(135deg, #7390A0 0%, #5a7080 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 25px; font-weight: bold; display: inline-block;'>
                            🚀 Acceder a mi Panel
                        </a>
                    </div>
                    
                    <p style='font-size: 14px; color: #666; margin-top: 30px; text-align: center;'>
                        Si tienes alguna pregunta, no dudes en contactarnos.
                    </p>
                </div>
            </div>
        ";
        
        EmailHelper::enviarCorreo(
            $publicador_datos['email'],
            $asunto,
            $mensaje_html
        );
    }
    
    return $exito;
}

// Rechaza un publicador
function rechazarPublicador($id, $motivo, $conn) {
    // Primero obtenemos los datos del publicador para enviar el correo
    $query_datos = "SELECT nombre, email FROM publicadores WHERE id = ?";
    $stmt_datos = $conn->prepare($query_datos);
    $stmt_datos->bind_param("i", $id);
    $stmt_datos->execute();
    $result_datos = $stmt_datos->get_result();
    $publicador_datos = $result_datos->fetch_assoc();
    
    // Actualizamos el estado a rechazado y guardamos el motivo
    $query = "UPDATE publicadores SET estado = 'rechazado', motivo_rechazo = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        error_log("ERROR en rechazarPublicador: " . $conn->error);
        return false;
    }
    
    $stmt->bind_param("si", $motivo, $id);
    $exito = $stmt->execute();
    
    if (!$exito) {
        error_log("ERROR al ejecutar UPDATE en rechazarPublicador: " . $stmt->error);
        error_log("ID: $id, Motivo: $motivo");
        return false;
    }
    
    error_log("SUCCESS: Publicador $id rechazado. Filas afectadas: " . $stmt->affected_rows);
    
    // Si se rechazó exitosamente, enviamos correo
    if ($exito && $publicador_datos) {
        require_once __DIR__ . '/../EmailHelper.php';
        
        $asunto = "❌ Tu solicitud de publicador ha sido rechazada";
        
        $mensaje_html = "
            <p>Lamentamos informarte que tu solicitud para ser publicador en <strong>Lab-Explora</strong> ha sido <strong>rechazada</strong>.</p>
            <h3>📋 Motivo del rechazo:</h3>
            <p style='background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px;'>
                " . htmlspecialchars($motivo) . "
            </p>
            <h3>ℹ️ ¿Qué significa esto?</h3>
            <ul>
                <li>Tu cuenta de publicador no ha sido aprobada</li>
                <li>No podrás crear publicaciones científicas</li>
                <li>Puedes seguir usando la plataforma como lector</li>
            </ul>
            <p>Si consideras que esto es un error o deseas volver a aplicar corrigiendo los problemas mencionados, por favor contacta al equipo de administración.</p>
            <p><strong>Gracias por tu interés en Lab-Explora.</strong></p>
        ";
        
        EmailHelper::enviarCorreo(
            $publicador_datos['email'],
            $asunto,
            $mensaje_html
        );
    }
    
    return $exito;
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
            <p>Lamentamos informarte que tu cuenta de publicador en <strong>Lab Explora</strong> ha sido <strong>suspendida</strong>.</p>
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

// Obtiene todos los reportes con filtros opcionales
function obtenerTodosReportes($conn, $tipo = null, $estado = null) {
    try {
        $query = "SELECT r.*, 
                  u.nombre as reportante_nombre,
                  u.correo as reportante_email,
                  p.titulo as publicacion_titulo,
                  c.contenido as comentario_contenido,
                  CASE 
                    WHEN r.tipo = 'publicacion' THEN p.titulo
                    WHEN r.tipo = 'comentario' THEN CONCAT('Comentario: ', SUBSTRING(c.contenido, 1, 50), '...')
                    ELSE 'Desconocido'
                  END as contenido_reportado
                  FROM reportes r
                  LEFT JOIN usuarios u ON r.usuario_id = u.id
                  LEFT JOIN publicaciones p ON r.tipo = 'publicacion' AND r.referencia_id = p.id
                  LEFT JOIN comentarios c ON r.tipo = 'comentario' AND r.referencia_id = c.id
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
    } catch (Exception $e) {
        // En caso de error, devolver array vacío para que la página pueda cargar
        error_log("Error en obtenerTodosReportes: " . $e->getMessage());
        return [];
    }
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

// Obtiene todos los comentarios para administración
function obtenerTodosComentarios($conn) {
    $query = "SELECT c.id, c.contenido, c.fecha_creacion, c.usuario_id, c.publicacion_id, 
              u.nombre as usuario_nombre, 
              p.titulo as publicacion_titulo 
              FROM comentarios c 
              JOIN usuarios u ON c.usuario_id = u.id 
              JOIN publicaciones p ON c.publicacion_id = p.id 
              ORDER BY c.fecha_creacion DESC";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Elimina un comentario desde admin
function eliminarComentarioAdmin($id, $conn) {
    $query = "DELETE FROM comentarios WHERE id = ?";
    $stmt = $conn->prepare($query);
    return $stmt->execute();
}
?>
