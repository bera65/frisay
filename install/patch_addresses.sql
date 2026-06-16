CREATE TABLE IF NOT EXISTS `user_addresses` (
  `id_address` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `label` varchar(64) NOT NULL DEFAULT '',
  `full_name` varchar(128) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `city` varchar(64) NOT NULL,
  `district` varchar(64) NOT NULL,
  `address_text` text NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_address`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
