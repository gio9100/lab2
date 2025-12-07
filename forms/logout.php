<?php
session_start();

// array() crea un array vacío
// Esto borra TODAS las variables de sesión de golpe
require_once '../mensajes/db.php';

// Verificar y limpiar estado segun rol
if (isset($_SESSION['admin_id'])) {
    $conn->query("UPDATE admins SET ultimo_acceso = NULL WHERE id = " . intval($_SESSION['admin_id']));
} elseif (isset($_SESSION['publicador_id'])) {
    $conn->query("UPDATE publicadores SET ultimo_acceso = NULL WHERE id = " . intval($_SESSION['publicador_id']));
}

$_SESSION = array();

// session_destroy() destruye la sesión completamente del servidor
// Es diferente a solo limpiar las variables
session_destroy();

header('Location: ../index.php');
exit();
?>