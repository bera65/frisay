<?php
	Class Settings
	{
		public static function get($title)
		{
			$value = DB::getValue('SELECT value FROM settings WHERE title = ? LIMIT 1', [$title]);

			return $value !== false ? $value : '';
		}

		public static function set(string $title, string $value): bool
		{
			$exists = DB::getValue('SELECT id FROM settings WHERE title = ? LIMIT 1', [$title]);

			if ($exists !== false) {
				$result = DB::update('settings', ['value' => $value], 'title = :where_title', ['where_title' => $title]);

				// MySQL/PDO: deger ayni kaldiysa rowCount 0 doner; bu hata degildir.
				return $result !== false;
			}

			$inserted = DB::insert('settings', [
				'title' => $title,
				'value' => $value,
			]);

			return $inserted !== false;
		}

		public static function getEditableKeys(): array
		{
			return [
				'SITE_NAME' => ['label' => 'Site name', 'group' => 'general'],
				'CONTACT_EMAIL' => ['label' => 'Contact email', 'group' => 'contact'],
				'CONTACT_PHONE' => ['label' => 'Phone (display)', 'group' => 'contact'],
				'CONTACT_PHONE_TEL' => ['label' => 'Phone (tel link)', 'group' => 'contact'],
				'RETURN_REQUEST_DAYS' => ['label' => 'Return request window (days)', 'group' => 'returns'],
				'ORDER_REF_PREFIX' => ['label' => 'Order number prefix', 'group' => 'orders'],
				'ORDER_REF_SUFFIX_MODE' => ['label' => 'Order number format', 'group' => 'orders', 'type' => 'select'],
				'ORDER_REF_PAD' => ['label' => 'Sequential digit count', 'group' => 'orders'],
				'MAIL_DRIVER' => ['label' => 'Mail driver', 'group' => 'mail', 'type' => 'select'],
				'SMTP_HOST' => ['label' => 'SMTP host', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_PORT' => ['label' => 'SMTP port', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_USER' => ['label' => 'SMTP user (email)', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_PASS' => ['label' => 'SMTP password', 'group' => 'smtp', 'type' => 'password'],
				'SMTP_ENCRYPTION' => ['label' => 'Encryption (ssl / tls)', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_FROM_EMAIL' => ['label' => 'From email', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_FROM_NAME' => ['label' => 'From name (empty = site name)', 'group' => 'smtp', 'type' => 'text'],
			];
		}

		public static function getAllForAdmin(): array
		{
			$values = [];

			foreach (self::getEditableKeys() as $key => $meta) {
				$values[$key] = self::get($key);
			}

			return $values;
		}
	}
