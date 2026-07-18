<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class FthemeBlocks
{
	public const SETTING_KEY = 'HOME-BLOCKS';

	/** @var int[] */
	public const BANNER_WIDTHS = [25, 33, 50, 66, 100];

	/** @return array<int, array<string, mixed>> */
	public static function getDefaultBlocks(): array
	{
		return [
			[
				'id' => 'home_slider',
				'type' => 'slider',
				'enabled' => true,
				'label' => 'Ana slider',
			],
			[
				'id' => 'featured',
				'type' => 'featured',
				'enabled' => true,
				'label' => 'Öne çıkan modüller',
			],
			[
				'id' => 'home_promo_slider',
				'type' => 'promo',
				'enabled' => true,
				'label' => 'Promo slider',
			],
			[
				'id' => 'categories',
				'type' => 'categories',
				'enabled' => true,
				'label' => 'Kategori blokları',
				'limit' => 2,
			],
			[
				'id' => 'home_text',
				'type' => 'home_text',
				'enabled' => true,
				'label' => 'Ana sayfa metni',
			],
		];
	}

	/** @return array<int, array<string, mixed>> */
	public static function getBlocks(): array
	{
		$row = DB::getRowSafe('ftheme_settings', 'title = ?', [self::SETTING_KEY]);
		$raw = (string) ($row['detail'] ?? '');

		if ($raw === '') {
			return self::getDefaultBlocks();
		}

		$decoded = json_decode($raw, true);

		if (!is_array($decoded) || $decoded === []) {
			return self::getDefaultBlocks();
		}

		return self::normalizeBlocks($decoded);
	}

	/** @return array<int, array<string, mixed>> */
	public static function getEnabledBlocks(): array
	{
		$blocks = [];

		foreach (self::getBlocks() as $block) {
			if (!empty($block['enabled'])) {
				$blocks[] = $block;
			}
		}

		return $blocks;
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks
	 * @return array<int, array<string, mixed>>
	 */
	public static function buildRenderUnits(array $blocks): array
	{
		$units = [];
		$bannerBuffer = [];

		foreach ($blocks as $block) {
			if (($block['type'] ?? '') === 'banner') {
				$bannerBuffer[] = $block;
				continue;
			}

			if ($bannerBuffer !== []) {
				$units[] = [
					'type' => 'banner_row',
					'banners' => $bannerBuffer,
				];
				$bannerBuffer = [];
			}

			$units[] = [
				'type' => 'block',
				'block' => $block,
			];
		}

		if ($bannerBuffer !== []) {
			$units[] = [
				'type' => 'banner_row',
				'banners' => $bannerBuffer,
			];
		}

		return $units;
	}

	/** @param array<int, array<string, mixed>> $blocks */
	public static function saveBlocks(array $blocks): bool
	{
		$normalized = self::normalizeBlocks($blocks);
		$json = json_encode($normalized, JSON_UNESCAPED_UNICODE);

		if ($json === false) {
			return false;
		}

		return self::saveSetting(self::SETTING_KEY, $json);
	}

	/**
	 * @param array<int, mixed> $blocks
	 * @return array<int, array<string, mixed>>
	 */
	public static function normalizeBlocks(array $blocks): array
	{
		$allowedTypes = ['slider', 'featured', 'promo', 'categories', 'home_text', 'html', 'banner'];
		$normalized = [];

		foreach ($blocks as $block) {
			if (!is_array($block)) {
				continue;
			}

			$id = preg_replace('/[^a-z0-9_-]/', '', (string) ($block['id'] ?? ''));

			if ($id === '') {
				continue;
			}

			$type = (string) ($block['type'] ?? 'html');

			if (!in_array($type, $allowedTypes, true)) {
				$type = 'html';
			}

			$item = [
				'id' => $id,
				'type' => $type,
				'enabled' => !empty($block['enabled']),
				'label' => trim((string) ($block['label'] ?? '')) ?: self::labelForType($type),
			];

			if ($type === 'categories') {
				$item['limit'] = max(1, min(6, (int) ($block['limit'] ?? 2)));
			}

			if ($type === 'html') {
				$item['title'] = trim((string) ($block['title'] ?? ''));
				$item['content'] = (string) ($block['content'] ?? '');
			}

			if ($type === 'banner') {
				$item['image'] = trim((string) ($block['image'] ?? ''));
				$item['link'] = trim((string) ($block['link'] ?? ''));
				$item['width'] = self::normalizeBannerWidth($block['width'] ?? 100);
			}

			$normalized[] = $item;
		}

		if ($normalized === []) {
			return self::getDefaultBlocks();
		}

		return $normalized;
	}

	public static function normalizeBannerWidth($width): int
	{
		$width = (int) $width;

		if (!in_array($width, self::BANNER_WIDTHS, true)) {
			return 100;
		}

		return $width;
	}

	public static function labelForType(string $type): string
	{
		$labels = [
			'slider' => 'Ana slider',
			'featured' => 'Öne çıkan modüller',
			'promo' => 'Promo slider',
			'categories' => 'Kategori blokları',
			'home_text' => 'Ana sayfa metni',
			'html' => 'Özel bölüm',
			'banner' => 'Banner',
		];

		return $labels[$type] ?? 'Blok';
	}

	public static function createCustomBlockId(): string
	{
		return 'custom_' . substr(bin2hex(random_bytes(4)), 0, 8);
	}

	private static function saveSetting(string $title, string $detail): bool
	{
		if (!Validate::isGenericName($title)) {
			return false;
		}

		$row = DB::getRowSafe('ftheme_settings', 'title = ?', [$title]);

		if ($row) {
			return DB::update('ftheme_settings', ['detail' => $detail], 'title = :where_title', ['where_title' => $title]) !== false;
		}

		return DB::insert('ftheme_settings', [
			'title' => $title,
			'detail' => $detail,
		]) !== false;
	}
}
