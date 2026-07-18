CREATE TABLE IF NOT EXISTS `product_reviews` (
  `id_review` int(11) NOT NULL AUTO_INCREMENT,
  `id_product` int(11) NOT NULL,
  `id_user` int(11) NOT NULL DEFAULT 0,
  `author_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `rating` tinyint(1) NOT NULL DEFAULT 5,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 0,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_review`),
  KEY `id_product` (`id_product`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `review_invite_queue` (
  `id_queue` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL DEFAULT 0,
  `customer_email` varchar(255) NOT NULL DEFAULT '',
  `customer_name` varchar(255) NOT NULL DEFAULT '',
  `order_reference` varchar(64) NOT NULL DEFAULT '',
  `scheduled_at` datetime NOT NULL,
  `status` varchar(16) NOT NULL DEFAULT 'pending',
  `coupon_code` varchar(32) NOT NULL DEFAULT '',
  `error_message` varchar(255) NOT NULL DEFAULT '',
  `sent_at` datetime DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_queue`),
  UNIQUE KEY `id_order` (`id_order`),
  KEY `status_scheduled` (`status`, `scheduled_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
