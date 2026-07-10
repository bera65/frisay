CREATE TABLE IF NOT EXISTS `shopier_products` (
	`id_product` int(11) NOT NULL,
	`shopier_id` varchar(64) NOT NULL DEFAULT '',
	`shopier_url` varchar(512) NOT NULL DEFAULT '',
	`last_status` varchar(32) NOT NULL DEFAULT '',
	`last_error` text NULL,
	`last_sync_at` datetime NULL,
	`date_add` datetime NOT NULL,
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id_product`),
	KEY `shopier_id` (`shopier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shopier_category_map` (
	`id_category` int(11) NOT NULL,
	`shopier_category_id` varchar(64) NOT NULL DEFAULT '',
	`date_upd` datetime NOT NULL,
	PRIMARY KEY (`id_category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
