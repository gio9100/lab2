-- Revert logs_auditoria table to original state
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;

-- 1. Remove new columns
ALTER TABLE `logs_auditoria` DROP COLUMN `tipo_actor`;
ALTER TABLE `logs_auditoria` DROP COLUMN `publicador_id`;
ALTER TABLE `logs_auditoria` DROP COLUMN `usuario_id`;

-- 2. Delete rows with NULL admin_id (created by users/publishers during the short time feature was active)
DELETE FROM `logs_auditoria` WHERE `admin_id` IS NULL;

-- 3. Make admin_id NOT NULL again
ALTER TABLE `logs_auditoria` MODIFY COLUMN `admin_id` int(11) NOT NULL;

COMMIT;
