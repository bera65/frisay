-- Stok, bildirim, kupon ve müşteri özellikleri (Schema::ensure() otomatik uygular)

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `email` varchar(128) NOT NULL DEFAULT '' AFTER `phone`;

CREATE TABLE IF NOT EXISTS `user_notifications` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `type` varchar(32) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `link` varchar(255) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_notification`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `coupons` (
  `id_coupon` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_cart` decimal(10,2) NOT NULL DEFAULT 0.00,
  `max_uses` int(11) NOT NULL DEFAULT 0,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `date_from` datetime DEFAULT NULL,
  `date_to` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_coupon`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `orders`
  ADD COLUMN IF NOT EXISTS `coupon_code` varchar(32) NOT NULL DEFAULT '' AFTER `note`,
  ADD COLUMN IF NOT EXISTS `coupon_discount` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `coupon_code`;
