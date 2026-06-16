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
		];

		return $labels[$name] ?? ucfirst(str_replace(['-', '_'], ' ', $name));
	}

	public static function colorsPath(string $theme): string
	{
		return self::templatesPath() . '/' . self::sanitizeName($theme) . '/css/colors.css';
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
