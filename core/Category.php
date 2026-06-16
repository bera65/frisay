<?php

class Category
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$metaTitle = DB::execute("SHOW COLUMNS FROM `categories` LIKE 'meta_title'");

		if (empty($metaTitle)) {
			DB::execute(
				"ALTER TABLE `categories`
				 ADD COLUMN `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' AFTER `category_link`,
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

		$row = DB::getRowSafe('categories', 'category_link = ? AND active = 1', [$link]);

		return $row ?: null;
	}

	public static function getById(int $id): ?array
	{
		$row = DB::getRowSafe('categories', 'id_category = ? AND active = 1', [$id]);

		return $row ?: null;
	}

	public static function getMenuList(): array
	{
		$rows = DB::execute(
			'SELECT * FROM categories WHERE active = 1 AND id_parent > 0 ORDER BY category_name ASC'
		);

		return $rows ?: [];
	}

	public static function getUrl(array $category): string
	{
		global $domain;

		return $domain . $category['category_link'];
	}

	public static function getAdminList(int $activeFilter = -1, int $limit = 50, int $offset = 0): array
	{
		$sql = 'SELECT c.*, p.category_name AS parent_name
			FROM categories c
			LEFT JOIN categories p ON c.id_parent = p.id_category
			WHERE 1=1';
		$params = [];

		if ($activeFilter >= 0) {
			$sql .= ' AND c.active = ?';
			$params[] = $activeFilter;
		}

		$sql .= ' ORDER BY c.id_category ASC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

		return DB::execute($sql, $params) ?: [];
	}

	public static function countAdmin(int $activeFilter = -1): int
	{
		if ($activeFilter >= 0) {
			return (int) DB::getValue('SELECT COUNT(*) FROM categories WHERE active = ?', [$activeFilter]);
		}

		return (int) DB::getValue('SELECT COUNT(*) FROM categories');
	}

	public static function getByIdAdmin(int $id): ?array
	{
		$row = DB::getRowSafe('categories', 'id_category = ?', [$id]);

		return $row ?: null;
	}

	public static function getParentOptions(int $excludeId = 0): array
	{
		$sql = 'SELECT id_category, category_name FROM categories WHERE active = 1';
		$params = [];

		if ($excludeId > 0) {
			$sql .= ' AND id_category != ?';
			$params[] = $excludeId;
		}

		$sql .= ' ORDER BY category_name ASC';

		return DB::execute($sql, $params) ?: [];
	}

	public static function isLinkUnique(string $link, int $excludeId = 0): bool
	{
		$sql = 'SELECT COUNT(*) FROM categories WHERE category_link = ?';
		$params = [$link];

		if ($excludeId > 0) {
			$sql .= ' AND id_category != ?';
			$params[] = $excludeId;
		}

		return (int) DB::getValue($sql, $params) === 0;
	}

	public static function save(array $data, int $id = 0): array
	{
		self::ensureSchema();

		$name = trim((string) ($data['category_name'] ?? ''));
		$link = trim((string) ($data['category_link'] ?? ''));
		$idParent = (int) ($data['id_parent'] ?? 0);
		$active = !empty($data['active']) ? 1 : 0;
		$metaTitle = mb_substr(trim(strip_tags((string) ($data['meta_title'] ?? ''))), 0, 255);
		$metaDescription = mb_substr(trim(strip_tags((string) ($data['meta_description'] ?? ''))), 0, 512);

		if ($name === '') {
			return self::fail('Kategori adı zorunludur');
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
			'category_name' => $name,
			'category_link' => $link,
			'meta_title' => $metaTitle,
			'meta_description' => $metaDescription,
			'id_parent' => max(0, $idParent),
			'active' => $active,
		];

		if ($id > 0) {
			$ok = DB::update('categories', $row, 'id_category = :where_id', ['where_id' => $id]);

			return $ok !== false
				? ['success' => true, 'message' => 'Kategori güncellendi', 'id' => $id]
				: self::fail('Kategori güncellenemedi');
		}

		$newId = DB::insert('categories', $row);

		return $newId
			? ['success' => true, 'message' => 'Kategori eklendi', 'id' => (int) $newId]
			: self::fail('Kategori eklenemedi');
	}

	private static function fail(string $message): array
	{
		return ['success' => false, 'message' => $message, 'id' => 0];
	}
}
