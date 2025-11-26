<?php
session_start();

// array() crea un array vacío
// Esto borra TODAS las variables de sesión de golpe
$_SESSION = array();

// session_destroy() destruye la sesión completamente del servidor
// Es diferente a solo limpiar las variables
session_destroy();

header('Location: ../index.php');
exit();
?>