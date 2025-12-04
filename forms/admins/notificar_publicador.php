<?php
// Notificar Publicador (Admin)
// Funciones para enviar notificaciones de cambio de estado a publicadores

// Incluir clases de PHPMailer
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

// Usar namespace de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Función: Enviar notificación de estado
// Notifica al publicador cuando el estado de su publicación cambia
function enviarNotificacionPublicador($email_publicador, $nombre_publicador, $titulo_publicacion, $tipo_publicacion, $nuevo_estado, $publicacion_id, $conn) {
    
    // Variable para motivo de rechazo
    $mensaje_rechazo = '';
    
    // Si es rechazada, obtener el motivo
    if ($nuevo_estado === 'rechazada') {
        $query = "SELECT mensaje_rechazo FROM publicaciones WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $publicacion_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $mensaje_rechazo = $row['mensaje_rechazo'] ?? 'No se especificó un motivo.';
        }
        $stmt->close();
    }
    
    // Configurar mensaje según estado
    switch ($nuevo_estado) {
        case 'publicado':
            $emoji = '✅';
            $titulo_email = 'Publicación Aprobada';
            $mensaje_principal = "¡Excelentes noticias! Tu publicación ha sido <strong>aprobada</strong> y ahora está visible para todos los usuarios de Lab Explorer.";
            $texto_adicional = "Tu contenido ya está disponible en la plataforma y los usuarios pueden acceder a él.";
            break;
            
        case 'rechazada':
            $emoji = '❌';
            $titulo_email = 'Publicación Rechazada';
            $mensaje_principal = "Lamentamos informarte que tu publicación ha sido <strong>rechazada</strong> por el equipo de administración.";
            $texto_adicional = "Por favor revisa el motivo del rechazo y realiza las correcciones necesarias antes de volver a enviarla.";
            break;
            
        case 'revision':
            $emoji = '🔄';
            $titulo_email = 'Publicación en Revisión';
            $mensaje_principal = "Tu publicación requiere algunas <strong>correcciones</strong> antes de ser aprobada.";
            $texto_adicional = "Por favor revisa los comentarios del administrador y realiza los ajustes necesarios.";
            break;
            
        default:
            return false;
    }
    
    // Incluir Helper de Emails
    require_once __DIR__ . '/../EmailHelper.php';
    
    // Construir cuerpo del correo
    $mensaje_html = $mensaje_principal . "<br><br>" . $texto_adicional;
    
    if ($nuevo_estado === 'rechazada' && !empty($mensaje_rechazo)) {
         $mensaje_html .= "<br><br><strong>Motivo del rechazo:</strong> " . htmlspecialchars($mensaje_rechazo);
    }
    
    $asunto = "$emoji $titulo_email: $titulo_publicacion";
    
    // Enviar correo
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html,
        'Ver Mis Publicaciones',
        'http://localhost/lab/forms/publicadores/mis-publicaciones.php'
    );
}
?>
