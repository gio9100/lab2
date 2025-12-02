-- ============================================================================
-- SCRIPT PARA AGREGAR COLUMNAS DE RECUPERACIÓN DE CONTRASEÑA A PUBLICADORES
-- ============================================================================
-- Este script agrega las columnas necesarias para que los publicadores
-- puedan recuperar su contraseña olvidada

USE lab_exp_db;
-- Seleccionamos la base de datos

-- Agregamos la columna para guardar el token de recuperación
ALTER TABLE publicadores 
ADD COLUMN reset_token VARCHAR(64) NULL DEFAULT NULL AFTER password;
-- VARCHAR(64) porque el token tiene 64 caracteres (32 bytes en hexadecimal)
-- NULL permite que esté vacío cuando no hay solicitud de recuperación
-- DEFAULT NULL lo pone en NULL por defecto

-- Agregamos la columna para guardar cuándo expira el token
ALTER TABLE publicadores 
ADD COLUMN token_expira DATETIME NULL DEFAULT NULL AFTER reset_token;
-- DATETIME guarda fecha y hora
-- NULL permite que esté vacío cuando no hay token activo
-- DEFAULT NULL lo pone en NULL por defecto

-- Verificamos que se agregaron correctamente
SELECT 'Columnas agregadas exitosamente a la tabla publicadores' AS resultado;
