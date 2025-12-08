-- ====================================================================
-- SCRIPT SQL: Sistema de Autenticación de Dos Factores (2FA)
-- ====================================================================
-- Este script configura completamente el sistema de verificación en 2 pasos
-- con códigos encriptados usando bcrypt para máxima seguridad
-- ====================================================================

-- ============================================================
-- PASO 1: Agregar columnas 2FA a tablas de usuarios
-- ============================================================

-- Tabla usuarios: agregar soporte para 2FA
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) DEFAULT 0 COMMENT 'Si el usuario tiene 2FA activado (0=no, 1=si)',
ADD COLUMN IF NOT EXISTS blocked_until DATETIME NULL COMMENT 'Bloqueado hasta esta fecha por intentos fallidos';

-- Tabla publicadores: agregar soporte para 2FA (OBLIGATORIO para publicadores)
ALTER TABLE publicadores 
ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) DEFAULT 1 COMMENT 'Si el publicador tiene 2FA activado (obligatorio)',
ADD COLUMN IF NOT EXISTS blocked_until DATETIME NULL COMMENT 'Bloqueado hasta esta fecha';

-- Tabla admins: agregar soporte para 2FA (OBLIGATORIO para administradores)
ALTER TABLE admins 
ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) DEFAULT 1 COMMENT 'Si el admin tiene 2FA activado (obligatorio)',
ADD COLUMN IF NOT EXISTS blocked_until DATETIME NULL COMMENT 'Bloqueado hasta esta fecha';

-- ============================================================
-- PASO 2: Crear tabla para códigos temporales encriptados
-- ============================================================

CREATE TABLE IF NOT EXISTS two_factor_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('usuario', 'publicador', 'admin') NOT NULL COMMENT 'Tipo de usuario',
    user_id INT NOT NULL COMMENT 'ID del usuario según su tipo',
    code VARCHAR(255) NOT NULL COMMENT 'Código encriptado con bcrypt (hash de ~60 caracteres)',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Cuándo se creó el código',
    expires_at DATETIME NOT NULL COMMENT 'Cuándo expira (10 minutos)',
    used TINYINT(1) DEFAULT 0 COMMENT 'Si ya se usó el código (0=no, 1=si)',
    ip_address VARCHAR(45) COMMENT 'IP desde donde se solicitó (IPv4 o IPv6)',
    
    INDEX idx_user (user_type, user_id),
    INDEX idx_expires (expires_at),
    INDEX idx_used (used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci 
COMMENT='Códigos temporales encriptados para verificación en 2 pasos';

-- ============================================================
-- PASO 3: Evento automático de limpieza de códigos expirados
-- ============================================================

-- Habilitar eventos (si no está habilitado)
SET GLOBAL event_scheduler = ON;

-- Crear evento para limpiar códigos expirados cada hora
DROP EVENT IF EXISTS cleanup_expired_2fa_codes;

CREATE EVENT cleanup_expired_2fa_codes
ON SCHEDULE EVERY 1 HOUR
COMMENT 'Limpia códigos 2FA expirados o usados cada hora'
DO
    DELETE FROM two_factor_codes 
    WHERE expires_at < NOW() OR used = 1;

-- ====================================================================
-- FIN DEL SCRIPT - Sistema 2FA configurado exitosamente
-- ====================================================================
-- CARACTERÍSTICAS:
-- ✓ Códigos encriptados con bcrypt (VARCHAR 255)
-- ✓ Expiración automática en 10 minutos
-- ✓ Límite de intentos con bloqueo temporal
-- ✓ Limpieza automática de códigos antiguos
-- ✓ Soporte para usuarios, publicadores y administradores
-- ====================================================================
