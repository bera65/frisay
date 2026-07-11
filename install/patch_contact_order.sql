ALTER TABLE `contact_messages`
 ADD COLUMN IF NOT EXISTS `id_order` int(11) NOT NULL DEFAULT 0 AFTER `id_user`;

CREATE TABLE IF NOT EXISTS `contact_replies` (
  `id_reply` int(11) NOT NULL AUTO_INCREMENT,
  `id_message` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL DEFAULT 0,
  `message` text NOT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_reply`),
  KEY `id_message` (`id_message`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
