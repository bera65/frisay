CREATE TABLE IF NOT EXISTS `home-text` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `detail` TEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `home-text` (`id`, `detail`) VALUES (1, '')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);
