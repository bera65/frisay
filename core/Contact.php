<?php

class Contact
{
	public static function submit(array $data): array
	{
		if (!empty($data['website'])) {
			return self::ok(self::t('Your message has been received. We will get back to you soon.'));
		}

		$name = trim((string) ($data['full_name'] ?? ''));
		$email = trim((string) ($data['email'] ?? ''));
		$phone = trim((string) ($data['phone'] ?? ''));
		$subject = trim((string) ($data['subject'] ?? ''));
		$message = trim((string) ($data['message'] ?? ''));

		if (!Validate::isName($name)) {
			return self::fail(self::t('Please enter a valid full name'));
		}

		if (!Validate::isEmail($email)) {
			return self::fail(self::t('Please enter a valid email address'));
		}

		if ($phone !== '' && !Validate::isPhoneNumber($phone)) {
			return self::fail(self::t('Please enter a valid phone number'));
		}

		if ($subject !== '' && !Validate::isGenericName($subject)) {
			return self::fail(self::t('Subject contains invalid characters'));
		}

		if (Tools::strlen($message) < 10) {
			return self::fail(self::t('Message must be at least 10 characters'));
		}

		if (!Validate::isCleanHtml($message)) {
			return self::fail(self::t('Message contains invalid content'));
		}

		$idUser = Customer::isLoggedIn() ? Customer::getId() : 0;
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';

		if (self::isRateLimited($ip, $email)) {
			return self::fail(self::t('You are sending messages too frequently, please wait'));
		}

		$id = DB::insert('contact_messages', [
			'id_user' => $idUser,
			'full_name' => $name,
			'email' => $email,
			'phone' => $phone,
			'subject' => $subject !== '' ? $subject : 'Genel',
			'message' => $message,
			'ip_address' => $ip,
		]);

		if (!$id) {
			return self::fail(self::t('Could not send message, please try again'));
		}

		self::notifyAdmin($name, $email, $subject, $message);

		return self::ok(self::t('Your message has been received. We will get back to you soon.'));
	}

	private static function isRateLimited(string $ip, string $email): bool
	{
		$since = date('Y-m-d H:i:s', time() - 300);

		$count = (int) DB::getValue(
			'SELECT COUNT(*) FROM contact_messages
			WHERE date_add >= ? AND (ip_address = ? OR email = ?)',
			[$since, $ip, $email]
		);

		return $count >= 3;
	}

	private static function notifyAdmin(string $name, string $email, string $subject, string $message): void
	{
		$to = Settings::get('CONTACT_EMAIL');

		if ($to === '' || !Validate::isEmail($to)) {
			return;
		}

		$subjectLine = '[FShop] ' . ($subject !== '' ? $subject : 'İletişim Formu');
		$body = "Ad Soyad: {$name}\nE-posta: {$email}\nKonu: {$subject}\n\n{$message}";
		$headers = 'From: noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
			. 'Reply-To: ' . $email . "\r\n"
			. 'Content-Type: text/plain; charset=UTF-8';

		@mail($to, $subjectLine, $body, $headers);
	}

	private static function ok(string $message): array
	{
		return [
			'success' => true,
			'message' => $message,
		];
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
		];
	}

	private static function t(string $message): string
	{
		return function_exists('translate') ? translate($message) : $message;
	}

	public static function countUnread(): int
	{
		return (int) DB::getValue('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
	}

	public static function getAdminList(int $limit = 30, int $offset = 0, ?int $readFilter = null): array
	{
		$sql = 'SELECT * FROM contact_messages WHERE 1=1';
		$params = [];

		if ($readFilter === 0) {
			$sql .= ' AND is_read = 0';
		} elseif ($readFilter === 1) {
			$sql .= ' AND is_read = 1';
		}

		$sql .= ' ORDER BY date_add DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		return DB::execute($sql, $params) ?: [];
	}

	public static function countAdmin(?int $readFilter = null): int
	{
		$sql = 'SELECT COUNT(*) FROM contact_messages WHERE 1=1';
		$params = [];

		if ($readFilter === 0) {
			$sql .= ' AND is_read = 0';
		} elseif ($readFilter === 1) {
			$sql .= ' AND is_read = 1';
		}

		return (int) DB::getValue($sql, $params);
	}

	public static function getById(int $id): ?array
	{
		$row = DB::getRowSafe('contact_messages', 'id_message = ?', [$id]);

		return $row ?: null;
	}

	public static function markRead(int $id): bool
	{
		return DB::update(
			'contact_messages',
			['is_read' => 1],
			'id_message = :id_message',
			['id_message' => $id]
		) !== false;
	}
}
