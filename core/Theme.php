<?php

class Theme
{
	public static function templatesPath(): string
	{
		return dirname(__DIR__) . '/templates';
	}

	/** @return array<string, array{name: string, label: string, has_colors: bool}> */
	public static function discover(): array
	{
		$dir = self::templatesPath();
		$themes = [];

		if (!is_dir($dir)) {
			return $themes;
		}

		foreach (scandir($dir) ?: [] as $entry) {
			if ($entry === '.' || $entry === '..' || $entry === 'admin') {
				continue;
			}

			$path = $dir . '/' . $entry;

			if (!is_dir($path) || !is_file($path . '/header.tpl')) {
				continue;
			}

			$themes[$entry] = [
				'name' => $entry,
				'label' => self::labelFor($entry),
				'has_colors' => is_file(self::colorsPath($entry)),
			];
		}

		ksort($themes);

		return $themes;
	}

	public static function labelFor(string $name): string
	{
		$labels = [
			'default' => 'Varsayılan Tema',
			'blue' => 'Blue',
			'restoran' => 'Restoran',
			'chapan' => 'Chapan',
			'dress' => 'Dress',
			'prime' => 'Prime',
		];

		return $labels[$name] ?? ucfirst(str_replace(['-', '_'], ' ', $name));
	}

	public static function colorsPath(string $theme): string
	{
		return self::templatesPath() . '/' . self::sanitizeName($theme) . '/css/colors.css';
	}

	public static function customCssPath(string $theme): string
	{
		return self::templatesPath() . '/' . self::sanitizeName($theme) . '/css/custom.css';
	}

	private static function optionsSettingsKey(string $theme): string
	{
		return 'THEME_OPTIONS_' . self::sanitizeName($theme);
	}

	/** @return array<string, string> */
	public static function discoverHeaderVariants(string $theme): array
	{
		if (!self::isValidName($theme)) {
			return [];
		}

		$dir = self::templatesPath() . '/' . $theme . '/_mini';
		$variants = [];

		if (!is_dir($dir)) {
			return [];
		}

		foreach (scandir($dir) ?: [] as $entry) {
			if (!preg_match('/^header\d+\.tpl$/', $entry)) {
				continue;
			}

			$key = substr($entry, 0, -4);
			$variants[$key] = self::headerVariantLabel($key);
		}

		ksort($variants);

		return $variants;
	}

	private static function headerVariantLabel(string $key): string
	{
		$labels = [
			'header1' => 'Klasik',
			'header2' => 'Modern (üst bar)',
			'header3' => 'Kompakt',
		];

		return $labels[$key] ?? ucfirst($key);
	}

	/** @return array<string, array{label: string, type: string, default: string, options?: array<string, string>}> */
	public static function getOptionDefinitions(string $theme): array
	{
		$fonts = self::getFontChoices();
		$widths = self::getContainerWidthChoices();
		$defs = [
			'font' => [
				'label' => 'Yazı tipi',
				'type' => 'select',
				'default' => 'system',
				'options' => $fonts,
			],
			'container_width' => [
				'label' => 'Site genişliği',
				'type' => 'select',
				'default' => '1320',
				'options' => $widths,
			],
		];

		$headers = self::discoverHeaderVariants($theme);

		if ($headers !== []) {
			$defaultHeader = isset($headers['header2']) ? 'header2' : array_key_first($headers);
			$defs = array_merge([
				'header' => [
					'label' => 'Header stili',
					'type' => 'select',
					'default' => (string) $defaultHeader,
					'options' => $headers,
				],
			], $defs);
		}

		return $defs;
	}

	/** @return array<string, string> */
	public static function getFontChoices(): array
	{
		return [
			'system' => 'Sistem (Segoe UI)',
			'inter' => 'Inter',
			'poppins' => 'Poppins',
			'nunito' => 'Nunito',
			'roboto' => 'Roboto',
			'open-sans' => 'Open Sans',
		];
	}

