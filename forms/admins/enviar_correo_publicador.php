<?php
// ============================================================================
// 📧 ARCHIVO: enviar_correo_publicador.php
// ============================================================================
// PROPÓSITO: Gestionar el envío de correos electrónicos a publicadores y admins
//
// FUNCIONES PRINCIPALES:
// 1. enviarCorreoAprobacion() - Notifica al publicador cuando es aprobado
// 2. enviarCorreoRechazo() - Notifica al publicador cuando es rechazado
// 3. enviarCorreoNuevoPublicadorAAdmins() - Notifica a admins de nuevo registro
//
// TECNOLOGÍA USADA:
// - PHPMailer: Librería para envío de correos con SMTP
// - Gmail SMTP: Servidor de correo de Google
// - HTML: Para correos con formato profesional
// ============================================================================

// Incluimos las clases de PHPMailer que necesitamos
require_once __DIR__ . '/../PHPMailer/PHPMailer.php';  // Clase principal
require_once __DIR__ . '/../PHPMailer/SMTP.php';        // Para conexión SMTP
require_once __DIR__ . '/../PHPMailer/Exception.php';   // Para manejo de errores

// Importamos las clases al namespace actual para usarlas fácilmente
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluimos el Helper de Emails
require_once __DIR__ . '/../EmailHelper.php';

/**
 * ============================================================================
 * FUNCIÓN 1: enviarCorreoAprobacion
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Envía un correo de felicitación al publicador cuando su cuenta es aprobada
 * 
 * ¿CUÁNDO SE USA?
 * Se llama desde gestionar_publicadores.php cuando un admin aprueba a un
 * publicador pendiente
 * 
 * PARÁMETROS:
 * @param string $email_publicador - Email del publicador aprobado
 * @param string $nombre_publicador - Nombre completo del publicador
 * 
 * RETORNA:
 * @return bool - true si el correo se envió correctamente, false si hubo error
 * 
 * EJEMPLO DE USO:
 * enviarCorreoAprobacion('juan@gmail.com', 'Juan Pérez');
 */
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

/**
 * ============================================================================
 * FUNCIÓN 2: enviarCorreoRechazo
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Envía un correo al publicador informándole que su solicitud fue rechazada
 * 
 * ¿CUÁNDO SE USA?
 * Se llama desde gestionar_publicadores.php cuando un admin rechaza a un
 * publicador pendiente
 * 
 * PARÁMETROS:
 * @param string $email_publicador - Email del publicador rechazado
 * @param string $nombre_publicador - Nombre completo del publicador
 * @param string $motivo - Razón del rechazo (opcional)
 * 
 * RETORNA:
 * @return bool - true si el correo se envió correctamente, false si hubo error
 * 
 * EJEMPLO DE USO:
 * enviarCorreoRechazo('juan@gmail.com', 'Juan Pérez', 'Información incompleta');
 */
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

/**
 * ============================================================================
 * FUNCIÓN 3: enviarCorreoNuevoPublicadorAAdmins
 * ============================================================================
 * 
 * ¿QUÉ HACE?
 * Notifica a TODOS los administradores activos cuando un nuevo publicador
 * se registra en el sistema
 * 
 * ¿CUÁNDO SE USA?
 * Se llama desde registro-publicadores.php cuando un nuevo publicador
 * completa su registro exitosamente
 * 
 * PARÁMETROS:
 * @param string $nombre_publicador - Nombre del nuevo publicador
 * @param string $email_publicador - Email del nuevo publicador
 * @param string $especialidad - Especialidad del publicador
 * @param mysqli $conn - Conexión a la base de datos
 * 
 * RETORNA:
 * @return bool - true si se envió a al menos un admin, false si no hay admins
 * 
 * EJEMPLO DE USO:
 * enviarCorreoNuevoPublicadorAAdmins('Juan Pérez', 'juan@gmail.com', 'Hematología', $conn);
 */
function enviarCorreoNuevoPublicadorAAdmins($nombre_publicador, $email_publicador, $especialidad, $conn) {
    // ====================================================================
    // PASO 1: OBTENER TODOS LOS ADMINISTRADORES ACTIVOS
    // ====================================================================
    $query = "SELECT email, nombre FROM admins WHERE estado = 'activo'";
    $result = $conn->query($query);
    
    // Si no hay admins activos o hubo error, salimos
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    
    // ====================================================================
    // PASO 2: PREPARAR EL CONTENIDO DEL CORREO
    // ====================================================================
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
    
    // ====================================================================
    // PASO 3: ENVIAR CORREO A CADA ADMINISTRADOR
    // ====================================================================
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
    
    // Retornamos true si se envió al menos a un admin
    return $enviados > 0;
}
?>
