-- Mevcut kurulumlar için users tablosu
CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `user_full_name` varchar(128) NOT NULL DEFAULT '',
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `image` varchar(128) NOT NULL DEFAULT '',
  `login_code` varchar(64) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `phone` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Test kullanıcısı: 0555 123 45 67 / 123456
INSERT IGNORE INTO `users` (`id_user`, `user_full_name`, `phone`, `password`, `active`) VALUES
(1, 'Demo Kullanıcı', '05551234567', '$2y$10$GPLawYLHswCkWbeq8.ErQeI2/Eq.nLk4r1kG/PzfjhlDN2k3z3JTG', 1);
