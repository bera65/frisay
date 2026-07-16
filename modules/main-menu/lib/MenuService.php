<?php

class MainMenuService
{
	public static function ensureSchema(): void
	{
		$table = DB::execute("SHOW TABLES LIKE 'main_menu_items'");

		if (!empty($table)) {
			return;
		}

		DB::execute(
			"CREATE TABLE IF NOT EXISTS `main_menu_items` (
				`id_menu_item` int(11) NOT NULL AUTO_INCREMENT,
				`label` varchar(128) NOT NULL DEFAULT '',
				`link_type` varchar(32) NOT NULL DEFAULT 'custom',
				`link_value` varchar(512) NOT NULL DEFAULT '',
				`target` varchar(16) NOT NULL DEFAULT '_self',
				`position` int(11) NOT NULL DEFAULT 0,
				`active` tinyint(1) NOT NULL DEFAULT 1,
				PRIMARY KEY (`id_menu_item`),
				KEY `position` (`position`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
		);

		self::seedDefaults();
	}

	public static function seedDefaults(): void
	{
		$count = (int) DB::getValue('SELECT COUNT(*) FROM main_menu_items');

		if ($count > 0) {
			return;
		}

		$defaults = [
			['Ana Sayfa', 'home', '', 0],
			['İletişim', 'custom', 'contact', 20],
		];

		$cats = Category::getMenuList();
		$pos = 1;

		foreach ($cats as $cat) {
			if ((int) ($cat['id_parent'] ?? 0) > 1 && $pos > 1) {
				// include top-level-ish items
			}
			$defaults[] = [
				(string) ($cat['category_name'] ?? 'Kategori'),
				'category',
				(string) ((int) ($cat['id_category'] ?? 0)),
				$pos++,
			];
			if ($pos > 6) {
				break;
			}
		}

		foreach ($defaults as $row) {
			DB::insert('main_menu_items', [
				'label' => $row[0],
				'link_type' => $row[1],
				'link_value' => $row[2],
				'target' => '_self',
				'position' => (int) $row[3],
				'active' => 1,
			]);
		}
	}

	public static function getAllAdmin(): array
	{
		self::ensureSchema();

		return DB::execute(
			'SELECT * FROM main_menu_items ORDER BY position ASC, id_menu_item ASC'
		) ?: [];
	}

	public static function getActiveItems(): array
	{
		self::ensureSchema();
		global $domain;

		$rows = DB::execute(
			'SELECT * FROM main_menu_items WHERE active = 1 ORDER BY position ASC, id_menu_item ASC'
		) ?: [];

		$items = [];

		foreach ($rows as $row) {
			$url = self::resolveUrl($row);

			if ($url === '') {
				continue;
			}

			$item = [
				'id' => (int) $row['id_menu_item'],
				'label' => (string) $row['label'],
				'url' => $url,
				'target' => ($row['target'] === '_blank') ? '_blank' : '_self',
				'link_type' => (string) $row['link_type'],
				'children' => [],
			];

			if ($item['link_type'] === 'category') {
				$item['children'] = self::getCategoryChildren((int) $row['link_value']);
			}

			$items[] = $item;
		}

		return $items;
	}

	/** @return array<int, array{label: string, url: string}> */
	private static function getCategoryChildren(int $idCategory): array
	{
		global $domain;

		if ($idCategory <= 0) {
			return [];
		}

		$base = rtrim((string) $domain, '/') . '/';
		$children = [];

		foreach (Category::getChildren($idCategory) as $child) {
			$link = trim((string) ($child['category_link'] ?? ''));

			if ($link === '') {
				continue;
			}

			$children[] = [
				'label' => (string) ($child['category_name'] ?? ''),
				'url' => $base . ltrim($link, '/'),
			];
		}

		return $children;
	}

	public static function saveItem(array $data, int $id = 0): array
	{
		self::ensureSchema();

		$label = trim((string) ($data['label'] ?? ''));
		$linkType = (string) ($data['link_type'] ?? 'custom');
		$linkValue = trim((string) ($data['link_value'] ?? ''));
		$target = ((string) ($data['target'] ?? '_self')) === '_blank' ? '_blank' : '_self';
		$position = (int) ($data['position'] ?? 0);
		$active = !empty($data['active']) ? 1 : 0;

		$allowed = ['custom', 'category', 'cms', 'home', 'blog', 'url'];

		if (!in_array($linkType, $allowed, true)) {
			$linkType = 'custom';
		}

		if ($label === '') {
			return ['success' => false, 'message' => 'Menü etiketi gerekli'];
		}

		$payload = [
			'label' => $label,
			'link_type' => $linkType,
			'link_value' => $linkValue,
			'target' => $target,
			'position' => $position,
			'active' => $active,
		];

		if ($id > 0) {
			DB::update('main_menu_items', $payload, 'id_menu_item = :id', ['id' => $id]);

			return ['success' => true, 'message' => 'Menü öğesi güncellendi'];
		}

		$newId = DB::insert('main_menu_items', $payload);

		return $newId
			? ['success' => true, 'message' => 'Menü öğesi eklendi', 'id' => (int) $newId]
			: ['success' => false, 'message' => 'Kayıt başarısız'];
	}

	public static function deleteItem(int $id): array
	{
		if ($id <= 0) {
			return ['success' => false, 'message' => 'Geçersiz öğe'];
		}

		DB::execute('DELETE FROM main_menu_items WHERE id_menu_item = ?', [$id]);

		return ['success' => true, 'message' => 'Silindi'];
	}

	private static function resolveUrl(array $row): string
	{
		global $domain;

		$base = rtrim((string) $domain, '/') . '/';
		$type = (string) ($row['link_type'] ?? 'custom');
		$value = trim((string) ($row['link_value'] ?? ''));

		if ($type === 'home') {
			return $base;
		}

		if ($type === 'blog') {
			return $base . 'blog';
		}

		if ($type === 'category') {
			$id = (int) $value;
			if ($id <= 0) {
				return '';
			}
			$cat = Category::getById($id);
			if (!$cat) {
				return '';
			}
			$link = (string) ($cat['category_link'] ?? '');

			return $link !== '' ? $base . ltrim($link, '/') : '';
		}

		if ($type === 'cms') {
			$id = (int) $value;
			if ($id <= 0) {
				return '';
			}
			$rowCms = DB::getRowSafe('cms_pages', 'id_cms = ? AND active = 1', [$id]);
			if (!$rowCms) {
				return '';
			}
			$slug = (string) ($rowCms['slug'] ?? '');

			return $slug !== '' ? $base . ltrim($slug, '/') : '';
		}

		if ($type === 'url' || $type === 'custom') {
			if ($value === '') {
				return '';
			}
			if (preg_match('#^https?://#i', $value)) {
				return $value;
			}

			return $base . ltrim($value, '/');
		}

		return '';
	}
}
