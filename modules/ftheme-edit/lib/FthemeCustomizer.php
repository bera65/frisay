<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class FthemeCustomizer
{
	public const SESSION_KEY = 'ftheme_customize_token';

	/** @var array<string, array<string, mixed>> */
	public const REGIONS = [
		'feature-title' => [
			'label' => 'Öne çıkan başlık',
			'setting' => 'FEATURE-TITLE',
			'type' => 'text',
			'page' => 'home',
		],
		'feature-desc' => [
			'label' => 'Öne çıkan açıklama',
			'setting' => 'FEATURE-DESC',
			'type' => 'textarea',
			'page' => 'home',
		],
		'footer-text' => [
			'label' => 'Footer metni',
			'setting' => 'FOOTER-TEXT',
			'type' => 'textarea',
			'page' => 'home',
		],
		'cookie-text' => [
			'label' => 'Çerez metni',
			'setting' => 'COOKIE-TEXT',
			'type' => 'textarea',
			'page' => 'home',
		],
	];

	/** @var array<string, string> */
	public const QUICK_COLORS = [
		'fy-gradient' => 'Buton rengi',
		'fy-secondary' => 'İkincil vurgu',
		'fy-dark' => 'Metin rengi',
		'fy-muted' => 'Soluk metin',
		'fy-light' => 'Yumuşak arka plan',
		'header-bg' => 'Header arka plan',
		'surface' => 'Sayfa arka plan',
		'fy-footer-bg' => 'Footer arka plan',
		'fy-border' => 'Kenarlık',
	];

	public static function ensureSchema(): void
	{
		static $done = false;

		if ($done) {
			return;
		}

		$done = true;

		try {
			DB::execute('ALTER TABLE `ftheme_settings` MODIFY `detail` TEXT NOT NULL');
		} catch (Throwable $e) {
			// Tablo henüz yoksa kurulum devreye girer.
		}
	}

	public static function startSession(): string
	{
		$token = bin2hex(random_bytes(16));
		$_SESSION[self::SESSION_KEY] = $token;

		return $token;
	}

	public static function getSessionToken(): string
	{
		return (string) ($_SESSION[self::SESSION_KEY] ?? '');
	}

	public static function isPreviewActive(): bool
	{
		$param = (string) Tools::getValue('ftheme_customize');
		$sessionToken = self::getSessionToken();

		return $param !== '' && $sessionToken !== '' && hash_equals($sessionToken, $param);
	}

	public static function getPreviewUrl(string $domain): string
	{
		$token = self::getSessionToken();

		if ($token === '') {
			$token = self::startSession();
		}

		$base = rtrim($domain, '/');

		return $base . '/?ftheme_customize=' . rawurlencode($token);
	}

	/**
	 * @param array<string, string> $settings
	 * @param array<string, string> $colors
	 * @param array<int, array<string, mixed>> $blocks
	 * @return array<string, mixed>
	 */
	public static function buildClientState(
		array $settings,
		array $colors,
		array $blocks,
		string $customCss = '',
		string $customJs = ''
	): array {
		return [
			'regions' => self::REGIONS,
			'quickColors' => self::QUICK_COLORS,
			'colorGroups' => FthemeCss::COLOR_GROUPS,
			'colorAliases' => FthemeCss::COLOR_ALIASES,
			'settings' => $settings,
			'colors' => $colors,
			'blocks' => $blocks,
			'customCss' => $customCss,
			'customJs' => $customJs,
			'blockTypes' => [
				['type' => 'banner', 'label' => 'Banner (görsel)'],
				['type' => 'html', 'label' => 'Özel HTML bölümü'],
				['type' => 'featured', 'label' => 'Öne çıkan ürünler'],
				['type' => 'categories', 'label' => 'Kategori ürünleri'],
				['type' => 'home_text', 'label' => 'Ana sayfa metni'],
				['type' => 'slider', 'label' => 'Ana slider'],
				['type' => 'promo', 'label' => 'Promo slider'],
			],
		];
	}

	/**
	 * @param array<string, mixed> $payload
	 * @return array{success: bool, message: string}
	 */
	public static function savePayload(array $payload, FthemeEditModule $module): array
	{
		$settings = $payload['settings'] ?? [];

		if (is_array($settings)) {
			foreach (self::REGIONS as $region) {
				$key = (string) ($region['setting'] ?? '');

				if ($key === '' || !array_key_exists($key, $settings)) {
					continue;
				}

				if (!$module->setSettingValue($key, (string) $settings[$key])) {
					return ['success' => false, 'message' => 'Ayar kaydedilemedi: ' . $key];
				}
			}

			$toggleKeys = [
				'HEADER', 'FOOTER', 'LOADING', 'GOTO-TOP', 'SHOW-COOKIE', 'SHOW-TOP-BAR',
				'DEFAULT-COLOR', 'THEME-FONT',
			];

			foreach ($toggleKeys as $key) {
				if (!array_key_exists($key, $settings)) {
					continue;
				}

				if (!$module->setSettingValue($key, (string) $settings[$key])) {
					return ['success' => false, 'message' => 'Ayar kaydedilemedi: ' . $key];
				}
			}
		}

		$colors = $payload['colors'] ?? [];

		if (is_array($colors) && $colors !== []) {
			$colorInput = [];

			foreach (array_keys(FthemeCss::DEFAULT_COLORS) as $key) {
				$colorInput[$key] = (string) ($colors[$key] ?? '');
			}

			$result = FthemeCss::writeColors($colorInput);

			if (!$result['success']) {
				return $result;
			}
		}

		$blocks = $payload['blocks'] ?? [];

		if (is_array($blocks) && !FthemeBlocks::saveBlocks($blocks)) {
			return ['success' => false, 'message' => 'Ana sayfa blokları kaydedilemedi'];
		}

		if (array_key_exists('customCss', $payload)) {
			$result = FthemeCss::writeCustomCss((string) $payload['customCss']);

			if (!$result['success']) {
				return $result;
			}
		}

		if (array_key_exists('customJs', $payload)) {
			$result = FthemeCss::writeCustomJs((string) $payload['customJs']);

			if (!$result['success']) {
				return $result;
			}
		}

		return ['success' => true, 'message' => 'Tema değişiklikleri yayınlandı'];
	}
}
