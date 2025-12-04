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
    // Helper para envío de correos centralizado
    
    // Envía un correo electrónico con diseño profesional
    // Parámetros: email destino, asunto, mensaje, botón opcional, detalles opcionales, tipo de mensaje
    public static function enviarCorreo($destinatario, $asunto, $cuerpo, $botonTexto = null, $botonLink = null, $detalles = [], $tipo = 'info') {
        
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

            // Adjuntar logo como imagen embebida (CID)
            // Intentamos buscar un PNG o JPG primero, si no, usamos el ICO
            $logoPath = __DIR__ . '/../assets/img/logo/logo.png'; // Idealmente PNG
            if (!file_exists($logoPath)) {
                $logoPath = __DIR__ . '/../assets/img/logo/logobrayan2.ico';
            }
            if (!file_exists($logoPath)) {
                $logoPath = __DIR__ . '/../assets/img/logo/logobrayan.ico';
            }
            
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, 'logo_lab');
            }

            // Configuración del correo
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8'; // Para tildes y ñ
            $mail->Subject = $asunto;

            // Lógica para manejar contenido que YA es HTML
            $contenidoFinal = $cuerpo;
            
            // Si el cuerpo contiene <html> o <body>, extraemos solo el contenido relevante
            // para meterlo en nuestra plantilla estándar con logo y estilos
            if (stripos($cuerpo, '<body') !== false) {
                // Usamos DOMDocument para extraer el body de forma segura
                $dom = new DOMDocument();
                // Suprimimos errores de HTML mal formado
                libxml_use_internal_errors(true);
                // Cargamos el HTML (agregamos encoding para acentos)
                $dom->loadHTML('<?xml encoding="utf-8" ?>' . $cuerpo);
                libxml_clear_errors();
                
                $body = $dom->getElementsByTagName('body')->item(0);
                if ($body) {
                    // Importamos los nodos del body al nuevo documento
                    $contenidoFinal = '';
                    foreach ($body->childNodes as $child) {
                        $contenidoFinal .= $dom->saveHTML($child);
                    }
                }
                
                // Limpiamos estilos inline que puedan chocar con nuestro diseño
                // (Opcional, pero recomendado para consistencia)
                $contenidoFinal = preg_replace('/style="[^"]*"/', '', $contenidoFinal);
                $contenidoFinal = preg_replace("/style='[^']*'/", "", $contenidoFinal);
            } elseif (stripos($cuerpo, '<html') !== false) {
                // Si tiene html pero no body (raro, pero posible), quitamos las etiquetas html
                $contenidoFinal = strip_tags($cuerpo, '<p><div><br><b><strong><i><em><ul><ol><li><a><h1><h2><h3><h4><table><tr><td><th>');
            }

            // Generamos el HTML final usando SIEMPRE nuestra plantilla render()
            // Esto asegura que SIEMPRE haya logo y estilos consistentes
            $boton = null;
            if ($botonTexto && $botonLink) {
                $boton = ['texto' => $botonTexto, 'url' => $botonLink];
            }
            
            $mail->Body = self::render($asunto, '', $contenidoFinal, $detalles, $boton, $tipo);
            
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

    // Método público para generar HTML de correos (usado por otros archivos)
    // Parámetros: título, nombre destinatario, mensaje, detalles (array), botón (array), tipo
    public static function render($titulo, $nombre_destinatario, $mensaje, $detalles = [], $boton = null, $tipo = 'info') {
        // Colores estandarizados (SIEMPRE usamos el azul del sitio como base)
        $colorPrincipal = '#7390A0'; // Azul Grisáceo (Color principal del sitio)
        
        // Colores de acento para bordes o detalles, pero manteniendo la identidad
        $colorAcento = $colorPrincipal;
        if ($tipo === 'aprobado') $colorAcento = '#28a745'; // Verde solo para detalles de éxito
        if ($tipo === 'rechazado') $colorAcento = '#dc3545'; // Rojo solo para detalles de error
        
        // Generar HTML de detalles si existen
        $detallesHtml = '';
        if (!empty($detalles)) {
            $detallesHtml = '<div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid ' . $colorAcento . ';">';
            $detallesHtml .= '<table style="width: 100%; border-collapse: collapse;">';
            foreach ($detalles as $clave => $valor) {
                $detallesHtml .= '<tr>';
                $detallesHtml .= '<td style="padding: 8px; font-weight: bold; color: #5a7080; width: 40%; font-family: \'Arial\', sans-serif;">' . htmlspecialchars($clave) . ':</td>';
                $detallesHtml .= '<td style="padding: 8px; color: #212529; font-family: \'Arial\', sans-serif;">' . htmlspecialchars($valor) . '</td>';
                $detallesHtml .= '</tr>';
            }
            $detallesHtml .= '</table>';
            $detallesHtml .= '</div>';
        }
        
        // Generar HTML del botón si existe
        $botonHtml = '';
        if ($boton && isset($boton['texto']) && isset($boton['url'])) {
            $botonHtml = "
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$boton['url']}' style='
                        background-color: {$colorPrincipal};
                        color: #ffffff;
                        padding: 14px 30px;
                        text-decoration: none;
                        border-radius: 50px;
                        font-weight: bold;
                        display: inline-block;
                        font-family: Arial, sans-serif;
                        box-shadow: 0 4px 6px rgba(115, 144, 160, 0.3);
                    '>{$boton['texto']}</a>
                </div>
            ";
        }
        
        return self::generarPlantilla($titulo, $mensaje, $detallesHtml, $botonHtml, $colorPrincipal, $nombre_destinatario);
    }

    // Genera el HTML del correo con diseño optimizado para Gmail
    private static function generarPlantilla($titulo, $contenido, $detallesHtml, $botonHtml, $colorPrincipal = '#7390A0', $nombre_destinatario = '') {
        
        $colorFondo = '#f4f6f8';
        $colorTexto = '#212529';
        $anio = date('Y');

        // Logo externo optimizado (PostImg)
        $logoUrl = 'https://i.postimg.cc/4dHvYPSG/logobrayan-removebg-preview.png';

        // Sanitize title to prevent HTML breakage
        $tituloSeguro = htmlspecialchars($titulo);

        // HTML ultra-compacto para Gmail
        return <<<HTML
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,sans-serif">
<table width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:15px 0;text-align:center;background:#fff">
<img src="{$logoUrl}" alt="LabExplorer" style="max-width:60px;height:auto">
<div style="color:{$colorPrincipal};font-size:18px;font-weight:bold;margin-top:8px">LabExplorer</div>
</td></tr><tr><td style="padding:0 15px 30px">
<table style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px" cellspacing="0" cellpadding="0">
<tr><td style="height:3px;background:{$colorPrincipal}"></td></tr>
<tr><td style="padding:25px">
<h1 style="color:{$colorPrincipal};margin:0 0 15px;font-size:20px;text-align:center">{$tituloSeguro}</h1>
<div style="color:{$colorTexto};line-height:1.6;font-size:14px">{$contenido}</div>
{$detallesHtml}
{$botonHtml}
<div style="margin-top:25px;border-top:1px solid #eee;padding-top:15px;font-size:12px;color:#6c757d;text-align:center">
<p style="margin:0">Atentamente,</p>
<p style="margin:5px 0 0;font-weight:bold;color:{$colorPrincipal}">El equipo de LabExplorer</p>
</div>
</td></tr></table>
</td></tr><tr><td style="padding:0 15px 20px;text-align:center;color:#999;font-size:10px">
<p>&copy; {$anio} LabExplorer</p>
</td></tr></table>
</body></html>
HTML;
    }
}
