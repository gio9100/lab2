<?php
// Clase para enviar correos con diseño profesional
// Centraliza el envío de emails para que todos tengan el mismo estilo

// Cargamos las clases de PHPMailer directamente
// PHPMailer está instalado en la carpeta PHPMailer, no con Composer
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

// Importamos las clases de PHPMailer que vamos a usar
// Así no tenemos que escribir el nombre completo cada vez
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Clase con métodos estáticos para enviar correos
// Se usa así: EmailHelper::enviarCorreo(...)
class EmailHelper {
    
    // Envía un correo electrónico con diseño profesional
    // Parámetros: email destino, asunto, mensaje, botón opcional
    public static function enviarCorreo($destinatario, $asunto, $cuerpo, $botonTexto = null, $botonLink = null) {
        
        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'lab.explorer2025@gmail.com';
            $mail->Password   = 'yero ewft jacf vjzp';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Remitente y destinatario
            $mail->setFrom('lab.explorer2025@gmail.com', 'LabExplorer');
            $mail->addAddress($destinatario);

            // Configuración del correo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8'; // Para tildes y ñ
            $mail->Subject = $asunto;

            // Generamos el HTML del correo
            $mail->Body = self::generarPlantilla($asunto, $cuerpo, $botonTexto, $botonLink);
            
            // Versión texto plano para clientes antiguos
            $mail->AltBody = strip_tags($cuerpo);

            // Enviamos
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Si falla, guardamos el error en el log
            error_log("Error al enviar correo: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Genera el HTML del correo con el diseño bonito
    private static function generarPlantilla($titulo, $contenido, $botonTexto, $botonLink) {
        
        // Colores de la marca
        $colorPrincipal = '#7390A0';
        $colorFondo = '#f8f9fa';
        $colorTexto = '#333333';
        $anio = date('Y');

        // Armamos el botón si nos lo pidieron
        $botonHtml = '';
        if ($botonTexto && $botonLink) {
            $botonHtml = "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$botonLink}' style='
                        background-color: {$colorPrincipal};
                        color: #ffffff;
                        padding: 12px 25px;
                        text-decoration: none;
                        border-radius: 5px;
                        font-weight: bold;
                        display: inline-block;
                    '>{$botonTexto}</a>
                </div>
            ";
        }

        // Leemos el logo en base64 del archivo
        $logoData = '';
        $base64File = __DIR__ . '/logo_base64.txt';
        if (file_exists($base64File)) {
            $logoData = file_get_contents($base64File);
        }

        // HTML del correo (usamos heredoc para escribir HTML cómodamente)
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; background-color: {$colorFondo}; font-family: Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td style="padding: 20px 0; text-align: center; background-color: #ffffff; border-bottom: 3px solid {$colorPrincipal};">
                <!-- Cabecera con Logo -->
                <img src="{$logoData}" alt="LabExplorer Logo" style="max-width: 150px; height: auto;">
            </td>
        </tr>
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" cellspacing="0" cellpadding="0" border="0">
                    <tr>
                        <td style="padding: 40px;">
                            <!-- Título del correo -->
                            <h1 style="color: {$colorPrincipal}; margin: 0 0 20px 0; font-size: 24px; text-align: center;">
                                {$titulo}
                            </h1>
                            
                            <!-- Contenido principal -->
                            <div style="color: {$colorTexto}; line-height: 1.6; font-size: 16px;">
                                {$contenido}
                            </div>

                            <!-- Botón de acción (si existe) -->
                            {$botonHtml}
                            
                            <!-- Despedida -->
                            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; font-size: 14px; color: #666;">
                                <p>Atentamente,<br>El equipo de LabExplorer</p>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; color: #999; font-size: 12px;">
                <!-- Pie de página -->
                <p>&copy; {$anio} LabExplorer. Todos los derechos reservados.</p>
                <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
?>
