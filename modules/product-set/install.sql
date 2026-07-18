CREATE TABLE IF NOT EXISTS `product_set_items` (
  `id_set_item` int(11) NOT NULL AUTO_INCREMENT,
  `id_set_product` int(11) NOT NULL,
  `id_product` int(11) NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_set_item`),
  UNIQUE KEY `set_child` (`id_set_product`, `id_product`),
  KEY `id_set_product` (`id_set_product`),
  KEY `id_product` (`id_product`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
