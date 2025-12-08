

-- ----------------------------------------------------------------------------
-- TABLA: comentarios
-- Almacena los comentarios que los usuarios hacen en las publicaciones
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `comentarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `contenido` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` enum('activo','reportado','eliminado') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id`),
  KEY `publicacion_id` (`publicacion_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLA: reportes
-- Almacena los reportes que los usuarios hacen sobre publicaciones o comentarios
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reportes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` enum('publicacion','comentario') NOT NULL,
  `referencia_id` int(11) NOT NULL COMMENT 'ID de la publicación o comentario reportado',
  `usuario_id` int(11) NOT NULL COMMENT 'Usuario que hizo el reporte',
  `motivo` varchar(50) NOT NULL COMMENT 'Categoría del reporte',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción adicional del reporte',
  `estado` enum('pendiente','revisado','resuelto','ignorado') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_revision` datetime DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL COMMENT 'Admin que revisó el reporte',
  PRIMARY KEY (`id`),
  KEY `tipo` (`tipo`),
  KEY `estado` (`estado`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLA: likes
-- Almacena los likes y dislikes de las publicaciones
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('like','dislike') NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`publicacion_id`,`usuario_id`) COMMENT 'Un usuario solo puede dar un like/dislike por publicación',
  KEY `publicacion_id` (`publicacion_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- TABLA: leer_mas_tarde
-- Almacena las publicaciones que los usuarios guardan para leer más tarde
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `leer_mas_tarde` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_agregado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_guardado` (`publicacion_id`,`usuario_id`) COMMENT 'Evita duplicados',
  KEY `publicacion_id` (`publicacion_id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

