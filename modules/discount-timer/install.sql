CREATE TABLE IF NOT EXISTS `discount_timer` (
	`id_product` int(11) NOT NULL,
	`starts_at` datetime NULL,
	`ends_at` datetime NULL,
	`date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id_product`),
	KEY `starts_at` (`starts_at`),
	KEY `ends_at` (`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
