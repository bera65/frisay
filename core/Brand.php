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

		if (!$row) {
			$lang = Lang::current();
			$idBrand = DB::getValue(
				'SELECT id_brand FROM brand_lang WHERE brand_link = ? AND lang = ? LIMIT 1',
				[$link, $lang]
			);

			if ($idBrand === false) {
				$idBrand = DB::getValue(
					'SELECT id_brand FROM brand_lang WHERE brand_link = ? LIMIT 1',
					[$link]
				);
			}

			if ($idBrand !== false) {
				$row = DB::getRowSafe('brands', 'id_brand = ? AND active = 1', [(int) $idBrand]);
			}
		}

		return $row ? Lang::applyBrand($row) : null;
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

	/** @return array<int, array<string, mixed>> */
	public static function getPublicList(int $limit = 12): array
	{
		self::ensureSchema();

		return DB::execute(
			'SELECT id_brand, brand_name, brand_link FROM brands WHERE active = 1 ORDER BY brand_name ASC LIMIT ' . (int) $limit
		) ?: [];
	}

	public static function isLinkUnique(string $link, int $excludeId = 0, ?string $lang = null): bool
	{
		$lang = $lang ?: Lang::getDefault();

		if ($lang === Lang::getDefault()) {
			$sql = 'SELECT COUNT(*) FROM brands WHERE brand_link = ?';
			$params = [$link];

			if ($excludeId > 0) {
				$sql .= ' AND id_brand != ?';
				$params[] = $excludeId;
			}

			if ((int) DB::getValue($sql, $params) > 0) {
				return false;
			}
		}

		$sql = 'SELECT COUNT(*) FROM brand_lang WHERE brand_link = ? AND lang = ?';
		$params = [$link, $lang];

		if ($excludeId > 0) {
			$sql .= ' AND id_brand != ?';
			$params[] = $excludeId;
		}

		return (int) DB::getValue($sql, $params) === 0;
	}

	public static function getLangRows(int $idBrand): array
	{
		Lang::ensureSchema();

		return Lang::getLangRowsMap('brand_lang', 'id_brand', $idBrand);
	}

	private static function saveLangRows(int $idBrand, array $langData): ?array
	{
		Lang::ensureSchema();

		foreach (Lang::getAvailable() as $lang) {
			$entry = is_array($langData[$lang] ?? null) ? $langData[$lang] : [];
			$name = trim((string) ($entry['brand_name'] ?? ''));
			$link = trim((string) ($entry['brand_link'] ?? ''));

			if ($link === '' && $name !== '') {
				$link = Tools::createSlug($name);
			} else {
				$link = Tools::createSlug($link);
			}

			if ($link !== '' && !self::isLinkUnique($link, $idBrand, $lang)) {
				return self::fail('Bu URL slug zaten kullanılıyor (' . Lang::label($lang) . ')');
			}

			Lang::saveLangRow('brand_lang', 'id_brand', $idBrand, $lang, [
				'brand_name' => mb_substr($name, 0, 64),
				'brand_link' => mb_substr($link, 0, 128),
				'meta_title' => mb_substr(trim(strip_tags((string) ($entry['meta_title'] ?? ''))), 0, 255),
				'meta_description' => mb_substr(trim(strip_tags((string) ($entry['meta_description'] ?? ''))), 0, 512),
			]);
		}

		return null;
	}

	public static function save(array $data, int $id = 0): array
	{
		self::ensureSchema();
		Lang::ensureSchema();

		$langData = is_array($data['langs'] ?? null) ? $data['langs'] : [];
		$defaultLang = Lang::getDefault();
		$defaultEntry = is_array($langData[$defaultLang] ?? null) ? $langData[$defaultLang] : $data;

		$name = trim((string) ($defaultEntry['brand_name'] ?? $data['brand_name'] ?? ''));
		$link = trim((string) ($defaultEntry['brand_link'] ?? $data['brand_link'] ?? ''));
		$active = !empty($data['active']) ? 1 : 0;
		$metaTitle = mb_substr(trim(strip_tags((string) ($defaultEntry['meta_title'] ?? $data['meta_title'] ?? ''))), 0, 255);
		$metaDescription = mb_substr(trim(strip_tags((string) ($defaultEntry['meta_description'] ?? $data['meta_description'] ?? ''))), 0, 512);

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

		if (!self::isLinkUnique($link, $id, $defaultLang)) {
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

			if ($ok === false) {
				return self::fail('Marka güncellenemedi');
			}

			$langError = self::saveLangRows($id, $langData);

			return $langError ?: ['success' => true, 'message' => 'Marka güncellendi', 'id' => $id];
		}

		$newId = DB::insert('brands', $row);

		if (!$newId) {
			return self::fail('Marka eklenemedi');
		}

		$newId = (int) $newId;
		$langError = self::saveLangRows($newId, $langData);

		return $langError ?: ['success' => true, 'message' => 'Marka eklendi', 'id' => $newId];
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message, 'id' => 0];
	}
}
