CREATE TABLE IF NOT EXISTS `wapio_templates` (
  `order_status` tinyint(4) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `message` text NOT NULL,
  PRIMARY KEY (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wapio_log` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL DEFAULT 0,
  `order_reference` varchar(32) NOT NULL DEFAULT '',
  `customer_phone` varchar(32) NOT NULL DEFAULT '',
  `order_status` tinyint(4) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `api_status` varchar(32) NOT NULL DEFAULT '',
  `api_message` varchar(512) NOT NULL DEFAULT '',
  `message_id` varchar(128) NOT NULL DEFAULT '',
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_log`),
  KEY `id_order` (`id_order`),
  KEY `order_status` (`order_status`),
  KEY `date_add` (`date_add`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
