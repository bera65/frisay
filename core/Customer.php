<?php

class Customer
{
	public static function normalizePhone(string $phone): string
	{
		$digits = preg_replace('/\D/', '', $phone);

		if (strlen($digits) === 10 && strpos($digits, '5') === 0) {
			$digits = '0' . $digits;
		}

		return $digits;
	}

	public static function isValidPhone(string $phone): bool
	{
		return (bool) preg_match('/^05[0-9]{9}$/', self::normalizePhone($phone));
	}

	public static function hashPassword(string $password): string
	{
		return password_hash($password, PASSWORD_DEFAULT);
	}

	public static function verifyPassword(string $password, string $hash): bool
	{
		$info = password_get_info($hash);

		if ($info['algo'] !== null && $info['algo'] !== 0) {
			return password_verify($password, $hash);
		}

		if (strlen($hash) === 32 && ctype_xdigit($hash)) {
			return hash_equals(Tools::hash($password), $hash);
		}

		return false;
	}

	public static function register(string $fullName, string $phone, string $password, string $email = ''): array
	{
		$phone = self::normalizePhone($phone);
		$fullName = trim($fullName);
		$email = trim(strtolower($email));

		if (!Validate::isName($fullName)) {
			return self::fail('Geçerli bir ad soyad girin');
		}

		if (!self::isValidPhone($phone)) {
			return self::fail('Geçerli bir telefon numarası girin (05xx xxx xx xx)');
		}

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return self::fail('Geçerli bir e-posta adresi girin');
		}

		$passwordError = Security::validatePassword($password);

		if ($passwordError !== null) {
			return self::fail($passwordError);
		}

		$exists = DB::getValue('SELECT id_user FROM users WHERE phone = ? LIMIT 1', [$phone]);
		if ($exists) {
			return self::fail('Bu telefon numarası zaten kayıtlı');
		}

		$emailExists = DB::getValue('SELECT id_user FROM users WHERE email = ? LIMIT 1', [$email]);
		if ($emailExists) {
			return self::fail('Bu e-posta adresi zaten kayıtlı');
		}

		$id = DB::insert('users', [
			'user_full_name' => $fullName,
			'phone' => $phone,
			'email' => $email,
			'password' => self::hashPassword($password),
			'active' => 1,
		]);

		if (!$id) {
			return self::fail('Kayıt oluşturulamadı');
		}

		self::loginSession((int) $id, true);
		Notification::welcome((int) $id, $fullName);
		Mail::sendWelcome($email, $fullName);

