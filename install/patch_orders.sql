-- Sipariş tabloları
CREATE TABLE IF NOT EXISTS `orders` (
  `id_order` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `reference` varchar(16) NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT 1,
  `payment_method` varchar(32) NOT NULL,
  `customer_name` varchar(128) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `address_city` varchar(64) NOT NULL,
  `address_district` varchar(64) NOT NULL,
  `address_text` text NOT NULL,
  `note` text NOT NULL,
  `subtotal` decimal(20,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total` decimal(20,2) NOT NULL DEFAULT 0.00,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_order`),
  KEY `id_user` (`id_user`),
  KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `order_detail` (
  `id_order_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `product_name` varchar(128) NOT NULL,
  `price` decimal(20,2) NOT NULL,
  `qty` int(11) NOT NULL,
  `total` decimal(20,2) NOT NULL,
  PRIMARY KEY (`id_order_detail`),
  KEY `id_order` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`title`, `value`) VALUES
('FREE_SHIPPING_MIN', '1500'),
('SHIPPING_FEE', '49.90');
