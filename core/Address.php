<?php

class Address
{
	private const MAX_PER_USER = 10;

	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$columns = [
			'company_name' => "varchar(128) NOT NULL DEFAULT '' AFTER `phone`",
			'tax_office' => "varchar(64) NOT NULL DEFAULT '' AFTER `company_name`",
			'tax_number' => "varchar(20) NOT NULL DEFAULT '' AFTER `tax_office`",
		];

		foreach ($columns as $name => $definition) {
			$exists = DB::execute("SHOW COLUMNS FROM `user_addresses` LIKE '{$name}'");

			if (empty($exists)) {
				DB::execute("ALTER TABLE `user_addresses` ADD COLUMN `{$name}` {$definition}");
			}
		}
	}

	public static function getListForUser(int $idUser): array
	{
		self::ensureSchema();

		$rows = DB::execute(
			'SELECT * FROM user_addresses WHERE id_user = ? ORDER BY is_default DESC, id_address DESC',
			[$idUser]
		);

		return $rows ?: [];
	}

	public static function getForUser(int $idAddress, int $idUser): ?array
	{
		$row = DB::getRowSafe('user_addresses', 'id_address = ? AND id_user = ?', [$idAddress, $idUser]);

		return $row ?: null;
	}

	public static function getDefault(int $idUser): ?array
	{
		$row = DB::getRowSafe('user_addresses', 'id_user = ? AND is_default = 1', [$idUser]);

		if ($row) {
			return $row;
		}

		$rows = self::getListForUser($idUser);

		return $rows[0] ?? null;
	}

	public static function save(int $idUser, array $data, int $idAddress = 0): array
	{
		self::ensureSchema();

		if ($idUser <= 0) {
			return self::fail('Giriş yapmalısınız');
		}

		$validated = self::validate($data);

		if (!$validated['success']) {
			return $validated;
		}

		$payload = $validated['data'];
		$makeDefault = !empty($data['is_default']);

		if ($idAddress > 0) {
			$existing = self::getForUser($idAddress, $idUser);

			if (!$existing) {
				return self::fail('Adres bulunamadı');
			}

			if ($makeDefault) {
				self::clearDefault($idUser);
				$payload['is_default'] = 1;
			}

			DB::update(
				'user_addresses',
				$payload,
				'id_address = :id_address AND id_user = :id_user',
				['id_address' => $idAddress, 'id_user' => $idUser]
			);

			return self::ok('Adres güncellendi', $idAddress);
		}

		$count = (int) DB::getValue(
			'SELECT COUNT(*) FROM user_addresses WHERE id_user = ?',
			[$idUser]
		);

		if ($count >= self::MAX_PER_USER) {
			return self::fail('En fazla ' . self::MAX_PER_USER . ' adres kaydedebilirsiniz');
		}

		if ($count === 0 || $makeDefault) {
			self::clearDefault($idUser);
			$payload['is_default'] = 1;
		}

		$payload['id_user'] = $idUser;
		$newId = DB::insert('user_addresses', $payload);

		if (!$newId) {
			return self::fail('Adres kaydedilemedi');
		}

		return self::ok('Adres kaydedildi', (int) $newId);
	}

	public static function delete(int $idAddress, int $idUser): array
	{
		$address = self::getForUser($idAddress, $idUser);

		if (!$address) {
			return self::fail('Adres bulunamadı');
		}

		DB::execute(
			'DELETE FROM user_addresses WHERE id_address = ? AND id_user = ?',
			[$idAddress, $idUser]
		);

		if ((int) $address['is_default'] === 1) {
			$remaining = self::getListForUser($idUser);

			if ($remaining) {
				self::setDefault((int) $remaining[0]['id_address'], $idUser);
			}
		}

		return self::ok('Adres silindi');
	}

	public static function setDefault(int $idAddress, int $idUser): array
	{
		$address = self::getForUser($idAddress, $idUser);

		if (!$address) {
			return self::fail('Adres bulunamadı');
		}

		self::clearDefault($idUser);

		DB::update(
			'user_addresses',
			['is_default' => 1],
			'id_address = :id_address AND id_user = :id_user',
			['id_address' => $idAddress, 'id_user' => $idUser]
		);

		return self::ok('Varsayılan adres güncellendi');
	}

	public static function formatSummary(array $address): string
	{
		$parts = [
			$address['city'] ?? '',
			$address['district'] ?? '',
			$address['address_text'] ?? '',
		];

		return trim(implode(' / ', array_filter($parts)));
	}

	private static function validate(array $data): array
	{
		$name = trim((string) ($data['full_name'] ?? ''));
		$phone = Customer::normalizePhone((string) ($data['phone'] ?? ''));
		$city = trim((string) ($data['city'] ?? ''));
		$district = trim((string) ($data['district'] ?? ''));
		$addressText = trim((string) ($data['address_text'] ?? ''));
		$label = trim((string) ($data['label'] ?? ''));
		$companyName = mb_substr(trim(strip_tags((string) ($data['company_name'] ?? ''))), 0, 128);
		$taxOffice = mb_substr(trim(strip_tags((string) ($data['tax_office'] ?? ''))), 0, 64);
		$taxNumber = preg_replace('/\D+/', '', (string) ($data['tax_number'] ?? ''));
		$taxNumber = mb_substr($taxNumber, 0, 20);

		if (!Validate::isName($name)) {
			return self::fail('Geçerli bir ad soyad girin');
		}

		if (!Customer::isValidPhone($phone)) {
			return self::fail('Geçerli bir telefon numarası girin');
		}

		if ($city === '' || $district === '' || $addressText === '') {
			return self::fail('Adres bilgilerini eksiksiz doldurun');
		}

		if ($label !== '' && !Validate::isGenericName($label)) {
			return self::fail('Adres başlığı geçersiz');
		}

		return [
			'success' => true,
			'data' => [
				'label' => $label,
				'full_name' => $name,
				'phone' => $phone,
				'company_name' => $companyName,
				'tax_office' => $taxOffice,
				'tax_number' => $taxNumber,
				'city' => $city,
				'district' => $district,
				'address_text' => $addressText,
			],
		];
	}

	private static function clearDefault(int $idUser): void
	{
		DB::execute(
			'UPDATE user_addresses SET is_default = 0 WHERE id_user = ?',
			[$idUser]
		);
	}

	private static function ok(string $message, int $idAddress = 0): array
	{
		return [
			'success' => true,
			'message' => $message,
			'id_address' => $idAddress,
		];
	}

	private static function fail(string $message): array
	{
		return [
			'success' => false,
			'message' => $message,
			'id_address' => 0,
		];
	}
}
