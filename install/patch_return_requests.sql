-- İade talepleri (mevcut kurulumlar için Schema::ensure otomatik uygular)
CREATE TABLE IF NOT EXISTS `return_requests` (
  `id_return` int(11) NOT NULL AUTO_INCREMENT,
  `id_order` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `customer_message` text NOT NULL,
  `admin_message` text NOT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_resolved` datetime DEFAULT NULL,
  PRIMARY KEY (`id_return`),
  KEY `id_order` (`id_order`),
  KEY `id_user` (`id_user`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `return_request_images` (
  `id_return_image` int(11) NOT NULL AUTO_INCREMENT,
  `id_return` int(11) NOT NULL,
  `image_file` varchar(255) NOT NULL,
  `date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_return_image`),
  KEY `id_return` (`id_return`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `settings` (`title`, `value`) VALUES ('RETURN_REQUEST_DAYS', '14');

ALTER TABLE `return_requests`
  ADD COLUMN IF NOT EXISTS `admin_receipt_file` varchar(255) NOT NULL DEFAULT '' AFTER `admin_message`;
