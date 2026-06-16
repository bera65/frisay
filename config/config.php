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

				// MySQL/PDO: değer aynı kaldıysa rowCount 0 döner; bu hata değildir.
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
				'SITE_NAME' => ['label' => 'Site Adı', 'group' => 'genel'],
				'CONTACT_EMAIL' => ['label' => 'İletişim E-posta', 'group' => 'iletisim'],
				'CONTACT_PHONE' => ['label' => 'Telefon (görünen)', 'group' => 'iletisim'],
				'CONTACT_PHONE_TEL' => ['label' => 'Telefon (tel: linki)', 'group' => 'iletisim'],
				'FREE_SHIPPING_MIN' => ['label' => 'Ücretsiz kargo limiti (₺)', 'group' => 'kargo'],
				'SHIPPING_FEE' => ['label' => 'Kargo ücreti (₺)', 'group' => 'kargo'],
				'MAIL_DRIVER' => ['label' => 'E-posta gönderim yöntemi', 'group' => 'mail', 'type' => 'select'],
				'SMTP_HOST' => ['label' => 'SMTP Sunucu', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_PORT' => ['label' => 'SMTP Port', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_USER' => ['label' => 'SMTP Kullanıcı (e-posta)', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_PASS' => ['label' => 'SMTP Şifre', 'group' => 'smtp', 'type' => 'password'],
				'SMTP_ENCRYPTION' => ['label' => 'Şifreleme (ssl / tls)', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_FROM_EMAIL' => ['label' => 'Gönderen e-posta', 'group' => 'smtp', 'type' => 'text'],
				'SMTP_FROM_NAME' => ['label' => 'Gönderen adı (boş = site adı)', 'group' => 'smtp', 'type' => 'text'],
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