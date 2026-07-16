CREATE TABLE IF NOT EXISTS `ftheme_settings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(128) NOT NULL,
  `detail` VARCHAR(480) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ftheme_settings` (`title`, `detail`) VALUES
('HEADER', '1'),
('FOOTER', '1'),
('LOADING', '0'),
('DEFAULT-COLOR', '2563EB'),
('THEME-FONT', 'Poppins'),
('FEATURE-TITLE', 'Öne Çıkan Modüller'),
('FEATURE-DESC', 'Frisay uyumlu, kuruluma hazır e-ticaret modülleri'),
('FOOTER-TEXT', 'Frisay açık kaynak e-ticaret altyapısı ile güçlendirilmiş modern mağaza deneyimi.'),
('GOTO-TOP', '1'),
('SHOW-COOKIE', '0'),
('COOKIE-TEXT', 'Deneyiminizi iyileştirmek için çerezler kullanıyoruz. Siteyi kullanmaya devam ederek çerez politikamızı kabul etmiş olursunuz.'),
('SHOW-TOP-BAR', '1')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);
