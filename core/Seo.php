<?php

class Seo
{
	/** @return array<string, array{label: string, title_key: string, desc_key: string, default_title: string, default_desc: string}> */
	public static function getPageDefinitions(): array
	{
		$siteName = Settings::get('SITE_NAME') ?: 'FShop';

		return [
			'home' => [
				'label' => 'Ana Sayfa',
				'title_key' => 'SEO_HOME_TITLE',
				'desc_key' => 'SEO_HOME_DESC',
				'default_title' => $siteName,
				'default_desc' => $siteName,
			],
			'contact' => [
				'label' => 'İletişim',
				'title_key' => 'SEO_CONTACT_TITLE',
				'desc_key' => 'SEO_CONTACT_DESC',
				'default_title' => 'İletişim',
				'default_desc' => 'Bizimle iletişime geçin',
			],
			'search' => [
				'label' => 'Arama',
				'title_key' => 'SEO_SEARCH_TITLE',
				'desc_key' => 'SEO_SEARCH_DESC',
				'default_title' => 'Arama',
				'default_desc' => 'Ürün ara',
			],
			'special' => [
				'label' => 'Kampanyalar',
				'title_key' => 'SEO_SPECIAL_TITLE',
				'desc_key' => 'SEO_SPECIAL_DESC',
				'default_title' => 'Kampanyalar',
				'default_desc' => 'İndirimli ürünler ve kampanyalar',
			],
			'cart' => [
				'label' => 'Sepet',
				'title_key' => 'SEO_CART_TITLE',
				'desc_key' => 'SEO_CART_DESC',
				'default_title' => 'Sepetim',
				'default_desc' => 'Alışveriş sepetiniz',
			],
		];
	}

	/** @return array<string, array{title: string, description: string}> */
	public static function getAllPageValues(): array
	{
		$values = [];

		foreach (self::getPageDefinitions() as $pageId => $def) {
			$values[$pageId] = [
				'title' => self::get($def['title_key'], $def['default_title']),
				'description' => self::get($def['desc_key'], $def['default_desc']),
			];
		}

		return $values;
	}

	public static function get(string $key, string $default = ''): string
	{
		$value = trim((string) Settings::get($key));

		return $value !== '' ? $value : $default;
	}

	public static function savePages(array $data): array
	{
		foreach (self::getPageDefinitions() as $pageId => $def) {
			$title = mb_substr(trim(strip_tags((string) ($data[$pageId . '_title'] ?? ''))), 0, 255);
			$description = mb_substr(trim(strip_tags((string) ($data[$pageId . '_description'] ?? ''))), 0, 512);

			if (!Settings::set($def['title_key'], $title)) {
				return ['success' => false, 'message' => 'SEO ayarları kaydedilemedi'];
			}

			if (!Settings::set($def['desc_key'], $description)) {
				return ['success' => false, 'message' => 'SEO ayarları kaydedilemedi'];
			}
		}

		return ['success' => true, 'message' => 'SEO ayarları kaydedildi'];
	}

	/** @return array{title: string, description: string} */
	public static function resolvePage(string $pageId, string $fallbackTitle = '', string $fallbackDesc = ''): array
	{
		$defs = self::getPageDefinitions();
		$def = $defs[$pageId] ?? null;

		if (!$def) {
			return [
				'title' => $fallbackTitle,
				'description' => $fallbackDesc,
			];
		}

		$title = self::get($def['title_key'], '');
		$description = self::get($def['desc_key'], '');

		if ($title === '') {
			$title = $fallbackTitle !== '' ? $fallbackTitle : $def['default_title'];
		}

		if ($description === '') {
			$description = $fallbackDesc !== '' ? $fallbackDesc : $def['default_desc'];
		}

		return [
			'title' => $title,
			'description' => $description,
		];
	}

	/** @return array{title: string, description: string} */
	public static function resolveEntity(string $metaTitle, string $metaDescription, string $fallbackTitle, string $fallbackDesc): array
	{
		$title = trim($metaTitle);
		$description = trim($metaDescription);

		return [
			'title' => $title !== '' ? $title : $fallbackTitle,
			'description' => $description !== '' ? $description : $fallbackDesc,
		];
	}

	/** @return array<string, string> */
	public static function getSchemaOrgFields(): array
	{
		return [
			'SCHEMA_ORG_STREET' => trim((string) Settings::get('SCHEMA_ORG_STREET')),
			'SCHEMA_ORG_CITY' => trim((string) Settings::get('SCHEMA_ORG_CITY')),
			'SCHEMA_ORG_POSTAL' => trim((string) Settings::get('SCHEMA_ORG_POSTAL')),
			'SCHEMA_ORG_LAT' => trim((string) Settings::get('SCHEMA_ORG_LAT')),
			'SCHEMA_ORG_LNG' => trim((string) Settings::get('SCHEMA_ORG_LNG')),
			'SCHEMA_FACEBOOK_URL' => trim((string) Settings::get('SCHEMA_FACEBOOK_URL')),
			'SCHEMA_INSTAGRAM_URL' => trim((string) Settings::get('SCHEMA_INSTAGRAM_URL')),
			'SCHEMA_YOUTUBE_URL' => trim((string) Settings::get('SCHEMA_YOUTUBE_URL')),
		];
	}

	public static function saveSchemaOrg(array $data): bool
	{
		$keys = array_keys(self::getSchemaOrgFields());

		foreach ($keys as $key) {
			if (!Settings::set($key, trim((string) ($data[$key] ?? '')))) {
				return false;
			}
		}

		return true;
	}
}
