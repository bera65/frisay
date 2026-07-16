CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL DEFAULT 0,
  `full_name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `phone` varchar(20) NOT NULL DEFAULT '',
  `subject` varchar(128) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`title`, `value`) VALUES
('CONTACT_EMAIL', 'destek@fyazilim.com');
