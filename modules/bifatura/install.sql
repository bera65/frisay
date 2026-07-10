CREATE TABLE IF NOT EXISTS `bifatura_invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_order` INT UNSIGNED NOT NULL,
  `reference` VARCHAR(24) NOT NULL DEFAULT '',
  `invoice_no` VARCHAR(64) NOT NULL DEFAULT '',
  `ettn` VARCHAR(64) NOT NULL DEFAULT '',
  `system_type` VARCHAR(16) NOT NULL DEFAULT '',
  `pdf_link` TEXT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'created',
  `message` TEXT NULL,
  `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order` (`id_order`),
  KEY `idx_reference` (`reference`),
  KEY `idx_ettn` (`ettn`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
