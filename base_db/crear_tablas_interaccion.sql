

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

CREATE TABLE IF NOT EXISTS `reportes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,

  `tipo` ENUM('publicacion','comentario') NOT NULL,

  `referencia_id` INT(11) NOT NULL COMMENT 'ID de la publicación o comentario reportado',

  `usuario_id` INT(11) NULL COMMENT 'Usuario que hizo el reporte (NULL si fue eliminado)',

  `motivo` VARCHAR(50) NOT NULL COMMENT 'Categoría del reporte',

  `descripcion` TEXT DEFAULT NULL COMMENT 'Descripción adicional del reporte',

  `estado` ENUM('pendiente','revisado','resuelto','ignorado') 
      NOT NULL DEFAULT 'pendiente',

  `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  `fecha_revision` DATETIME DEFAULT NULL,

  `admin_id` INT(11) DEFAULT NULL COMMENT 'Admin que revisó el reporte',

  PRIMARY KEY (`id`),

  KEY `idx_tipo` (`tipo`),
  KEY `idx_estado` (`estado`),
  KEY `idx_usuario_id` (`usuario_id`),

  CONSTRAINT `fk_reportes_usuario`
      FOREIGN KEY (`usuario_id`) 
      REFERENCES `usuarios`(`id`)
      ON DELETE SET NULL
) 
ENGINE=InnoDB 
DEFAULT CHARSET=utf8mb4 
COLLATE=utf8mb4_unicode_ci;


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

