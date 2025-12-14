<?php
// Script de configuración inicial para la tabla de IA
// Ejecutar una sola vez

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lab_exp_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Conectado a la base de datos...\n";

// SQL para crear la tabla
$sql = "CREATE TABLE IF NOT EXISTS configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gemini_api_key VARCHAR(255) DEFAULT NULL,
    enable_cognitive_tools TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

if ($conn->query($sql) === TRUE) {
    echo "Tabla 'configuracion_sistema' creada o verificada correctamente.\n";
} else {
    echo "Error creando tabla: " . $conn->error . "\n";
}

// Insertar fila por defecto si no existe
$check = "SELECT id FROM configuracion_sistema LIMIT 1";
$result = $conn->query($check);

if ($result->num_rows == 0) {
    $insert = "INSERT INTO configuracion_sistema (gemini_api_key, enable_cognitive_tools) VALUES ('', 0)";
    if ($conn->query($insert) === TRUE) {
        echo "Configuración por defecto insertada.\n";
    } else {
        echo "Error insertando configuración: " . $conn->error . "\n";
    }
} else {
    echo "La configuración ya existe.\n";
}

$conn->close();
?>
