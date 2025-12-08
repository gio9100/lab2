<?php
// Cancelar proceso de 2FA y volver al login
session_start();

// Limpiar sesión temporal
unset($_SESSION['pending_2fa']);
unset($_SESSION['intentos_2fa']);
unset($_SESSION['error_2fa']);
unset($_SESSION['success_2fa']);

// Mensaje info
$_SESSION['info'] = "Verificación cancelada";

// Volver al login
header('Location: inicio-sesion.php');
exit();
?>
