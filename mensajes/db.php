<?php
// Conexión a MySQL para el sistema de mensajería

// Datos de conexión a la base de datos
$servername = "localhost";  // Servidor MySQL (localhost = esta máquina)
$username = "root";          // Usuario de MySQL (root = administrador)
$password = "";              // Contraseña de MySQL (vacía en XAMPP)
$dbname = "lab_exp_db";      // Nombre de la base de datos

// new mysqli() = crea conexión a MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hubo error al conectar
// connect_error = propiedad que contiene el error
if ($conn->connect_error) {
    // die() = detiene script y muestra mensaje
    die("Connection failed: " . $conn->connect_error);
}

// set_charset() = establece codificación de caracteres
// utf8mb4 = soporta emojis, ñ, acentos, etc.
$conn->set_charset("utf8mb4");

// date_default_timezone_set() = establece zona horaria
// America/Mexico_City = hora de México
date_default_timezone_set('America/Mexico_City');
?>