	/** @return array<string, string> */
	public static function getContainerWidthChoices(): array
	{
		return [
			'1140' => 'Standart (1140px)',
			'1320' => 'Geniş (1320px)',
			'1440' => 'Çok geniş (1440px)',
			'fluid' => 'Tam genişlik',
		];
	}

	/** @return array{font_family: string, google_font_url: string} */
	public static function resolveFont(string $fontKey): array
	{
		$map = [
			'system' => [
				'font_family' => "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif",
				'google_font_url' => '',
			],
			'inter' => [
				'font_family' => "'Inter', sans-serif",
				'google_font_url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
			],
			'poppins' => [
				'font_family' => "'Poppins', sans-serif",
				'google_font_url' => 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap',
			],
			'nunito' => [
				'font_family' => "'Nunito', sans-serif",
				'google_font_url' => 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap',
			],
			'roboto' => [
				'font_family' => "'Roboto', sans-serif",
				'google_font_url' => 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap',
			],
			'open-sans' => [
				'font_family' => "'Open Sans', sans-serif",
				'google_font_url' => 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap',
			],
		];

		return $map[$fontKey] ?? $map['system'];
	}

	/** @return array<string, string> */
	public static function getDefaultOptions(string $theme): array
	{
		$options = [];

		foreach (self::getOptionDefinitions($theme) as $key => $meta) {
			$options[$key] = (string) $meta['default'];
		}

		return $options;
	}

	/** @return array<string, string> */
	public static function getOptions(string $theme): array
	{
		if (!self::isValidName($theme)) {
			return [];
		}

		$defaults = self::getDefaultOptions($theme);
		$raw = Settings::get(self::optionsSettingsKey($theme));

		if ($raw === '') {
			return $defaults;
		}

		$decoded = json_decode($raw, true);

		if (!is_array($decoded)) {
			return $defaults;
		}

		$defs = self::getOptionDefinitions($theme);
		$merged = $defaults;

		foreach ($decoded as $key => $value) {
			if (!isset($defs[$key])) {
				continue;
			}

			$value = trim((string) $value);
			$allowed = $defs[$key]['options'] ?? null;

			if (is_array($allowed) && $allowed !== [] && !isset($allowed[$value])) {
				continue;
			}

			$merged[$key] = $value;
		}

		return $merged;
	}

	/** @param array<string, string> $options */
	public static function saveOptions(string $theme, array $options): array
	{
		if (!self::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		$defs = self::getOptionDefinitions($theme);
		$normalized = self::getDefaultOptions($theme);

		foreach ($defs as $key => $meta) {
			if (!array_key_exists($key, $options)) {
				continue;
			}

			$value = trim((string) $options[$key]);
			$allowed = $meta['options'] ?? null;

			if (is_array($allowed) && $allowed !== [] && !isset($allowed[$value])) {
				return ['success' => false, 'message' => 'Geçersiz seçim: ' . $meta['label']];
			}

			$normalized[$key] = $value;
		}

		$json = json_encode($normalized, JSON_UNESCAPED_UNICODE);

		if ($json === false || !Settings::set(self::optionsSettingsKey($theme), $json)) {
			return ['success' => false, 'message' => 'Tema ayarları kaydedilemedi'];
		}

		self::writeCustomCss($theme, $normalized);

		return ['success' => true, 'message' => 'Tema özelleştirmesi kaydedildi'];
	}

	public static function ensureCustomCss(string $theme): void
	{
		if (!self::isValidName($theme)) {
			return;
		}

		$path = self::customCssPath($theme);

		if (is_file($path)) {
			return;
		}

		self::writeCustomCss($theme, self::getOptions($theme));
	}

	/** @param array<string, string> $options */
	private static function writeCustomCss(string $theme, array $options): void
	{
		$path = self::customCssPath($theme);
		$dir = dirname($path);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return;
		}

		$font = self::resolveFont($options['font'] ?? 'system');
		$widthKey = $options['container_width'] ?? '1320';
		$widthMap = [
			'1140' => '1140px',
			'1320' => '1320px',
			'1440' => '1440px',
			'fluid' => '100%',
		];
		$maxWidth = $widthMap[$widthKey] ?? '1320px';
		$padding = $widthKey === 'fluid' ? '0 16px' : '0 12px';

		$css = implode("\n", [
			'/**',
			' * Tema özelleştirme — Admin > Temalar ekranından düzenlenir.',
			' */',
			':root {',
			"\t--theme-font-family: {$font['font_family']};",
			"\t--theme-container-max: {$maxWidth};",
			"\t--theme-container-padding: {$padding};",
			'}',
			'',
			'body, .prime-body {',
			"\tfont-family: var(--theme-font-family);",
			'}',
			'',
			'.custom-container,',
			'.page > .container,',
			'.page .container.custom-container {',
			"\tmax-width: var(--theme-container-max);",
			"\tpadding-left: var(--theme-container-padding);",
			"\tpadding-right: var(--theme-container-padding);",
			"\tmargin-left: auto;",
			"\tmargin-right: auto;",
			"\twidth: 100%;",
			'}',
			'',
		]);

		file_put_contents($path, $css);
	}

