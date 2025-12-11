-- Agregar 'rechazado' al ENUM de estado en la tabla publicadores
-- Este es el problema: el ENUM no incluía 'rechazado' como valor válido

ALTER TABLE publicadores 
MODIFY COLUMN estado ENUM('activo', 'pendiente', 'suspendido', 'inactivo', 'rechazado') 
DEFAULT 'pendiente';

-- Verificar el cambio
DESCRIBE publicadores;
