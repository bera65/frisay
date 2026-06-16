<?php

class Cms
{
	private static bool $schemaReady = false;

	public static function ensureSchema(): void
	{
		if (self::$schemaReady) {
			return;
		}

		self::$schemaReady = true;

		$table = DB::execute("SHOW TABLES LIKE 'cms_meta'");

		if (empty($table)) {
			DB::execute(
				"CREATE TABLE `cms_meta` (
					`slug` varchar(64) NOT NULL,
					`meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					`meta_description` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					PRIMARY KEY (`slug`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
			);
		}
	}

	public static function getPages(): array
	{
		return [
			'hakkimizda' => [
				'title' => 'Hakkımızda',
				'desc' => 'FShop hakkında bilgi',
				'template' => './cms/hakkimizda.tpl',
			],
			'mesafeli-satis' => [
				'title' => 'Mesafeli Satış Sözleşmesi',
				'desc' => 'Mesafeli satış sözleşmesi ve ön bilgilendirme',
				'template' => './cms/mesafeli-satis.tpl',
			],
			'gizlilik' => [
				'title' => 'Gizlilik ve KVKK',
				'desc' => 'Kişisel verilerin korunması ve gizlilik politikası',
				'template' => './cms/gizlilik.tpl',
			],
			'iade-degisim' => [
				'title' => 'İade ve Değişim',
				'desc' => 'İade ve değişim koşulları',
				'template' => './cms/iade-degisim.tpl',
			],
			'odeme-kargo' => [
				'title' => 'Ödeme ve Kargo',
				'desc' => 'Ödeme yöntemleri ve kargo bilgileri',
				'template' => './cms/odeme-kargo.tpl',
			],
		];
	}

	public static function exists(string $slug): bool
	{
		return isset(self::getPages()[$slug]);
	}

	public static function getSeo(string $slug): array
	{
		self::ensureSchema();

		$page = self::getPages()[$slug] ?? null;

		if (!$page) {
			return ['meta_title' => '', 'meta_description' => ''];
		}

		$row = DB::getRowSafe('cms_meta', 'slug = ?', [$slug]);

		return [
			'meta_title' => trim((string) ($row['meta_title'] ?? '')),
			'meta_description' => trim((string) ($row['meta_description'] ?? '')),
		];
	}

	public static function getBySlug(string $slug): ?array
	{
		$pages = self::getPages();

		if (!isset($pages[$slug])) {
			return null;
		}

		global $domain;
		$seo = self::getSeo($slug);

		return array_merge($pages[$slug], [
			'slug' => $slug,
			'url' => $domain . $slug,
			'meta_title' => $seo['meta_title'],
			'meta_description' => $seo['meta_description'],
		]);
	}

	public static function saveSeo(string $slug, string $metaTitle, string $metaDescription): array
	{
		if (!self::exists($slug)) {
			return ['success' => false, 'message' => 'Sayfa bulunamadı'];
		}

		self::ensureSchema();

		$metaTitle = mb_substr(trim(strip_tags($metaTitle)), 0, 255);
		$metaDescription = mb_substr(trim(strip_tags($metaDescription)), 0, 512);

		$exists = DB::getValue('SELECT slug FROM cms_meta WHERE slug = ? LIMIT 1', [$slug]);

		if ($exists !== false) {
			DB::update(
				'cms_meta',
				[
					'meta_title' => $metaTitle,
					'meta_description' => $metaDescription,
				],
				'slug = :where_slug',
				['where_slug' => $slug]
			);
		} else {
			DB::insert('cms_meta', [
				'slug' => $slug,
				'meta_title' => $metaTitle,
				'meta_description' => $metaDescription,
			]);
		}

		return ['success' => true, 'message' => 'SEO bilgileri kaydedildi'];
	}

	public static function getFooterLinks(): array
	{
		global $domain;
		$links = [];

		foreach (self::getPages() as $slug => $page) {
			$links[] = [
				'slug' => $slug,
				'title' => $page['title'],
				'url' => $domain . $slug,
			];
		}

		return $links;
	}

	public static function getActiveTheme(): string
	{
		$theme = Settings::get('THEME') ?: 'default';

		return preg_match('/^[a-z0-9_-]+$/', $theme) ? $theme : 'default';
	}

	public static function getContentPath(string $slug): ?string
	{
		$pages = self::getPages();

		if (!isset($pages[$slug])) {
			return null;
		}

		$relative = ltrim(str_replace('./', '', $pages[$slug]['template']), '/');
		$themePath = dirname(__DIR__) . '/templates/' . self::getActiveTheme() . '/' . $relative;

		if (is_file($themePath)) {
			return $themePath;
		}

		$fallback = dirname(__DIR__) . '/templates/default/' . $relative;

		return is_file($fallback) ? $fallback : null;
	}

	public static function getContent(string $slug): ?string
	{
		$path = self::getContentPath($slug);

		if (!$path || !is_file($path)) {
			return null;
		}

		return file_get_contents($path);
	}

	public static function saveContent(string $slug, string $content): array
	{
		if (!self::exists($slug)) {
			return ['success' => false, 'message' => 'Sayfa bulunamadı'];
		}

		$path = self::getContentPath($slug);

		if (!$path) {
			return ['success' => false, 'message' => 'Şablon yolu bulunamadı'];
		}

		$dir = dirname($path);
		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'Klasör oluşturulamadı'];
		}

		if (file_put_contents($path, $content) === false) {
			return ['success' => false, 'message' => 'Dosya kaydedilemedi'];
		}

		return ['success' => true, 'message' => 'Sayfa içeriği kaydedildi'];
	}

	public static function getAdminList(): array
	{
		global $adminUrl;
		$base = $adminUrl ?? '';
		$list = [];

		foreach (self::getPages() as $slug => $page) {
			$seo = self::getSeo($slug);
			$list[] = array_merge($page, $seo, [
				'slug' => $slug,
				'edit_url' => $base . 'cms-edit?slug=' . rawurlencode($slug),
			]);
		}

		return $list;
	}
}
