CREATE TABLE IF NOT EXISTS `alert_price_subscriptions` (
    `id_subscription` int(11) NOT NULL AUTO_INCREMENT,
    `id_product` int(11) NOT NULL,
    `id_user` int(11) NOT NULL DEFAULT 0,
    `email` varchar(255) NOT NULL,
    `product_name` varchar(255) NOT NULL DEFAULT '',
    `product_url` varchar(500) NOT NULL DEFAULT '',
    `target_price` decimal(15,2) NOT NULL DEFAULT 0.00,
    `current_price_at_subscribe` decimal(15,2) NOT NULL DEFAULT 0.00,
    `price_when_sent` decimal(15,2) NOT NULL DEFAULT 0.00,
    `is_sent` tinyint(1) NOT NULL DEFAULT 0,
    `date_add` datetime NOT NULL,
    `sent_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id_subscription`),
    KEY `idx_product` (`id_product`),
    KEY `idx_email` (`email`),
    KEY `idx_is_sent` (`is_sent`),
    KEY `idx_product_email_unsent` (`id_product`, `email`, `is_sent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;