	/** @return array<string, mixed> */
	public static function getResolvedOptions(string $theme): array
	{
		$options = self::getOptions($theme);
		$font = self::resolveFont($options['font'] ?? 'system');
		$header = $options['header'] ?? '';

		if ($header === '' || !is_file(self::templatesPath() . '/' . $theme . '/_mini/' . $header . '.tpl')) {
			$variants = self::discoverHeaderVariants($theme);
			$header = isset($variants['header2']) ? 'header2' : ($variants ? (string) array_key_first($variants) : '');
		}

		return [
			'header' => $header,
			'font' => $options['font'] ?? 'system',
			'container_width' => $options['container_width'] ?? '1320',
			'font_family' => $font['font_family'],
			'google_font_url' => $font['google_font_url'],
		];
	}

	public static function isValidName(string $theme): bool
	{
		return (bool) preg_match('/^[a-z0-9_-]+$/', $theme)
			&& is_dir(self::templatesPath() . '/' . $theme);
	}

	public static function sanitizeName(string $theme): string
	{
		return preg_replace('/[^a-z0-9_-]/', '', strtolower($theme)) ?: 'default';
	}

	/** @return array<string, array{label: string, default: string, group: string}> */
	public static function getColorDefinitions(): array
	{
		return [
			'brand-primary' => ['label' => 'Ana renk (buton)', 'default' => '#1a1a1a', 'group' => 'marka'],
			'brand-primary-dark' => ['label' => 'Ana renk koyu', 'default' => '#000000', 'group' => 'marka'],
			'brand-accent' => ['label' => 'Vurgu rengi', 'default' => '#d6001c', 'group' => 'marka'],
			'brand-accent-light' => ['label' => 'Vurgu açık', 'default' => '#fdeaec', 'group' => 'marka'],
			'surface' => ['label' => 'Sayfa arka planı', 'default' => '#ffffff', 'group' => 'yuzey'],
			'surface-soft' => ['label' => 'Yumuşak arka plan', 'default' => '#f6f6f6', 'group' => 'yuzey'],
			'border-color' => ['label' => 'Kenarlık rengi', 'default' => '#ebebeb', 'group' => 'yuzey'],
			'text-primary' => ['label' => 'Ana metin', 'default' => '#1a1a1a', 'group' => 'metin'],
			'text-secondary' => ['label' => 'İkincil metin', 'default' => '#767676', 'group' => 'metin'],
			'color-dark' => ['label' => 'Koyu ton', 'default' => '#000000', 'group' => 'ek'],
			'color1' => ['label' => 'Buton / aksan 1', 'default' => '#1a1a1a', 'group' => 'ek'],
			'color1-hover' => ['label' => 'Buton hover 1', 'default' => '#000000', 'group' => 'ek'],
			'color2' => ['label' => 'Header arka plan', 'default' => '#f6f6f6', 'group' => 'ek'],
			'color3' => ['label' => 'Aksan 3', 'default' => '#1a1a1a', 'group' => 'ek'],
			'color3-hover' => ['label' => 'Aksan 3 hover', 'default' => '#000000', 'group' => 'ek'],
			'footer-bg' => ['label' => 'Footer arka plan', 'default' => '#0c0c0c', 'group' => 'footer'],
			'footer-heading' => ['label' => 'Footer başlık', 'default' => '#ffffff', 'group' => 'footer'],
			'footer-text' => ['label' => 'Footer metin', 'default' => '#a3a3a3', 'group' => 'footer'],
			'footer-accent' => ['label' => 'Footer vurgu', 'default' => '#ffffff', 'group' => 'footer'],
		];
	}

