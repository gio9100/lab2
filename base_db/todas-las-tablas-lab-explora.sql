-- ==============================================================================
-- BASE DE DATOS MAESTRA: LAB EXPLORER (v1.0 Final)
-- ==============================================================================
-- Fecha: 2025-12-14
-- Descripción: Esquema consolidado con mejoras de integridad, seguridad y estandarización.
-- Cotejamiento: utf8mb4_unicode_ci (Soporte completo para emojis y acentos)
-- ==============================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ==============================================================================
-- 1. TABLAS PRINCIPALES (CORE)
-- ==============================================================================

-- 1.1 CONFIGURACIÓN DEL SISTEMA
CREATE TABLE IF NOT EXISTS `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gemini_api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `enable_cognitive_tools` tinyint(1) DEFAULT 0,
  `enable_quiz` tinyint(1) DEFAULT 0,
  `enable_chat_qa` tinyint(1) DEFAULT 0,
  `enable_writing_assistant` tinyint(1) DEFAULT 0,
  `enable_auto_moderation` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.2 USUARIOS (Lectores / Normales)
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `correo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reset_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  `imagen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rol` enum('usuario','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'usuario',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT '2FA Opcional para usuarios',
  `blocked_until` datetime DEFAULT NULL,
  `fecha_registro` timestamp DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `correo` (`correo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.3 ADMINISTRADORES
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` enum('superadmin','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'admin',
  `estado` enum('activo','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'activo',
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0 COMMENT '2FA (Puede ser obligatorio por política)',
  `blocked_until` datetime DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 1.4 CATEGORÍAS
CREATE TABLE IF NOT EXISTS `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#007acc',
  `icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('activa','inactiva') COLLATE utf8mb4_unicode_ci DEFAULT 'activa',
  `fecha_creacion` timestamp DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 2. PUBLICADORES Y CONTENIDO
-- ==============================================================================

-- 2.1 PUBLICADORES
CREATE TABLE IF NOT EXISTS `publicadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `especialidad` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `titulo_academico` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `institucion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biografia` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_perfil` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  -- Stats y Límites
  `experiencia_años` int(11) DEFAULT 0,
  `limite_publicaciones_mes` int(11) DEFAULT 10,
  `publicaciones_este_mes` int(11) DEFAULT 0,
  
  -- Estado y Seguridad
  `estado` enum('activo','pendiente','suspendido','inactivo') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `motivo_suspension` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `blocked_until` datetime DEFAULT NULL,
  
  -- Auth Tokens
  `reset_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL,
  
  -- Fechas
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_activacion` timestamp NULL DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `fk_publicadores_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.2 PUBLICACIONES
CREATE TABLE IF NOT EXISTS `publicaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicador_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  
  -- Contenido Principal
  `titulo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resumen` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenido` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('articulo','noticia','tutorial','investigacion') COLLATE utf8mb4_unicode_ci DEFAULT 'articulo',
  
  -- Multimedia y Archivos
  `imagen_principal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `usar_imagen_completa` tinyint(1) DEFAULT 0,
  `archivo_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_archivo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pdf_adjunto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `archivos_adjuntos` text COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'JSON',
  
  -- SEO
  `meta_descripcion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` longtext COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  
  -- Estado
  `estado` enum('publicado','borrador','revision','rechazado','rechazada') COLLATE utf8mb4_unicode_ci DEFAULT 'borrador',
  `motivo_rechazo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mensaje_rechazo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  
  -- Métricas
  `vistas` int(11) DEFAULT 0,
  `likes` int(11) DEFAULT 0,
  
  -- Fechas
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `fecha_publicacion` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `publicador_id` (`publicador_id`),
  KEY `categoria_id` (`categoria_id`),
  CONSTRAINT `fk_publicaciones_publicador` FOREIGN KEY (`publicador_id`) REFERENCES `publicadores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_publicaciones_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 3. INTERACCIÓN Y COMUNIDAD
-- ==============================================================================

-- 3.1 COMENTARIOS
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('activo','reportado','eliminado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'activo',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Soft Delete',
  PRIMARY KEY (`id`),
  KEY `publicacion_id` (`publicacion_id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_comentarios_publicacion` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comentarios_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.2 LIKES
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('like','dislike') COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`publicacion_id`,`usuario_id`),
  CONSTRAINT `fk_likes_publicacion` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_likes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.3 GUARDAR PARA MÁS TARDE
CREATE TABLE IF NOT EXISTS `leer_mas_tarde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guardado` (`publicacion_id`,`usuario_id`),
  CONSTRAINT `fk_leer_publicacion` FOREIGN KEY (`publicacion_id`) REFERENCES `publicaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_leer_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.4 REPORTES
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('publicacion','comentario') COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `motivo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` enum('pendiente','revisado','resuelto','ignorado') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pendiente',
  `admin_id` int(11) DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_revision` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  CONSTRAINT `fk_reportes_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 4. HERRAMIENTAS DE ADMINISTRACIÓN Y SISTEMA
-- ==============================================================================

-- 4.1 LISTA NEGRA (Palabras Prohibidas)
CREATE TABLE IF NOT EXISTS `lista_negra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `palabra` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_coincidencia` enum('exacta','parcial') COLLATE utf8mb4_unicode_ci DEFAULT 'parcial',
  `accion` enum('rechazar','asteriscos','revision') COLLATE utf8mb4_unicode_ci DEFAULT 'asteriscos',
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `palabra` (`palabra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.2 LOGS DE AUDITORÍA
CREATE TABLE IF NOT EXISTS `logs_auditoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `accion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_objeto` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objeto_id` int(11) DEFAULT NULL,
  `detalles` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_origen` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`) -- No FK estricta para mantener logs históricos si se borra admin
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.3 ANUNCIOS DEL SISTEMA
CREATE TABLE IF NOT EXISTS `anuncios_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('info','warning','success','danger') COLLATE utf8mb4_unicode_ci DEFAULT 'info',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` datetime DEFAULT current_timestamp(),
  `fecha_fin` datetime DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.4 MÉTRICAS IA
CREATE TABLE IF NOT EXISTS `metricas_ia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokens_input` int(11) DEFAULT 0,
  `tokens_output` int(11) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4.5 LOGS MODERACIÓN IA
CREATE TABLE IF NOT EXISTS `moderacion_ia_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `decision` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `confianza` int(11) NOT NULL,
  `revisado_por` int(11) DEFAULT NULL,
  `admin_de_acuerdo` tinyint(1) DEFAULT NULL,
  `comentarios_admin` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_analisis` datetime DEFAULT current_timestamp(),
  `fecha_revision` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ==============================================================================
-- 5. UTILIDADES Y SEGURIDAD
-- ==============================================================================

-- 5.1 CÓDIGOS 2FA
CREATE TABLE IF NOT EXISTS `two_factor_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('usuario','publicador','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_type`,`user_id`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.2 USUARIOS ONLINE
CREATE TABLE IF NOT EXISTS `usuarios_online` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `usuario_tipo` enum('admin','superadmin','publicador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ultima_actividad` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.3 MENSAJERÍA
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remitente_id` int(11) NOT NULL,
  `remitente_tipo` enum('admin','publicador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `destinatario_tipo` enum('admin','publicador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_chat` (`remitente_id`,`remitente_tipo`,`destinatario_id`,`destinatario_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5.4 CORREOS PERMITIDOS
CREATE TABLE IF NOT EXISTS `correos_permitidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_acceso` enum('usuario','publicador','ambos') COLLATE utf8mb4_unicode_ci DEFAULT 'ambos',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==============================================================================
-- 6. EVENTOS DEL SISTEMA
-- ==============================================================================

SET GLOBAL event_scheduler = ON;

DROP EVENT IF EXISTS cleanup_expired_2fa_codes;
CREATE EVENT cleanup_expired_2fa_codes
ON SCHEDULE EVERY 1 HOUR
DO
  DELETE FROM two_factor_codes WHERE expires_at < NOW() OR used = 1;

COMMIT;
