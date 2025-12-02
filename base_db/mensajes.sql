-- Tabla para el sistema de mensajería
CREATE TABLE IF NOT EXISTS mensajes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    remitente_id INT NOT NULL,
    remitente_tipo ENUM('admin', 'publicador') NOT NULL,
    destinatario_id INT NOT NULL,
    destinatario_tipo ENUM('admin', 'publicador') NOT NULL,
    mensaje TEXT NOT NULL,
    leido BOOLEAN DEFAULT FALSE,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_remitente (remitente_id, remitente_tipo),
    INDEX idx_destinatario (destinatario_id, destinatario_tipo),
    INDEX idx_conversacion (remitente_id, remitente_tipo, destinatario_id, destinatario_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
