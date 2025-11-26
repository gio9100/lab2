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
        // Usamos HTML inline CSS para que se vea bien en todos los clientes de correo
        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <!-- ENCABEZADO -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #7390A0; margin-bottom: 10px;'>🎉 ¡Felicidades!</h1>
                    <h2 style='color: #333; font-weight: normal;'>Tu cuenta ha sido aprobada</h2>
                </div>
                
                <!-- MENSAJE PRINCIPAL -->
                <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;'>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        Hola <strong>$nombre_publicador</strong>,
                    </p>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        ¡Tenemos excelentes noticias! Tu solicitud para ser publicador en <strong>Lab Explorer</strong> 
                        ha sido revisada y <strong style='color: #7390A0;'>aprobada exitosamente</strong>.
                    </p>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        Ahora formas parte de nuestra comunidad de profesionales de laboratorio clínico. 
                        Podrás compartir tus conocimientos, experiencias y contribuir al crecimiento de la comunidad científica.
                    </p>
                </div>
                
                <!-- LISTA DE BENEFICIOS -->
                <div style='background: #f0f4f6; padding: 20px; border-left: 4px solid #7390A0; margin-bottom: 20px;'>
                    <h3 style='color: #7390A0; margin-top: 0;'>📝 ¿Qué puedes hacer ahora?</h3>
                    <ul style='color: #333; line-height: 1.8;'>
                        <li>Crear y publicar artículos científicos</li>
                        <li>Compartir casos clínicos interesantes</li>
                        <li>Publicar estudios y revisiones</li>
                        <li>Interactuar con otros profesionales</li>
                        <li>Gestionar tus publicaciones desde tu panel</li>
                    </ul>
                </div>
                
                <!-- BOTÓN DE ACCIÓN -->
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/lab/forms/publicadores/inicio-sesion-publicadores.php' 
                       style='background: #7390A0; color: white; padding: 15px 40px; text-decoration: none; 
                              border-radius: 5px; display: inline-block; font-weight: bold; font-size: 16px;'>
                        🚀 Iniciar Sesión Ahora
                    </a>
                </div>
                
                <!-- CONSEJO -->
                <div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 0; color: #856404; font-size: 14px;'>
                        <strong>💡 Consejo:</strong> Te recomendamos completar tu perfil con tu información 
                        académica y profesional para darle más credibilidad a tus publicaciones.
                    </p>
                </div>
                
                <!-- PIE DE PÁGINA -->
                <div style='border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px;'>
                    <p style='color: #6c757d; font-size: 14px; line-height: 1.6;'>
                        Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.
                    </p>
                    <p style='color: #6c757d; font-size: 14px;'>
                        Saludos cordiales,<br>
                        <strong>El equipo de Lab Explorer</strong>
                    </p>
                </div>
                
                <!-- AVISO LEGAL -->
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        Este es un correo automático, por favor no respondas a este mensaje.
                    </p>
                </div>
            </div>
        ";
        
        // ====================================================================
        // PASO 6: ASIGNAR EL CUERPO DEL CORREO
        // ====================================================================
        $mail->Body = $cuerpo;  // Versión HTML
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
        $seccion_motivo = '';
        if (!empty($motivo)) {
            // Si hay motivo, creamos una sección especial para mostrarlo
            $seccion_motivo = "
                <div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin-bottom: 20px;'>
                    <h3 style='color: #856404; margin-top: 0;'>📋 Motivo del rechazo:</h3>
                    <p style='color: #856404; font-size: 15px; line-height: 1.6; margin: 0;'>
                        " . htmlspecialchars($motivo) . "
                    </p>
                </div>
            ";
        }
        
        // ====================================================================
        // CUERPO DEL CORREO
        // ====================================================================
        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <!-- ENCABEZADO -->
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #dc3545; margin-bottom: 10px;'>Actualización de tu Solicitud</h1>
                    <h2 style='color: #333; font-weight: normal;'>Lab Explorer</h2>
                </div>
                
                <!-- MENSAJE PRINCIPAL -->
                <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 20px;'>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        Hola <strong>$nombre_publicador</strong>,
                    </p>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        Gracias por tu interés en formar parte de <strong>Lab Explorer</strong> como publicador.
                    </p>
                    <p style='font-size: 16px; color: #333; line-height: 1.6;'>
                        Lamentablemente, después de revisar tu solicitud, <strong style='color: #dc3545;'>
                        no hemos podido aprobarla en este momento</strong>.
                    </p>
                </div>
                
                <!-- MOTIVO DEL RECHAZO (si existe) -->
                $seccion_motivo
                
                <!-- SUGERENCIAS -->
                <div style='background: #f0f4f6; padding: 20px; border-left: 4px solid #7390A0; margin-bottom: 20px;'>
                    <h3 style='color: #7390A0; margin-top: 0;'>🔄 ¿Qué puedes hacer?</h3>
                    <ul style='color: #333; line-height: 1.8;'>
                        <li>Revisa los requisitos para ser publicador en nuestra plataforma</li>
                        <li>Asegúrate de proporcionar información completa y verificable</li>
                        <li>Puedes volver a solicitar el registro más adelante</li>
                        <li>Si crees que hubo un error, contáctanos para aclararlo</li>
                    </ul>
                </div>
                
                <!-- NOTA INFORMATIVA -->
                <div style='background: #d1ecf1; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>
                    <p style='margin: 0; color: #0c5460; font-size: 14px;'>
                        <strong>💡 Nota:</strong> Aunque no puedas publicar contenido, aún puedes 
                        disfrutar de todos los artículos y recursos disponibles en Lab Explorer como usuario registrado.
                    </p>
                </div>
                
                <!-- PIE DE PÁGINA -->
                <div style='border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px;'>
                    <p style='color: #6c757d; font-size: 14px; line-height: 1.6;'>
                        Agradecemos tu comprensión y tu interés en nuestra plataforma. 
                        Si tienes alguna pregunta, no dudes en contactarnos.
                    </p>
                    <p style='color: #6c757d; font-size: 14px;'>
                        Saludos cordiales,<br>
                        <strong>El equipo de Lab Explorer</strong>
                    </p>
                </div>
                
                <!-- AVISO LEGAL -->
                <div style='text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;'>
                    <p style='color: #999; font-size: 12px; margin: 0;'>
                        Este es un correo automático, por favor no respondas a este mensaje.
                    </p>
                </div>
            </div>
        ";
        
        // ASIGNAR CUERPO
        $mail->Body = $cuerpo;
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
        $mail->isHTML(true);
        
        // ====================================================================
        // PASO 3: CREAR EL CUERPO DEL CORREO
        // ====================================================================
        $cuerpo = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
                <!-- ENCABEZADO CON GRADIENTE -->
                <div style='text-align: center; margin-bottom: 30px; background: linear-gradient(135deg, #7390A0 0%, #5a7080 100%); padding: 30px; border-radius: 10px;'>
                    <h1 style='color: white; margin: 0;'>🔔 Nueva Solicitud de Publicador</h1>
                </div>
                
                <!-- INFORMACIÓN DEL SOLICITANTE -->
                <div style='background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 20px;'>
                    <h2 style='color: #333; margin-top: 0; border-bottom: 2px solid #7390A0; padding-bottom: 10px;'>
                        📋 Información del Solicitante
                    </h2>
                    
                    <!-- TABLA DE DATOS -->
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #7390A0;'>👤 Nombre:</strong>
                            </td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e9ecef; text-align: right;'>
                                <strong>" . htmlspecialchars($nombre_publicador) . "</strong>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e9ecef;'>
                                <strong style='color: #7390A0;'>📧 Email:</strong>
                            </td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #e9ecef; text-align: right;'>
                                " . htmlspecialchars($email_publicador) . "
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 12px 0;'>
                                <strong style='color: #7390A0;'>🔬 Especialidad:</strong>
                            </td>
                            <td style='padding: 12px 0; text-align: right;'>
                                " . htmlspecialchars($especialidad) . "
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- ALERTA DE ACCIÓN REQUERIDA -->
                <div style='background: #fff3cd; padding: 20px; border-left: 4px solid #ffc107; margin-bottom: 25px; border-radius: 5px;'>
                    <p style='margin: 0; color: #856404; font-size: 15px;'>
                        <strong>⚠️ Acción Requerida:</strong> Esta solicitud está pendiente de revisión y aprobación. 
                        Por favor, revisa la información del solicitante y toma una decisión.
                    </p>
                </div>
                
                <!-- BOTÓN DE ACCIÓN -->
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/lab/forms/admins/gestionar_publicadores.php' 
                       style='background: linear-gradient(135deg, #7390A0 0%, #5a7080 100%); 
                              color: white; padding: 15px 40px; text-decoration: none; 
                              border-radius: 25px; display: inline-block; font-weight: bold; 
                              font-size: 16px; box-shadow: 0 4px 15px rgba(115, 144, 160, 0.4);'>
                        🔍 Revisar Solicitud
                    </a>
                </div>
                
                <!-- RECORDATORIO -->
                <div style='background: #f0f4f6; padding: 20px; border-left: 4px solid #7390A0; margin-bottom: 20px; border-radius: 5px;'>
                    <h3 style='color: #7390A0; margin-top: 0; font-size: 16px;'>💡 Recordatorio:</h3>
                    <ul style='color: #333; line-height: 1.8; margin: 10px 0; padding-left: 20px;'>
                        <li>Verifica que la información sea completa y coherente</li>
                        <li>Revisa la especialidad y credenciales si están disponibles</li>
                        <li>Puedes aprobar o rechazar desde el panel de administración</li>
                        <li>El publicador recibirá un correo automático con tu decisión</li>
                    </ul>
                </div>
                
                <!-- PIE DE PÁGINA -->
                <div style='border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px; text-align: center;'>
                    <p style='color: #6c757d; font-size: 14px; line-height: 1.6; margin: 0;'>
                        Este es un correo automático del sistema de notificaciones de Lab Explorer.
                    </p>
                    <p style='color: #999; font-size: 12px; margin: 10px 0 0 0;'>
                        Sistema de Gestión de Publicadores - Lab Explorer © 2025
                    </p>
                </div>
            </div>
        ";
        
        // ASIGNAR CUERPO
        $mail->Body = $cuerpo;
        $mail->AltBody = "Nuevo publicador registrado: $nombre_publicador ($email_publicador) - Especialidad: $especialidad. Por favor revisa la solicitud en el panel de administración.";
        
        // ENVIAR
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error enviando correo a admins: " . $mail->ErrorInfo);
        return false;
    }
}
?>
