-- Migración para Mejoras de Administración (Lab-Explora)
-- Este archivo crea las tablas necesarias para:
-- 1. Moderación por Palabras Clave
-- 2. Logs de Auditoría
-- 3. Anuncios del Sistema


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- 1. Tabla: lista_negra (Palabras Prohibidas)
--
CREATE TABLE IF NOT EXISTS `lista_negra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `palabra` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_coincidencia` enum('exacta','parcial') COLLATE utf8mb4_unicode_ci DEFAULT 'parcial',
  `accion` enum('rechazar','asteriscos','revision') COLLATE utf8mb4_unicode_ci DEFAULT 'asteriscos',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `palabra` (`palabra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 2. Tabla: logs_auditoria (Registro de Actividad Admin)
--
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_objeto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ej: publicacion, usuario, comentario',
  `objeto_id` int(11) DEFAULT NULL,
  `detalles` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  KEY `accion` (`accion`),
  KEY `fecha` (`fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- 3. Tabla: anuncios_sistema (Banner Global)
--
CREATE TABLE IF NOT EXISTS `anuncios_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('info','warning','success','danger') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` datetime DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
