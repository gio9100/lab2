CREATE TABLE `usuarios_online` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `usuario_tipo` enum('admin','superadmin','publicador') NOT NULL,
  `ultima_actividad` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;