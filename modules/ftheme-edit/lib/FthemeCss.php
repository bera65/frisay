<?php

if (!defined('IN_SCRIPT') && !defined('IN_ADMIN')) {
	exit;
}

class FthemeCss
{
	/** @var array<string, string> */
	public const DEFAULT_COLORS = [
		'fy-primary' => '#2563EB',
		'fy-secondary' => '#1D4ED8',
		'fy-accent' => '#3B82F6',
		'fy-hover' => '#1E40AF',
		'fy-dark' => '#0F172A',
		'fy-footer-bg' => '#0F172A',
		'prime-primary' => '#2563EB',
		'header-bg' => '#ffffff',
		'coral-color' => '#3B82F6',
		'brand-primary' => '#1a1a1a',
		'brand-primary-dark' => '#000000',
		'brand-accent' => '#d6001c',
		'brand-accent-light' => '#fdeaec',
		'surface' => '#ffffff',
		'surface-soft' => '#f6f6f6',
		'border-color' => '#ebebeb',
		'text-primary' => '#1a1a1a',
		'text-secondary' => '#767676',
		'link-color' => '#1a1a1a',
		'link-hover-color' => '#d6001c',
		'color-dark' => '#000000',
		'color1' => '#1a1a1a',
		'color1-hover' => '#000000',
		'color2' => '#f6f6f6',
		'color3' => '#1a1a1a',
		'color3-hover' => '#000000',
		'price-color' => '#1a1a1a',
		'old-price-color' => '#767676',
		'discount-bg' => '#d6001c',
		'discount-color' => '#ffffff',
		'footer-bg' => '#0c0c0c',
		'footer-heading' => '#ffffff',
		'footer-text' => '#a3a3a3',
		'footer-accent' => '#ffffff',
		'footer-border' => '#0c0c0c',
		'social-icon-color' => '#a3a3a3',
		'social-icon-hover' => '#ffffff',
		'social-icon-bg' => 'rgba(255,255,255,0.1)',
		'social-icon-bg-hover' => '#d6001c',
		'theme-color' => '#213c8b',
		'active-category-color' => '#d6001c',
		'category-hover-color' => '#d6001c',
		'mobile-menu-bg' => '#ffffff',
		'mobile-category-icon-color' => '#213c8b',
		'fy-gradient' => '#4279ea',
		'font-family' => "'Poppins', 'Segoe UI', sans-serif",
		'container' => 'var(--theme-container-max, 1320px)',
	];

	/** @var array<string, array<string, string>> */
	public const COLOR_GROUPS = [
		'Frisay paleti' => [
			'fy-primary' => 'Birincil',
			'fy-secondary' => 'İkincil',
			'fy-accent' => 'Vurgu',
			'fy-hover' => 'Hover',
			'fy-dark' => 'Koyu',
			'fy-footer-bg' => 'Footer arka plan',
			'fy-gradient' => 'Gradient',
			'prime-primary' => 'Prime birincil',
		],
		'Header & yüzey' => [
			'header-bg' => 'Header arka plan',
			'surface' => 'Yüzey',
			'surface-soft' => 'Yumuşak yüzey',
			'border-color' => 'Kenarlık',
			'coral-color' => 'Coral',
		],
		'Marka' => [
			'brand-primary' => 'Marka birincil',
			'brand-primary-dark' => 'Marka koyu',
			'brand-accent' => 'Marka vurgu',
			'brand-accent-light' => 'Marka vurgu açık',
		],
		'Metin & link' => [
			'text-primary' => 'Metin birincil',
			'text-secondary' => 'Metin ikincil',
			'link-color' => 'Link',
			'link-hover-color' => 'Link hover',
			'color-dark' => 'Koyu',
			'color1' => 'Renk 1',
			'color1-hover' => 'Renk 1 hover',
			'color2' => 'Renk 2',
			'color3' => 'Renk 3',
			'color3-hover' => 'Renk 3 hover',
		],
		'Fiyat' => [
			'price-color' => 'Fiyat',
			'old-price-color' => 'Eski fiyat',
			'discount-bg' => 'İndirim arka plan',
			'discount-color' => 'İndirim metin',
		],
		'Footer' => [
			'footer-bg' => 'Arka plan',
			'footer-heading' => 'Başlık',
			'footer-text' => 'Metin',
			'footer-accent' => 'Vurgu',
			'footer-border' => 'Kenarlık',
		],
		'Sosyal ikonlar' => [
			'social-icon-color' => 'İkon rengi',
			'social-icon-hover' => 'İkon hover',
			'social-icon-bg' => 'İkon arka plan',
			'social-icon-bg-hover' => 'İkon arka plan hover',
		],
		'Kategori & mobil' => [
			'theme-color' => 'Tema rengi',
			'active-category-color' => 'Aktif kategori',
			'category-hover-color' => 'Kategori hover',
			'mobile-menu-bg' => 'Mobil menü arka plan',
			'mobile-category-icon-color' => 'Mobil kategori ikon',
		],
		'Tipografi & layout' => [
			'font-family' => 'Font ailesi',
			'container' => 'Container',
		],
	];

