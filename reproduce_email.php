<?php
require_once 'forms/EmailHelper.php';

// Simulate the logic from notificar_publicador.php
$nuevo_estado = 'publicado';
$titulo_publicacion = "Mi Publicación <con> tags & cosas";

$emoji = '✅';
$titulo_email = 'Publicación Aprobada';
$mensaje_principal = "¡Excelentes noticias! Tu publicación ha sido <strong>aprobada</strong> y ahora está visible para todos los usuarios de Lab Explorer.";
$texto_adicional = "Tu contenido ya está disponible en la plataforma y los usuarios pueden acceder a él.";

$mensaje_html = $mensaje_principal . "<br><br>" . $texto_adicional;
$asunto = "$emoji $titulo_email: $titulo_publicacion";

$botonTexto = 'Ver Mis Publicaciones';
$botonLink = 'http://localhost/lab/forms/publicadores/mis-publicaciones.php';
$detalles = [];
$tipo = 'info';

// Simulate EmailHelper::enviarCorreo logic part that generates body
$contenidoFinal = $mensaje_html;

$boton = null;
if ($botonTexto && $botonLink) {
    $boton = ['texto' => $botonTexto, 'url' => $botonLink];
}

$body = EmailHelper::render($asunto, '', $contenidoFinal, $detalles, $boton, $tipo);

echo $body;
?>
