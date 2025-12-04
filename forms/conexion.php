<?php
// Archivo de conexión a la base de datos MySQL
// Este archivo se incluye en todos los scripts que necesitan acceso a la BD

// Datos de conexión a MySQL
$servername = "localhost";  // Servidor donde está MySQL (localhost = esta misma máquina)
$username = "root";          // Usuario de MySQL (root = administrador)
$password = "";              // Contraseña de MySQL (vacía por defecto en XAMPP)
$dbname = "lab_exp_db";      // Nombre de la base de datos del proyecto

// new mysqli() = crea nueva conexión a MySQL
$conexion = new mysqli($servername, $username, $password, $dbname);

// Verificar si hubo error al conectar
// connect_error = propiedad que contiene el error si falló
if ($conexion->connect_error) {
    // die() = detiene el script y muestra mensaje
    die("Error de conexión: " . $conexion->connect_error);
}

// set_charset() = establece codificación de caracteres
// utf8mb4 = soporta todos los caracteres (emojis, ñ, acentos, etc.)
$conexion->set_charset("utf8mb4");
?>