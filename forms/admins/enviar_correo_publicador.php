<?php
// Enviar Correo Publicador (Admin)
// Funciones para enviar notificaciones por correo a publicadores y administradores

// Incluir clases de PHPMailer
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

// Usar namespace de PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir Helper de Emails
require_once __DIR__ . '/../EmailHelper.php';

// Función: Enviar correo de aprobación
// Notifica al publicador que su cuenta ha sido aprobada
function enviarCorreoAprobacion($email_publicador, $nombre_publicador) {
    $asunto = "✅ ¡Bienvenido a Lab Explorer! Tu cuenta ha sido aprobada";
    
    $mensaje_html = "
        <p>¡Tenemos excelentes noticias! Tu solicitud para ser publicador en <strong>Lab Explorer</strong> ha sido revisada y <strong>aprobada exitosamente</strong>.</p>
        <p>Ahora formas parte de nuestra comunidad de profesionales de laboratorio clínico. Podrás compartir tus conocimientos, experiencias y contribuir al crecimiento de la comunidad científica.</p>
        <h3>📝 ¿Qué puedes hacer ahora?</h3>
        <ul>
            <li>Crear y publicar artículos científicos</li>
            <li>Compartir casos clínicos interesantes</li>
            <li>Publicar estudios y revisiones</li>
            <li>Interactuar con otros profesionales</li>
        </ul>
    ";
    
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html,
        '🚀 Iniciar Sesión Ahora',
        'http://localhost/lab/forms/publicadores/inicio-sesion-publicadores.php'
    );
}

// Función: Enviar correo de rechazo
// Notifica al publicador que su solicitud ha sido rechazada
function enviarCorreoRechazo($email_publicador, $nombre_publicador, $motivo = '') {
    $asunto = "❌ Actualización sobre tu solicitud en Lab Explorer";
    
    $mensaje_html = "
        <p>Gracias por tu interés en formar parte de <strong>Lab Explorer</strong> como publicador.</p>
        <p>Lamentablemente, después de revisar tu solicitud, <strong>no hemos podido aprobarla en este momento</strong>.</p>";
    
    if (!empty($motivo)) {
        $mensaje_html .= "
        <h3>📋 Motivo del rechazo:</h3>
        <p style='background-color: #f8f9fa; padding: 15px; border-left: 4px solid #dc3545; border-radius: 4px;'>
            " . htmlspecialchars($motivo) . "
        </p>";
    }
    
    $mensaje_html .= "
        <h3>🔄 ¿Qué puedes hacer?</h3>
        <ul>
            <li>Revisa los requisitos para ser publicador en nuestra plataforma</li>
            <li>Asegúrate de proporcionar información completa y verificable</li>
            <li>Puedes volver a solicitar el registro más adelante</li>
        </ul>
    ";
    
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html
    );
}

// Función: Notificar nuevo publicador a administradores
// Envía un correo a todos los admins activos cuando se registra un nuevo publicador
function enviarCorreoNuevoPublicadorAAdmins($nombre_publicador, $email_publicador, $especialidad, $conn) {
    // Obtener admins activos
    $query = "SELECT email, nombre FROM admins WHERE estado = 'activo'";
    $result = $conn->query($query);
    
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    
    // Preparar mensaje
    $asunto = "🔔 Nuevo Publicador Pendiente de Aprobación";
    
    $mensaje_html = "
        <p>Se ha registrado un nuevo publicador en la plataforma y está esperando tu aprobación.</p>
        <h3>📋 Datos del Publicador:</h3>
        <ul>
            <li><strong>Nombre:</strong> " . htmlspecialchars($nombre_publicador) . "</li>
            <li><strong>Email:</strong> " . htmlspecialchars($email_publicador) . "</li>
            <li><strong>Especialidad:</strong> " . htmlspecialchars($especialidad) . "</li>
            <li><strong>Fecha de registro:</strong> " . date('d/m/Y H:i') . "</li>
        </ul>
        <p>Por favor, revisa la información del publicador y procede con la aprobación o rechazo desde el panel de administración.</p>
    ";
    
    // Enviar a cada admin
    $enviados = 0;
    while ($admin = $result->fetch_assoc()) {
        $exito = EmailHelper::enviarCorreo(
            $admin['email'],
            $asunto,
            $mensaje_html,
            'Ver Panel de Administración',
            'http://localhost/lab/forms/admins/index-admin.php'
        );
        
        if ($exito) {
            $enviados++;
        }
    }
    
    return $enviados > 0;
}
?>