	public static function getTargetTheme(): string
	{
		$theme = Settings::get('THEME') ?: 'fyazilim';

		if (!Theme::isValidName($theme)) {
			$theme = 'fyazilim';
		}

		return $theme;
	}

	public static function colorsPath(string $theme = ''): string
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		return Theme::colorsPath($theme);
	}

	public static function customCssPath(string $theme = ''): string
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		return Theme::customCssPath($theme);
	}

	/** @return array<string, string> */
	public static function readColors(string $theme = ''): array
	{
		$colors = self::DEFAULT_COLORS;
		$path = self::colorsPath($theme);

		if (!is_file($path)) {
			return $colors;
		}

		$content = file_get_contents($path);

		if ($content === false) {
			return $colors;
		}

		if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $content, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$key = $match[1];

				if (array_key_exists($key, $colors)) {
					$colors[$key] = trim($match[2]);
				}
			}
		}

		return $colors;
	}

	public static function readCustomCss(string $theme = ''): string
	{
		$path = self::customCssPath($theme);

		if (!is_file($path)) {
			return '';
		}

		$content = file_get_contents($path);

		return $content === false ? '' : $content;
	}

	/** @param array<string, string> $colors */
	public static function writeColors(array $colors, string $theme = ''): array
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		if (!Theme::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		$normalized = self::DEFAULT_COLORS;

		foreach (array_keys(self::DEFAULT_COLORS) as $key) {
			$value = trim((string) ($colors[$key] ?? ''));

			if ($value === '') {
				$value = $normalized[$key];
			}

			if (!self::isValidColorValue($value)) {
				return ['success' => false, 'message' => 'Geçersiz renk değeri: --' . $key];
			}

			$normalized[$key] = $value;
		}

		$css = self::buildColorsCss($normalized);
		$path = self::colorsPath($theme);
		$dir = dirname($path);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'CSS klasörü oluşturulamadı'];
		}

		if (file_put_contents($path, $css) === false) {
			return ['success' => false, 'message' => 'colors.css yazılamadı'];
		}

		return ['success' => true, 'message' => 'Renkler colors.css dosyasına kaydedildi'];
	}

	public static function writeCustomCss(string $css, string $theme = ''): array
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		if (!Theme::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		$path = self::customCssPath($theme);
		$dir = dirname($path);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'CSS klasörü oluşturulamadı'];
		}

		if (file_put_contents($path, $css) === false) {
			return ['success' => false, 'message' => 'custom.css yazılamadı'];
		}

		return ['success' => true, 'message' => 'custom.css kaydedildi'];
	}

	/** @param array<string, string> $colors */
	private static function buildColorsCss(array $colors): string
	{
		$lines = [
			'/**',
			' * Tema renkleri — ftheme-edit modülünden düzenlenir.',
			' */',
			':root {',
		];

		foreach ($colors as $key => $value) {
			$lines[] = "\t--{$key}: {$value};";
		}

		$lines[] = '}';
		$lines[] = '';

		return implode("\n", $lines);
	}

	public static function isValidColorValue(string $value): bool
	{
		if ($value === '') {
			return false;
		}

		if (preg_match('/^var\(--[a-z0-9-]+(?:,\s*[^)]+)?\)$/i', $value)) {
			return true;
		}

		if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $value)) {
			return true;
		}

		if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+(?:\s*,\s*(?:0?\.\d+|1))?\s*\)$/i', $value)) {
			return true;
		}

		if (preg_match('/^\'[^\']+\'(?:,\s*\'[^\']+\')*(?:,\s*[a-z\s-]+)?$/i', $value)) {
			return true;
		}

		return (bool) preg_match('/^[a-z]+$/i', $value);
	}

	public static function hexForPicker(string $value): string
	{
		if (preg_match('/^#([0-9a-f]{6})$/i', $value, $match)) {
			return '#' . strtolower($match[1]);
		}

		if (preg_match('/^#([0-9a-f]{3})$/i', $value, $match)) {
			$h = $match[1];
			return '#' . strtolower($h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2]);
		}

		return '#2563eb';
	}
}
