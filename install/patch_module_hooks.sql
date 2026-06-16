-- Görünür hook atama tablosu (modül → footer/header/home)
-- mysql -u root -p fshop < install/patch_module_hooks.sql

CREATE TABLE IF NOT EXISTS `module_display_hooks` (
  `id_hook` int(11) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(64) NOT NULL,
  `hook_name` varchar(32) NOT NULL,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_hook`),
  UNIQUE KEY `module_hook` (`module_name`, `hook_name`),
  KEY `hook_name` (`hook_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
