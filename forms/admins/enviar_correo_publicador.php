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
    $asunto = "✅ ¡Bienvenido a Lab Explora! Tu cuenta ha sido aprobada";
    
    $mensaje_html = "
        <p>¡Tenemos excelentes noticias! Tu solicitud para ser publicador en <strong>Lab Explora</strong> ha sido revisada y <strong>aprobada exitosamente</strong>.</p>
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
        'http://localhost/lab2/forms/publicadores/inicio-sesion-publicadores.php'
    );
}

// Función: Enviar correo de rechazo
// Notifica al publicador que su solicitud ha sido rechazada
function enviarCorreoRechazo($email_publicador, $nombre_publicador, $motivo = '') {
    $asunto = "❌ Actualización sobre tu solicitud en Lab Explora";
    
    $mensaje_html = "
        <p>Gracias por tu interés en formar parte de <strong>Lab Explora</strong> como publicador.</p>
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

// Función: Enviar correo de suspensión
// Notifica al publicador que su cuenta ha sido suspendida
function enviarCorreoSuspension($email_publicador, $nombre_publicador, $motivo) {
    $asunto = "⚠️ Importante: Tu cuenta ha sido suspendida - Lab Explora";
    
    $mensaje_html = "
        <p>Hola <strong>" . htmlspecialchars($nombre_publicador) . "</strong>,</p>
        <p>Te informamos que tu cuenta de publicador en <strong>Lab Explora</strong> ha sido <strong style='color: #dc3545;'>SUSPENDIDA</strong> temporalmente.</p>
        
        <h3>📋 Motivo de la suspensión:</h3>
        <p style='background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; border-radius: 4px; color: #856404;'>
            " . htmlspecialchars($motivo) . "
        </p>
        
        <h3>¿Qué implica esto?</h3>
        <ul>
            <li>No podrás iniciar sesión en tu panel de publicador.</li>
            <li>Tus publicaciones actuales dejarán de ser visibles públicamente.</li>
            <li>No podrás crear ni editar contenido.</li>
        </ul>
        
        <p>Si consideras que esto es un error o deseas apelar esta decisión, por favor contacta directamente con la administración respondiendo a este correo.</p>
    ";
    
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html
    );
}

// Función: Enviar correo de reactivación
function enviarCorreoReactivacion($email_publicador, $nombre_publicador) {
    $asunto = "🎉 ¡Bienvenido de nuevo! Tu cuenta ha sido reactivada - Lab Explora";
    
    $mensaje_html = "
        <p>Hola <strong>" . htmlspecialchars($nombre_publicador) . "</strong>,</p>
        <p>Nos alegra informarte que tu cuenta de publicador en <strong>Lab Explora</strong> ha sido <strong style='color: #28a745;'>REACTIVADA</strong>.</p>
        <p>Ya puedes volver a iniciar sesión y gestionar tus publicaciones con normalidad.</p>
        <p>¡Gracias por seguir con nosotros!</p>
    ";
    
    return EmailHelper::enviarCorreo(
        $email_publicador,
        $asunto,
        $mensaje_html,
        '🚀 Iniciar Sesión',
        'http://localhost/lab2/forms/publicadores/inicio-sesion-publicadores.php'
    );
}

// Función: Enviar correo de eliminación
function enviarCorreoEliminacion($email_publicador, $nombre_publicador) {
    $asunto = "⚠️ Tu cuenta ha sido eliminada - Lab Explora";
    
    $mensaje_html = "
        <p>Hola <strong>" . htmlspecialchars($nombre_publicador) . "</strong>,</p>
        <p>Te informamos que tu cuenta de publicador en <strong>Lab Explora</strong> ha sido <strong style='color: #dc3545;'>ELIMINADA</strong> permanentemente.</p>
        <p>Esta acción es irreversible y toda tu información y publicaciones han sido borradas de nuestros registros.</p>
        <p>Si consideras que esto ha sido un error, por favor ponte en contacto con la administración de Lab Explora inmediatamente.</p>
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
            'http://localhost/lab2/forms/admins/index-admin.php'
        );
        
        if ($exito) {
            $enviados++;
        }
    }
    
    return $enviados > 0;
}
?>
