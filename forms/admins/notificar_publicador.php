<?php
// ============================================================================
// 📄 ARCHIVO: notificar_publicador.php
// ============================================================================
// PROPÓSITO: Enviar notificaciones por email a los publicadores cuando
//            un administrador cambia el estado de su publicación
//
// ESTADOS QUE ACTIVAN NOTIFICACIÓN:
// - 'publicado'  → La publicación fue aprobada y está visible
// - 'rechazada'  → La publicación fue rechazada (incluye motivo)
// - 'revision'   → La publicación necesita correcciones
//
// USO:
// require_once 'notificar_publicador.php';
// enviarNotificacionPublicador($email, $nombre, $titulo, $tipo, $estado, $id, $conn);
// ============================================================================

// ====================================================================
// INCLUIR PHPMAILER PARA ENVÍO DE CORREOS
// ====================================================================
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
// Incluimos la clase principal de PHPMailer
require_once __DIR__ . '/../PHPMailer/SMTP.php';
// Incluimos la clase SMTP para envío de correos
require_once __DIR__ . '/../PHPMailer/Exception.php';
// Incluimos la clase de excepciones para manejo de errores

// Importamos las clases de PHPMailer al namespace actual
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * ============================================================================
 * FUNCIÓN: enviarNotificacionPublicador
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Envía un correo electrónico al publicador notificándole que el estado
 * de su publicación ha cambiado (aprobada, rechazada o en revisión)
 * 
 * ¿CUÁNDO SE USA?
 * Se llama desde gestionar-publicaciones.php cuando un admin cambia el
 * estado de una publicación
 * 
 * PARÁMETROS:
 * @param string $email_publicador - Email del publicador que recibirá la notificación
 * @param string $nombre_publicador - Nombre del publicador para personalizar el mensaje
 * @param string $titulo_publicacion - Título de la publicación afectada
 * @param string $tipo_publicacion - Tipo de contenido (artículo, caso clínico, etc.)
 * @param string $nuevo_estado - Estado al que cambió (publicado, rechazada, revision)
 * @param int $publicacion_id - ID de la publicación para obtener más datos si es necesario
 * @param mysqli $conn - Conexión a la base de datos
 * 
 * RETORNA:
 * bool - true si el correo se envió exitosamente, false en caso contrario
 * 
 * EJEMPLO DE USO:
 * enviarNotificacionPublicador(
 *     'doctor@ejemplo.com',
 *     'Dr. Juan Pérez',
 *     'Nuevos avances en hematología',
 *     'articulo',
 *     'publicado',
 *     123,
 *     $conn
 * );
 */
function enviarNotificacionPublicador($email_publicador, $nombre_publicador, $titulo_publicacion, $tipo_publicacion, $nuevo_estado, $publicacion_id, $conn) {
    
    // ====================================================================
    // PASO 1: OBTENER INFORMACIÓN ADICIONAL SI ES RECHAZO
    // ====================================================================
    $mensaje_rechazo = '';
    // Variable para guardar el motivo del rechazo (si aplica)
    
    if ($nuevo_estado === 'rechazada') {
        // Si la publicación fue rechazada, obtenemos el motivo
        $query = "SELECT mensaje_rechazo FROM publicaciones WHERE id = ?";
        // Consulta para obtener el mensaje de rechazo
        $stmt = $conn->prepare($query);
        // Preparamos la consulta
        $stmt->bind_param("i", $publicacion_id);
        // Vinculamos el ID de la publicación
        $stmt->execute();
        // Ejecutamos la consulta
        $result = $stmt->get_result();
        // Obtenemos el resultado
        
        if ($result && $result->num_rows > 0) {
            // Si encontramos el registro
            $row = $result->fetch_assoc();
            // Obtenemos los datos
            $mensaje_rechazo = $row['mensaje_rechazo'] ?? 'No se especificó un motivo.';
            // Guardamos el mensaje o ponemos uno por defecto
        }
        $stmt->close();
        // Cerramos el statement
    }
    
    // ====================================================================
    // PASO 2: CONFIGURAR TÍTULOS Y MENSAJES SEGÚN EL ESTADO
    // ====================================================================
    // Definimos el emoji, título y mensaje según el estado
    switch ($nuevo_estado) {
        case 'publicado':
            // Si fue aprobada
            $emoji = '✅';
            $titulo_email = 'Publicación Aprobada';
            $mensaje_principal = "¡Excelentes noticias! Tu publicación ha sido <strong>aprobada</strong> y ahora está visible para todos los usuarios de Lab Explorer.";
            $texto_adicional = "Tu contenido ya está disponible en la plataforma y los usuarios pueden acceder a él.";
            break;
            
        case 'rechazada':
            // Si fue rechazada
            $emoji = '❌';
            $titulo_email = 'Publicación Rechazada';
            $mensaje_principal = "Lamentamos informarte que tu publicación ha sido <strong>rechazada</strong> por el equipo de administración.";
            $texto_adicional = "Por favor revisa el motivo del rechazo y realiza las correcciones necesarias antes de volver a enviarla.";
            break;
            
        case 'revision':
            // Si necesita correcciones
            $emoji = '🔄';
            $titulo_email = 'Publicación en Revisión';
            $mensaje_principal = "Tu publicación requiere algunas <strong>correcciones</strong> antes de ser aprobada.";
            $texto_adicional = "Por favor revisa los comentarios del administrador y realiza los ajustes necesarios.";
            break;
            
        default:
            // Estado no reconocido, no enviamos email
            return false;
    }
    
    // Incluimos el Helper de Emails
    require_once __DIR__ . '/../EmailHelper.php';
    
    $mensaje_html = $mensaje_principal . "<br><br>" . $texto_adicional;
    
    if ($nuevo_estado === 'rechazada' && !empty($mensaje_rechazo)) {
         $mensaje_html .= "<br><br><strong>Motivo del rechazo:</strong> " . htmlspecialchars($mensaje_rechazo);
    }
    
    $asunto = "$emoji $titulo_email: $titulo_publicacion";
    
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html,
        'Ver Mis Publicaciones',
        'http://localhost/lab/forms/publicadores/mis-publicaciones.php'
    );
}
?>
