<?php 
// Verificamos si ya hay una sesión iniciada antes de crear una nueva
if(session_status() === PHP_SESSION_NONE) {
    // PHP_SESSION_NONE significa que las sesiones están habilitadas pero no hay ninguna activa
    // Esto evita el error "session already started"
    session_start();
}

$servidor_db = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$nombre_bd = "lab_exp_db";

// mysqli es la forma moderna de conectarse a MySQL en PHP
$conexion = new mysqli($servidor_db, $usuario_bd, $contrasena_bd, $nombre_bd);

if ($conexion->connect_error) {
    // die() detiene todo el código y muestra un mensaje
    die("error de conexion a msyql:" . $conexion->connect_error);
}

// set_charset configura la codificación de caracteres
// utf8mb4 soporta emojis y caracteres especiales (mejor que utf8 normal)
if (!$conexion->set_charset("utf8mb4")) {
    die ("error al configurar UTF-8:" . $conexion->connect_error);
}