CREATE TABLE IF NOT EXISTS `admins` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `password` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- E-posta: admin@fyazilim.com / Şifre: admin123
INSERT IGNORE INTO `admins` (`full_name`, `email`, `password`, `active`) VALUES
('Site Yöneticisi', 'admin@fyazilim.com', '$2y$10$AzMgY8L1.YjCNJ1ja.aShu4VQt/Fjr.vohUUcomM76cHipsqto3/C', 1);