	/** @return array<string, string> */
	public static function getColors(string $theme): array
	{
		self::ensureColorsFile($theme);

		$defs = self::getColorDefinitions();
		$values = [];

		foreach ($defs as $key => $meta) {
			$values[$key] = $meta['default'];
		}

		$file = self::colorsPath($theme);

		if (!is_file($file)) {
			return $values;
		}

		$content = file_get_contents($file);

		if ($content === false) {
			return $values;
		}

		if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$key = $match[1];

				if (isset($defs[$key])) {
					$values[$key] = trim($match[2]);
				}
			}
		}

		return $values;
	}

	/** @param array<string, string> $colors */
	public static function saveColors(string $theme, array $colors): array
	{
		if (!self::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		$defs = self::getColorDefinitions();
		$normalized = [];

		foreach ($defs as $key => $meta) {
			$value = trim((string) ($colors[$key] ?? $meta['default']));

			if (!self::isValidColor($value)) {
				return ['success' => false, 'message' => 'Geçersiz renk: ' . $meta['label']];
			}

			$normalized[$key] = $value;
		}

		$css = self::buildColorsCss($normalized);
		$path = self::colorsPath($theme);
		$dir = dirname($path);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'Tema klasörü oluşturulamadı'];
		}

		if (file_put_contents($path, $css) === false) {
			return ['success' => false, 'message' => 'colors.css yazılamadı'];
		}

		return ['success' => true, 'message' => 'Tema renkleri kaydedildi'];
	}

	public static function setActiveTheme(string $theme): array
	{
		if (!self::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		if (!Settings::set('THEME', $theme)) {
			return ['success' => false, 'message' => 'Tema kaydedilemedi'];
		}

		self::ensureColorsFile($theme);
		self::ensureCustomCss($theme);

		return ['success' => true, 'message' => 'Aktif tema güncellendi'];
	}

	public static function ensureColorsFile(string $theme): void
	{
		if (!self::isValidName($theme)) {
			return;
		}

		$path = self::colorsPath($theme);

		if (is_file($path)) {
			return;
		}

		$defs = self::getColorDefinitions();
		$defaults = [];

		foreach ($defs as $key => $meta) {
			$defaults[$key] = $meta['default'];
		}

		$dir = dirname($path);

		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}

		file_put_contents($path, self::buildColorsCss($defaults));
	}

	/** @param array<string, string> $colors */
	private static function buildColorsCss(array $colors): string
	{
		$lines = [
			'/**',
			' * Tema renkleri — Admin > Temalar ekranından düzenlenir.',
			' */',
			':root {',
		];

		foreach (self::getColorDefinitions() as $key => $meta) {
			$value = $colors[$key] ?? $meta['default'];
			$lines[] = "\t--{$key}: {$value};";
		}

		$lines[] = "\t--surface-muted: var(--surface);";
		$lines[] = "\t--color-text: var(--text-primary);";
		$lines[] = "\t--text-gray: var(--text-secondary);";
		$lines[] = '}';
		$lines[] = '';

		return implode("\n", $lines);
	}

	private static function isValidColor(string $value): bool
	{
		if ($value === '') {
			return false;
		}

		if (preg_match('/^var\(--[a-z0-9-]+\)$/i', $value)) {
			return true;
		}

		if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value)) {
			return true;
		}

		if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+(?:\s*,\s*(?:0?\.\d+|1))?\s*\)$/i', $value)) {
			return true;
		}

		return (bool) preg_match('/^[a-z]+$/i', $value);
	}
}
