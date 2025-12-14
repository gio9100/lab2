<?php
// apply_migration.php
require 'forms/conexion.php';

$sql = "
CREATE TABLE IF NOT EXISTS lista_negra (
    id INT AUTO_INCREMENT PRIMARY KEY,
    palabra VARCHAR(100) NOT NULL,
    tipo_coincidencia ENUM('exacta', 'parcial') DEFAULT 'parcial',
    accion ENUM('rechazar', 'asteriscos', 'revision') DEFAULT 'rechazar',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tipo_objeto VARCHAR(50) NOT NULL,
    objeto_id INT NOT NULL,
    detalles TEXT,
    ip_origen VARCHAR(45),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS anuncios_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensaje TEXT NOT NULL,
    tipo ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    creado_por INT NOT NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin DATETIME NULL,
    activo TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS metricas_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    tokens_entrada INT DEFAULT 0,
    tokens_salida INT DEFAULT 0,
    modelo VARCHAR(50)
);
";

if ($conn->multi_query($sql)) {
    echo "Tablas creadas correctamente (si no existían).";
} else {
    echo "Error al crear tablas: " . $conn->error;
}
?>
