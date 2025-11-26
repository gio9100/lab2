-- ============================================================================
-- SCRIPT PARA ELIMINAR COLUMNAS INNECESARIAS DE LA TABLA PUBLICADORES
-- ============================================================================
-- Este script elimina las columnas que ya no se usan en la tabla publicadores
-- Ejecuta este script en phpMyAdmin o en la consola de MySQL

-- IMPORTANTE: Asegúrate de hacer un BACKUP de tu base de datos antes de ejecutar esto
-- Una vez eliminadas, las columnas y sus datos NO se pueden recuperar

USE lab_explorer;

-- Eliminar columnas una por una
ALTER TABLE publicadores DROP COLUMN cv_url;
ALTER TABLE publicadores DROP COLUMN ocid_id;
ALTER TABLE publicadores DROP COLUMN linkedin_url;
ALTER TABLE publicadores DROP COLUMN permisos;
ALTER TABLE publicadores DROP COLUMN area_interes;
ALTER TABLE publicadores DROP COLUMN departamento;
ALTER TABLE publicadores DROP COLUMN avatar;

-- Mensaje de confirmación
SELECT 'Columnas eliminadas exitosamente' AS resultado;
