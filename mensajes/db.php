<?php
// mensajes/db.php
// Conexión dedicada para el sistema de mensajería

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
date_default_timezone_set('America/Mexico_City');
?>
