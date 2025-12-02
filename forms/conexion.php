<?php 
// Archivo de conexión a la base de datos
// La sesión se inicia en usuario.php, no aquí

$servidor_db = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$nombre_bd = "lab_exp_db";

$conexion = new mysqli($servidor_db, $usuario_bd, $contrasena_bd, $nombre_bd);
if ($conexion->connect_error) {
    die("error de conexion a msyql:" . $conexion->connect_error);
}
if (!$conexion->set_charset("utf8mb4")) {
    die ("error al configurar UTF-8:" . $conexion->connect_error);
}