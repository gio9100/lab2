-- TABLA DE LOGS DE VERIFICACION DE CREDENCIALES
-- Para registrar quién escanea las credenciales y cuándo
-- Archivo separado como solicitado

CREATE TABLE IF NOT EXISTS `logs_verificacion_credencial` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_usuario` enum('admin','publicador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_verificacion` datetime DEFAULT current_timestamp(),
  `ip_escaner` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exitoso` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
