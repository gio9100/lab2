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
    // Creamos una nueva instancia de PHPMailer
    // El parámetro 'true' activa las excepciones para mejor manejo de errores
    $mail = new PHPMailer(true);
    
    try {
        // ====================================================================
        // PASO 1: CONFIGURAR LA CONEXIÓN SMTP
        // ====================================================================
        $mail->isSMTP();                                    // Usamos SMTP (no mail() de PHP)
        $mail->Host = 'smtp.gmail.com';                     // Servidor SMTP de Gmail
        $mail->SMTPAuth = true;                             // Activamos autenticación
        $mail->Username = 'lab.explorer2025@gmail.com';     // Email de Lab Explorer
        $mail->Password = 'yero ewft jacf vjzp';            // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Encriptación TLS
        $mail->Port = 587;                                  // Puerto para TLS
        
        // ====================================================================
        // PASO 2: CONFIGURAR CODIFICACIÓN (para emojis, tildes, ñ)
        // ====================================================================
        $mail->CharSet = 'UTF-8';      // Conjunto de caracteres UTF-8
        $mail->Encoding = 'base64';    // Codificación base64 para compatibilidad
        
        // ====================================================================
        // PASO 3: CONFIGURAR REMITENTE Y DESTINATARIO
        // ====================================================================
        $mail->setFrom('lab.explorer2025@gmail.com', 'Lab Explorer');  // Quién envía
        $mail->addAddress($email_publicador, $nombre_publicador);      // Quién recibe
        
        // ====================================================================
        // PASO 4: CONFIGURAR EL ASUNTO Y FORMATO
        // ====================================================================
        $mail->Subject = "✅ ¡Bienvenido a Lab Explorer! Tu cuenta ha sido aprobada";
        $mail->isHTML(true);  // El correo será en formato HTML (no texto plano)
        
        // ====================================================================
        // PASO 5: CREAR EL CUERPO DEL CORREO EN HTML
        // ====================================================================
        // ====================================================================
        // PASO 5: CREAR EL CUERPO DEL CORREO EN HTML
        // ====================================================================
        
        $boton = [
            'texto' => '🚀 Iniciar Sesión Ahora',
            'url' => 'http://localhost/lab/forms/publicadores/inicio-sesion-publicadores.php'
        ];
        
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
     $mail->Body = EmailHelper::render(
    "¡Felicidades! Tu cuenta ha sido aprobada",
    $nombre_publicador,
    $mensaje_html,
    [],
    $boton,
    'aprobado'
);



        // Versión de texto plano (para clientes que no soportan HTML)
        $mail->AltBody = "Hola $nombre_publicador, tu cuenta de publicador en Lab Explorer ha sido aprobada. Ya puedes iniciar sesión y comenzar a publicar.";
        
        // ====================================================================
        // PASO 7: ENVIAR EL CORREO
        // ====================================================================
        $mail->send();
        return true;  // Éxito
        
    } catch (Exception $e) {
        // Si algo sale mal, guardamos el error en el log del servidor
        error_log("Error enviando correo de aprobación: " . $mail->ErrorInfo);
        return false;  // Fallo
    }
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
    // Creamos una nueva instancia de PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // CONFIGURACIÓN SMTP (igual que en enviarCorreoAprobacion)
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lab.explorer2025@gmail.com';
        $mail->Password = 'yero ewft jacf vjzp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // CONFIGURACIÓN DE CODIFICACIÓN
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        // REMITENTE Y DESTINATARIO
        $mail->setFrom('lab.explorer2025@gmail.com', 'Lab Explorer');
        $mail->addAddress($email_publicador, $nombre_publicador);
        
        // ASUNTO
        $mail->Subject = "❌ Actualización sobre tu solicitud en Lab Explorer";
        $mail->isHTML(true);
        
        // ====================================================================
        // PREPARAR SECCIÓN DEL MOTIVO (si existe)
        // ====================================================================
        // ====================================================================
        // CUERPO DEL CORREO
        // ====================================================================
        
        $detalles = [];
        if (!empty($motivo)) {
            $detalles['Motivo del rechazo'] = $motivo;
        }
        
        $mensaje_html = "
            <p>Gracias por tu interés en formar parte de <strong>Lab Explorer</strong> como publicador.</p>
            <p>Lamentablemente, después de revisar tu solicitud, <strong>no hemos podido aprobarla en este momento</strong>.</p>
            <h3>🔄 ¿Qué puedes hacer?</h3>
            <ul>
                <li>Revisa los requisitos para ser publicador en nuestra plataforma</li>
                <li>Asegúrate de proporcionar información completa y verificable</li>
                <li>Puedes volver a solicitar el registro más adelante</li>
            </ul>
        ";
        
        $mail->Body = EmailHelper::render(
            "Actualización de tu Solicitud",
            $nombre_publicador,
            $mensaje_html,
            $detalles,
            null, // Sin botón
            'rechazado'
        );
        
        $mail->AltBody = "Hola $nombre_publicador, lamentablemente tu solicitud para ser publicador en Lab Explorer no ha sido aprobada. " . ($motivo ? "Motivo: $motivo" : "");
        
        // ENVIAR
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error enviando correo de rechazo: " . $mail->ErrorInfo);
        return false;
    }
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
    
    // Creamos instancia de PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // CONFIGURACIÓN SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lab.explorer2025@gmail.com';
        $mail->Password = 'yero ewft jacf vjzp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // CONFIGURACIÓN DE CODIFICACIÓN
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        
        // REMITENTE
        $mail->setFrom('lab.explorer2025@gmail.com', 'Lab Explorer - Sistema');
        
        // ====================================================================
        // PASO 2: AGREGAR TODOS LOS ADMINS COMO DESTINATARIOS
        // ====================================================================
        while ($admin = $result->fetch_assoc()) {
            $mail->addAddress($admin['email'], $admin['nombre']);
        }
        
        // ASUNTO
        $mail->Subject = "🔔 Nuevo Publicador Pendiente de Aprobación";
    } catch (Exception $e) {
        error_log("Error enviando correo a admins: " . $mail->ErrorInfo);
        return false;
    }
}
?>
