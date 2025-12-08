-- ====================================================================
-- SCRIPT SQL: Corrección de Columna 2FA (Si ya existe la tabla)
-- ====================================================================
-- Este script actualiza la columna 'code' de VARCHAR(6) a VARCHAR(255)
-- para soportar hashes bcrypt (~60 caracteres) en lugar de texto plano
-- ====================================================================

-- Ampliar columna para almacenar hashes bcrypt
ALTER TABLE two_factor_codes 
MODIFY COLUMN code VARCHAR(255) NOT NULL COMMENT 'Código encriptado con bcrypt (hash de ~60 caracteres)';

-- ====================================================================
-- FIN - Columna actualizada para soportar encriptación
-- ====================================================================
-- NOTA: Ejecuta este script solo si ya tenías la tabla creada con VARCHAR(6)
-- Si vas a crear la tabla desde cero, usa setup_2fa.sql directamente
-- ====================================================================
