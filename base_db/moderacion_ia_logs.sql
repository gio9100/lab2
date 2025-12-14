CREATE TABLE `moderacion_ia_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `publicacion_id` int(11) NOT NULL,
  `decision` varchar(50) NOT NULL,
  `razon` text NOT NULL,
  `confianza` int(11) NOT NULL,
  `fecha_analisis` datetime DEFAULT current_timestamp(),
  `revisado_por` int(11) DEFAULT NULL,
  `fecha_revision` datetime DEFAULT NULL,
  `admin_de_acuerdo` tinyint(1) DEFAULT NULL,
  `comentarios_admin` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;