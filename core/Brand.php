<?php

class Brand
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$metaTitle = DB::execute("SHOW COLUMNS FROM `brands` LIKE 'meta_title'");

		if (empty($metaTitle)) {
			DB::execute(
				"ALTER TABLE `brands`
				 ADD COLUMN `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `brand_link`,
				 ADD COLUMN `meta_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `meta_title`"
			);
		}
	}

	public static function getByLink(string $link): ?array
	{
		$link = trim($link);

		if ($link === '') {
			return null;
		}

		$row = DB::getRowSafe('brands', 'brand_link = ? AND active = 1', [$link]);

		return $row ?: null;
	}

	public static function getByIdAdmin(int $id): ?array
	{
		$row = DB::getRowSafe('brands', 'id_brand = ?', [$id]);

		return $row ?: null;
	}

	public static function getUrl(array $brand): string
	{
		global $domain;

		return $domain . 'marka/' . $brand['brand_link'];
	}

	public static function getAdminList(int $activeFilter = -1, int $limit = 50, int $offset = 0): array
	{
		$sql = 'SELECT * FROM brands WHERE 1=1';
		$params = [];

		if ($activeFilter >= 0) {
			$sql .= ' AND active = ?';
			$params[] = $activeFilter;
		}

		$sql .= ' ORDER BY brand_name ASC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		return DB::execute($sql, $params) ?: [];
	}

	public static function countAdmin(int $activeFilter = -1): int
	{
		if ($activeFilter >= 0) {
			return (int) DB::getValue('SELECT COUNT(*) FROM brands WHERE active = ?', [$activeFilter]);
		}

		return (int) DB::getValue('SELECT COUNT(*) FROM brands');
	}

	public static function getOptions(): array
	{
		return DB::execute('SELECT id_brand, brand_name FROM brands WHERE active = 1 ORDER BY brand_name ASC') ?: [];
	}

	public static function isLinkUnique(string $link, int $excludeId = 0): bool
	{
		$sql = 'SELECT COUNT(*) FROM brands WHERE brand_link = ?';
		$params = [$link];

		if ($excludeId > 0) {
			$sql .= ' AND id_brand != ?';
			$params[] = $excludeId;
		}

		return (int) DB::getValue($sql, $params) === 0;
	}

	public static function save(array $data, int $id = 0): array
	{
		self::ensureSchema();

		$name = trim((string) ($data['brand_name'] ?? ''));
		$link = trim((string) ($data['brand_link'] ?? ''));
		$active = !empty($data['active']) ? 1 : 0;
		$metaTitle = mb_substr(trim(strip_tags((string) ($data['meta_title'] ?? ''))), 0, 255);
		$metaDescription = mb_substr(trim(strip_tags((string) ($data['meta_description'] ?? ''))), 0, 512);

		if ($name === '') {
			return self::fail('Marka adı zorunludur');
		}

		if ($link === '') {
			$link = Tools::createSlug($name);
		} else {
			$link = Tools::createSlug($link);
		}

		if ($link === '') {
			return self::fail('Geçerli bir URL slug girin');
		}

		if (!self::isLinkUnique($link, $id)) {
			return self::fail('Bu URL slug zaten kullanılıyor');
		}

		$row = [
			'brand_name' => $name,
			'brand_link' => $link,
			'meta_title' => $metaTitle,
			'meta_description' => $metaDescription,
			'active' => $active,
		];

		if ($id > 0) {
			$ok = DB::update('brands', $row, 'id_brand = :where_id', ['where_id' => $id]);

			return $ok !== false
				? ['success' => true, 'message' => 'Marka güncellendi', 'id' => $id]
				: self::fail('Marka güncellenemedi');
		}

		$newId = DB::insert('brands', $row);

		return $newId
			? ['success' => true, 'message' => 'Marka eklendi', 'id' => (int) $newId]
			: self::fail('Marka eklenemedi');
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message, 'id' => 0];
	}
}