		return self::ok('Kayıt başarılı');
	}

	public static function login(string $phone, string $password, bool $remember = true): array
	{
		$phone = self::normalizePhone($phone);
		$identifier = RateLimit::loginIdentifier($phone);

		if (RateLimit::isLimited(RateLimit::SCOPE_CUSTOMER_LOGIN, $identifier, 8, 900)) {
			return self::fail('Çok fazla başarısız giriş denemesi. Lütfen 15 dakika sonra tekrar deneyin.');
		}

		if (!self::isValidPhone($phone)) {
			RateLimit::record(RateLimit::SCOPE_CUSTOMER_LOGIN, $identifier);

			return self::fail('Telefon veya şifre hatalı');
		}

		$user = DB::getRowSafe('users', 'phone = ? AND active = 1', [$phone]);

		if (!$user || !self::verifyPassword($password, $user['password'])) {
			RateLimit::record(RateLimit::SCOPE_CUSTOMER_LOGIN, $identifier);

			return self::fail('Telefon veya şifre hatalı');
		}

		RateLimit::clear(RateLimit::SCOPE_CUSTOMER_LOGIN, $identifier);

		self::upgradePasswordIfNeeded((int) $user['id_user'], $password, $user['password']);
		self::loginSession((int) $user['id_user'], $remember);

		return self::ok('Giriş başarılı');
	}

	public static function loginSession(int $idUser, bool $remember = true): void
	{
		session_regenerate_id(true);
		$_SESSION['id_user'] = $idUser;

		if ($remember) {
			Cookie::issueRememberToken($idUser);
		}
	}

	public static function logout(): void
	{
		if (!empty($_SESSION['id_user'])) {
			DB::execute(
				'UPDATE users SET login_code = ? WHERE id_user = ?',
				['', (int) $_SESSION['id_user']]
			);
		}

		Cookie::clearRememberCookie();
		unset($_SESSION['id_user']);
		session_regenerate_id(true);
	}

	public static function isLoggedIn(): bool
	{
		return !empty($_SESSION['id_user']);
	}

	public static function getId(): int
	{
		return (int) ($_SESSION['id_user'] ?? 0);
	}

	public static function getCurrent(): ?array
	{
		if (!self::isLoggedIn()) {
			return null;
		}

		$user = DB::getRowSafe('users', 'id_user = ? AND active = 1', [self::getId()]);

		if (!$user) {
			self::logout();

			return null;
		}

		unset($user['password'], $user['login_code']);

		return $user;
	}

	public static function publicUser(?array $user): ?array
	{
		if (!$user) {
			return null;
		}

		return [
			'id_user' => (int) $user['id_user'],
			'user_full_name' => $user['user_full_name'],
			'phone' => $user['phone'],
			'email' => $user['email'] ?? '',
			'image' => $user['image'],
		];
	}

	public static function updateProfile(string $fullName, string $phone, string $email = ''): array
	{
		if (!self::isLoggedIn()) {
			return self::fail('Giriş yapmalısınız');
		}

		$fullName = trim($fullName);
		$phone = self::normalizePhone($phone);
		$email = trim(strtolower($email));
		$idUser = self::getId();
		$current = self::getCurrent();

		if (!$current) {
			return self::fail('Oturum bulunamadı');
		}

		if (!Validate::isName($fullName)) {
			return self::fail('Geçerli bir ad soyad girin');
		}

		if (!self::isValidPhone($phone)) {
			return self::fail('Geçerli bir telefon numarası girin');
		}

		if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return self::fail('Geçerli bir e-posta adresi girin');
		}

		if ($phone !== $current['phone']) {
			$exists = DB::getValue(
				'SELECT id_user FROM users WHERE phone = ? AND id_user != ? LIMIT 1',
				[$phone, $idUser]
			);

			if ($exists) {
				return self::fail('Bu telefon numarası başka bir hesapta kayıtlı');
			}
		}

		if ($email !== '' && $email !== ($current['email'] ?? '')) {
			$emailExists = DB::getValue(
				'SELECT id_user FROM users WHERE email = ? AND id_user != ? LIMIT 1',
				[$email, $idUser]
			);

			if ($emailExists) {
				return self::fail('Bu e-posta adresi başka bir hesapta kayıtlı');
			}
		}

		$updated = DB::update(
			'users',
			[
				'user_full_name' => $fullName,
				'phone' => $phone,
				'email' => $email,
			],
			'id_user = :id_user',
			['id_user' => $idUser]
		);

		if ($updated === false) {
			return self::fail('Profil güncellenemedi');
		}

		return self::ok('Profil bilgileriniz güncellendi');
	}

	public static function updatePassword(string $currentPassword, string $newPassword): array
	{
		if (!self::isLoggedIn()) {
			return self::fail('Giriş yapmalısınız');
		}

		$user = DB::getRowSafe('users', 'id_user = ? AND active = 1', [self::getId()]);

		if (!$user) {
			return self::fail('Oturum bulunamadı');
		}

		if (!self::verifyPassword($currentPassword, $user['password'])) {
			return self::fail('Mevcut şifre hatalı');
		}

		$passwordError = Security::validatePassword($newPassword);

		if ($passwordError !== null) {
			return self::fail($passwordError);
		}

		$updated = DB::update(
			'users',
			['password' => self::hashPassword($newPassword)],
			'id_user = :id_user',
			['id_user' => (int) $user['id_user']]
		);

		if ($updated === false) {
			return self::fail('Şifre güncellenemedi');
		}

		return self::ok('Şifreniz güncellendi');
	}

	private static function upgradePasswordIfNeeded(int $idUser, string $password, string $storedHash): void
	{
		$info = password_get_info($storedHash);

		if ($info['algo'] !== null && $info['algo'] !== 0) {
			if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
				DB::update(
					'users',
					['password' => self::hashPassword($password)],
					'id_user = :id_user',
					['id_user' => $idUser]
				);
			}

			return;
		}

		if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
			DB::update(
				'users',
				['password' => self::hashPassword($password)],
				'id_user = :id_user',
				['id_user' => $idUser]
			);
		}
	}

	public static function countAdmin(string $query = ''): int
	{
		$params = [];
		$where = '1=1';

		if ($query !== '') {
			$where .= ' AND (user_full_name LIKE ? OR phone LIKE ? OR email LIKE ?)';
			$like = '%' . $query . '%';
			$params = [$like, $like, $like];
		}

		return (int) DB::getValue('SELECT COUNT(*) FROM users WHERE ' . $where, $params);
	}

	public static function getAdminList(string $query = '', int $limit = 30, int $offset = 0): array
	{
		$params = [];
		$where = '1=1';

		if ($query !== '') {
			$where .= ' AND (u.user_full_name LIKE ? OR u.phone LIKE ? OR u.email LIKE ?)';
			$like = '%' . $query . '%';
			$params = [$like, $like, $like];
		}

		$sql = 'SELECT u.*, COUNT(o.id_order) AS order_count,
				COALESCE(SUM(o.total), 0) AS order_total
				FROM users u
				LEFT JOIN orders o ON o.id_user = u.id_user
				WHERE ' . $where . '
				GROUP BY u.id_user
				ORDER BY u.date_add DESC
				LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		$rows = DB::execute($sql, $params) ?: [];

		foreach ($rows as &$row) {
			$row['order_count'] = (int) $row['order_count'];
			$row['order_total_formatted'] = Tools::displayPrice($row['order_total']);
			$row['date_formatted'] = Tools::formatDate3($row['date_add']);
			$row['active'] = (int) $row['active'];
		}
		unset($row);

		return $rows;
	}

	public static function getByIdAdmin(int $idUser): ?array
	{
		$user = DB::getRowSafe('users', 'id_user = ?', [$idUser]);

		if (!$user) {
			return null;
		}

		unset($user['password'], $user['login_code']);
		$user['date_formatted'] = Tools::formatDate3($user['date_add']);
		$user['orders'] = Order::getUserOrders($idUser);

		return $user;
	}

	public static function requestPasswordReset(string $email): array
	{
		$email = trim(strtolower($email));

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return self::fail('Geçerli bir e-posta adresi girin');
		}

		$user = DB::getRowSafe('users', 'email = ? AND active = 1', [$email]);

		if ($user) {
			global $domain;

			$rawToken = bin2hex(random_bytes(32));
			$tokenHash = hash('sha256', $rawToken);
			$expires = date('Y-m-d H:i:s', time() + 3600);

			DB::update(
				'users',
				[
					'reset_token' => $tokenHash,
					'reset_expires' => $expires,
				],
				'id_user = :id_user',
				['id_user' => (int) $user['id_user']]
			);

			$resetUrl = rtrim($domain, '/') . '/reset-password?token=' . $rawToken;
			Mail::sendPasswordReset($email, (string) $user['user_full_name'], $resetUrl);
		}

		return self::ok('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi');
	}

	public static function resetPassword(string $token, string $password, string $password2): array
	{
		$token = trim($token);

		$passwordError = Security::validatePassword($password);

		if ($passwordError !== null) {
			return self::fail($passwordError);
		}

		if ($password !== $password2) {
			return self::fail('Şifreler eşleşmiyor');
		}

		if ($token === '' || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
			return self::fail('Geçersiz veya süresi dolmuş bağlantı');
		}

		$tokenHash = hash('sha256', $token);
		$user = DB::getRowSafe(
			'users',
			'reset_token = ? AND active = 1 AND reset_expires IS NOT NULL AND reset_expires > NOW()',
			[$tokenHash]
		);

		if (!$user) {
			return self::fail('Geçersiz veya süresi dolmuş bağlantı');
		}

		$updated = DB::update(
			'users',
			[
				'password' => self::hashPassword($password),
				'reset_token' => '',
				'reset_expires' => null,
				'login_code' => '',
			],
			'id_user = :id_user',
			['id_user' => (int) $user['id_user']]
		);

		if ($updated === false) {
			return self::fail('Şifre güncellenemedi');
		}

		Cookie::clearRememberCookie();

		return self::ok('Şifreniz güncellendi, giriş yapabilirsiniz');
	}

	public static function setActive(int $idUser, bool $active): array
	{
		if ($idUser <= 0) {
			return self::fail('Geçersiz müşteri');
		}

		$updated = DB::update(
			'users',
			['active' => $active ? 1 : 0],
			'id_user = :id_user',
			['id_user' => $idUser]
		);

		if ($updated === false) {
			return self::fail('Müşteri güncellenemedi');
		}

		return [
			'success' => true,
			'message' => $active ? 'Müşteri aktif edildi' : 'Müşteri pasif edildi',
		];
	}

	private static function ok(string $message): array
	{
		return [
			'success' => true,
			'message' => $message,
			'user' => self::publicUser(self::getCurrent()),
		];
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
			'user' => null,
		];
	}

	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$googleId = DB::execute("SHOW COLUMNS FROM `users` LIKE 'google_id'");

		if (empty($googleId)) {
			DB::execute(
				"ALTER TABLE `users`
				 ADD COLUMN `google_id` varchar(64) DEFAULT NULL AFTER `email`,
				 ADD UNIQUE KEY `google_id` (`google_id`)"
			);
		}
	}

	public static function authWithGoogle(string $googleId, string $email, string $fullName): array
	{
		self::ensureSchema();

		$googleId = trim($googleId);
		$email = strtolower(trim($email));
		$fullName = trim($fullName);

		if ($googleId === '') {
			return self::fail(translate('Invalid request, please refresh and try again'));
		}

		if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return self::fail(translate('Please enter a valid email'));
		}

		if ($fullName === '') {
			$fullName = strstr($email, '@', true) ?: 'Google User';
		}

		$user = DB::getRowSafe('users', 'google_id = ? AND active = 1', [$googleId]);

		if ($user) {
			self::loginSession((int) $user['id_user'], true);

			return self::ok(translate('Login successful'));
		}

		$user = DB::getRowSafe('users', 'email = ? AND active = 1', [$email]);

		if ($user) {
			DB::execute(
				'UPDATE users SET google_id = ?, user_full_name = IF(user_full_name = "", ?, user_full_name) WHERE id_user = ?',
				[$googleId, $fullName, (int) $user['id_user']]
			);
			self::loginSession((int) $user['id_user'], true);

			return self::ok(translate('Login successful'));
		}

		$phone = self::generateGooglePlaceholderPhone($googleId);
		$id = DB::insert('users', [
			'user_full_name' => mb_substr($fullName, 0, 128),
			'phone' => $phone,
			'email' => $email,
			'google_id' => $googleId,
			'password' => self::hashPassword(bin2hex(random_bytes(16))),
			'active' => 1,
		]);

		if (!$id) {
			return self::fail(translate('Register failed'));
		}

		self::loginSession((int) $id, true);
		Notification::welcome((int) $id, $fullName);
		Mail::sendWelcome($email, $fullName);

		return self::ok(translate('Register successful'));
	}

	private static function generateGooglePlaceholderPhone(string $googleId): string
	{
		$base = '05' . str_pad((string) (abs(crc32($googleId)) % 1000000000), 9, '0', STR_PAD_LEFT);
		$phone = $base;
		$attempt = 0;

		while (DB::getValue('SELECT id_user FROM users WHERE phone = ? LIMIT 1', [$phone]) && $attempt < 20) {
			$phone = '05' . str_pad((string) random_int(100000000, 999999999), 9, '0', STR_PAD_LEFT);
			$attempt++;
		}

		return $phone;
	}
}
