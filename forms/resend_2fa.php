<?php
// Reenviar código si el usuario no lo recibió
session_start();
require_once 'conexion.php';
require_once '2fa_functions.php';

// Verificar que haya sesión pendiente
if (!isset($_SESSION['pending_2fa'])) {
    header('Location: inicio-sesion.php');
    exit();
}

// Obtener datos
$userType = $_SESSION['pending_2fa']['type'];
$userId = $_SESSION['pending_2fa']['id'];
$email = $_SESSION['pending_2fa']['email'];
$nombre = $_SESSION['pending_2fa']['nombre'];

// Generar nuevo código
$nuevoCodigo = generarCodigo2FA();

// Guardar en BD
guardarCodigo2FA($conexion, $userType, $userId, $nuevoCodigo);

// Enviar email
enviarCodigo2FA($email, $nombre, $nuevoCodigo);

// Mensaje de éxito
$_SESSION['success_2fa'] = "¡Listo! Te enviamos un nuevo código.";

// Volver a página de verificación
header('Location: verify_2fa.php');
exit();
?>
