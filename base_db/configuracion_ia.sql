CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `gemini_api_key` varchar(255) DEFAULT NULL,
  `enable_cognitive_tools` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `enable_quiz` tinyint(1) DEFAULT 0,
  `enable_chat_qa` tinyint(1) DEFAULT 0,
  `enable_writing_assistant` tinyint(1) DEFAULT 0,
  `enable_auto_moderation` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;