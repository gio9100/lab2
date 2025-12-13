-- Actualización para tabla publicaciones
-- Agrega columnas para soporte de subida de archivos (PDF, Word, Imágenes como contenido)

USE lab_exp_db;

-- 1. Agregar columna para la ruta del archivo
-- Se usa VARCHAR(255) para guardar el nombre/ruta del archivo en 'uploads/'
ALTER TABLE publicaciones
ADD COLUMN archivo_url VARCHAR(255) NULL AFTER imagen_principal;

-- 2. Agregar columna para el tipo de archivo
-- Se usa VARCHAR(50) para guardar la extensión o tipo MIME (ej: 'pdf', 'docx', 'imagen_contenido')
ALTER TABLE publicaciones
ADD COLUMN tipo_archivo VARCHAR(50) NULL AFTER archivo_url;

-- Nota: Si las columnas ya existen, este script podría dar error. 
-- Es recomendable ejecutarlo solo una vez o verificar existencia antes.
