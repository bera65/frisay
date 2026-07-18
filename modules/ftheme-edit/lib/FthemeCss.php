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
		'fy-dark' => '#0F172A',
		'fy-muted' => '#64748B',
		'fy-light' => '#F8FAFC',
		'fy-border' => '#E5E7EB',
		'fy-gradient' => '#4279ea',
		'fy-footer-bg' => '#0F172A',
		'header-bg' => '#ffffff',
		'surface' => '#ffffff',
	];

	/** @var array<string, string> alias => var() referansı (style.css uyumu) */
	public const COLOR_ALIASES = [
		'prime-primary' => 'var(--fy-gradient)',
		'brand-accent' => 'var(--fy-gradient)',
		'brand-primary' => 'var(--fy-dark)',
		'brand-primary-dark' => 'var(--fy-dark)',
		'coral-color' => 'var(--fy-accent)',
		'text-primary' => 'var(--fy-dark)',
		'text-secondary' => 'var(--fy-muted)',
		'link-color' => 'var(--fy-dark)',
		'link-hover-color' => 'var(--fy-secondary)',
		'footer-bg' => 'var(--fy-footer-bg)',
		'footer-text' => 'var(--fy-muted)',
		'footer-heading' => '#ffffff',
		'border-color' => 'var(--fy-border)',
		'surface-soft' => 'var(--fy-light)',
		'price-color' => 'var(--fy-dark)',
		'old-price-color' => 'var(--fy-muted)',
		'discount-bg' => 'var(--fy-gradient)',
		'discount-color' => '#ffffff',
		'color2' => 'var(--fy-light)',
	];

	/** @var array<string, array<string, string>> */
	public const COLOR_GROUPS = [
		'Buton & vurgu' => [
			'fy-secondary' => 'İkincil vurgu',
			'fy-accent' => 'Aksan',
			'fy-gradient' => 'Buton rengi (gradient)',
		],
		'Metin & arka plan' => [
			'fy-dark' => 'Ana metin',
			'fy-muted' => 'Soluk metin',
			'fy-light' => 'Yumuşak bölüm arka planı',
			'surface' => 'Sayfa arka planı',
			'header-bg' => 'Header arka planı',
			'fy-border' => 'Kenarlık',
		],
		'Footer' => [
			'fy-footer-bg' => 'Footer arka plan',
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

	public static function customJsPath(string $theme = ''): string
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		return Theme::templatesPath() . '/' . $theme . '/js/custom.js';
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

	public static function readCustomJs(string $theme = ''): string
	{
		$path = self::customJsPath($theme);

		if (!is_file($path)) {
			return '';
		}

		$content = file_get_contents($path);

		return $content === false ? '' : $content;
	}

	public static function ensureCustomJs(string $theme = ''): void
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();
		$path = self::customJsPath($theme);

		if (is_file($path)) {
			return;
		}

		$dir = dirname($path);

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return;
		}

		file_put_contents($path, "/**\n * Özel JS — ftheme-edit modülünden düzenlenir.\n */\n");
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

	public static function writeCustomJs(string $js, string $theme = ''): array
	{
		$theme = $theme !== '' ? $theme : self::getTargetTheme();

		if (!Theme::isValidName($theme)) {
			return ['success' => false, 'message' => 'Geçersiz tema'];
		}

		self::ensureCustomJs($theme);
		$path = self::customJsPath($theme);

		if (file_put_contents($path, $js) === false) {
			return ['success' => false, 'message' => 'custom.js yazılamadı'];
		}

		return ['success' => true, 'message' => 'custom.js kaydedildi'];
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

		$lines[] = '';
		$lines[] = "\t/* style.css / sepet / bildirim uyumu */";

		foreach (self::COLOR_ALIASES as $key => $value) {
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
