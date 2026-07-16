-- Modül sistemi tablosu
-- mysql -u root -p fshop < install/patch_modules.sql

CREATE TABLE IF NOT EXISTS `modules` (
  `id_module` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `version` varchar(16) NOT NULL DEFAULT '1.0.0',
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `installed` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_module`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
