CREATE TABLE IF NOT EXISTS `smart_campaign_rules` (
	`id_rule` int(11) NOT NULL AUTO_INCREMENT,
	`name` varchar(128) NOT NULL DEFAULT '',
	`id_product` int(11) NOT NULL DEFAULT 0,
	`delay_amount` int(11) NOT NULL DEFAULT 7,
	`delay_unit` varchar(8) NOT NULL DEFAULT 'days',
	`trigger_status` tinyint(2) NOT NULL DEFAULT 0,
	`email_subject` varchar(255) NOT NULL DEFAULT '',
	`email_body` text NOT NULL,
	`target_url` varchar(512) NOT NULL DEFAULT '',
	`active` tinyint(1) NOT NULL DEFAULT 1,
	`date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_upd` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_rule`),
	KEY `id_product` (`id_product`),
	KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smart_campaign_queue` (
	`id_queue` int(11) NOT NULL AUTO_INCREMENT,
	`id_rule` int(11) NOT NULL,
	`id_order` int(11) NOT NULL,
	`id_product` int(11) NOT NULL DEFAULT 0,
	`customer_email` varchar(128) NOT NULL DEFAULT '',
	`customer_name` varchar(128) NOT NULL DEFAULT '',
	`product_name` varchar(255) NOT NULL DEFAULT '',
	`order_reference` varchar(32) NOT NULL DEFAULT '',
	`tracking_code` varchar(32) NOT NULL DEFAULT '',
	`send_after` datetime NOT NULL,
	`status` varchar(16) NOT NULL DEFAULT 'pending',
	`error_message` varchar(255) NOT NULL DEFAULT '',
	`sent_at` datetime DEFAULT NULL,
	`click_count` int(11) NOT NULL DEFAULT 0,
	`first_click_at` datetime DEFAULT NULL,
	`date_add` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_queue`),
	UNIQUE KEY `rule_order` (`id_rule`, `id_order`),
	UNIQUE KEY `tracking_code` (`tracking_code`),
	KEY `status_send` (`status`, `send_after`),
	KEY `id_order` (`id_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `smart_campaign_clicks` (
	`id_click` int(11) NOT NULL AUTO_INCREMENT,
	`id_queue` int(11) NOT NULL,
	`tracking_code` varchar(32) NOT NULL DEFAULT '',
	`ip_address` varchar(45) NOT NULL DEFAULT '',
	`user_agent` varchar(255) NOT NULL DEFAULT '',
	`date_click` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`id_click`),
	KEY `id_queue` (`id_queue`),
	KEY `tracking_code` (`tracking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
