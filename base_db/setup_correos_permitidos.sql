-- Script para crear la tabla de correos institucionales permitidos

-- Creamos la tabla si no existe
CREATE TABLE IF NOT EXISTS correos_permitidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    -- Identificador único
    valor VARCHAR(255) NOT NULL,
    -- El correo o dominio permitido (ej: @empresa.com o usuario@escuela.edu)
    tipo_acceso ENUM('usuario', 'publicador', 'ambos') DEFAULT 'ambos',
    -- Define si vale para registro normal, publicadores o ambos
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
    -- Fecha de creación automática
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Usamos InnoDB y UTF8mb4 para soporte completo
