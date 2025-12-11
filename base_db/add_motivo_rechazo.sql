-- Agregar columna motivo_rechazo a la tabla publicadores
-- Esta columna almacenará el motivo por el cual un publicador fue rechazado

ALTER TABLE publicadores 
ADD COLUMN motivo_rechazo TEXT NULL AFTER estado;

-- Verificar que la columna se agregó correctamente
DESCRIBE publicadores;
