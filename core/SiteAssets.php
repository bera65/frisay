<?php

class SiteAssets
{
	/** @var array<string, array{file: string, label: string}> */
	private const LOGOS = [
		'header' => ['file' => 'logo.png', 'label' => 'Ana logo (mobil)'],
		'bar' => ['file' => 'logo2.png', 'label' => 'Üst bar logosu'],
		'footer' => ['file' => 'logoFooter.png', 'label' => 'Footer logosu'],
		'admin' => ['file' => 'shopLogo.png', 'label' => 'Admin panel logosu'],
	];

	public static function imgDir(): string
	{
		return dirname(__DIR__) . '/img';
	}

	/** @return array<string, array{key: string, label: string, url: string, file: string}> */
	public static function getLogos(): array
	{
		global $domain;
		$list = [];

		foreach (self::LOGOS as $key => $meta) {
			$list[$key] = [
				'key' => $key,
				'label' => $meta['label'],
				'file' => self::resolveLogoFile($key),
				'url' => self::resolveLogoUrl($key),
			];
		}

		return $list;
	}

	public static function getLogoUrl(string $key): string
	{
		$logos = self::getLogos();

		return $logos[$key]['url'] ?? (rtrim($GLOBALS['domain'] ?? '', '/') . '/img/logo.png');
	}

	public static function uploadLogo(string $key, array $file): array
	{
		if (!isset(self::LOGOS[$key])) {
			return ['success' => false, 'message' => 'Geçersiz logo türü'];
		}

		if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
			return ['success' => false, 'message' => 'Dosya seçilmedi'];
		}

		if (!empty($file['error'])) {
			return ['success' => false, 'message' => 'Dosya yüklenemedi'];
		}

		if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
			return ['success' => false, 'message' => 'Logo en fazla 2 MB olabilir'];
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = $finfo ? (string) finfo_file($finfo, $file['tmp_name']) : '';
		if ($finfo) {
			finfo_close($finfo);
		}

		$map = [
			'image/jpeg' => 'jpg',
			'image/png' => 'png',
			'image/webp' => 'webp',
			'image/gif' => 'gif',
			'image/svg+xml' => 'svg',
		];

		if (!isset($map[$mime])) {
			return ['success' => false, 'message' => 'Sadece JPG, PNG, WEBP, GIF veya SVG yükleyebilirsiniz'];
		}

		$targetName = self::LOGOS[$key]['file'];
		$ext = strtolower(pathinfo($targetName, PATHINFO_EXTENSION));
		$uploadExt = $map[$mime];

		if ($mime === 'image/svg+xml') {
			$uploadExt = 'svg';
		}

		$dir = self::imgDir();

		if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
			return ['success' => false, 'message' => 'img klasörü oluşturulamadı'];
		}

		if ($uploadExt !== $ext) {
			$base = pathinfo($targetName, PATHINFO_FILENAME);
			$targetName = $base . '.' . $uploadExt;
		}

		$targetPath = $dir . '/' . $targetName;

		if ($mime === 'image/svg+xml') {
			$raw = (string) file_get_contents($file['tmp_name']);
			$sanitized = Security::sanitizeSvg($raw);

			if ($sanitized === null) {
				return ['success' => false, 'message' => 'SVG dosyası güvenlik kontrolünden geçemedi'];
			}

			if (@file_put_contents($targetPath, $sanitized) === false) {
				return ['success' => false, 'message' => 'Logo kaydedilemedi'];
			}
		} elseif (!move_uploaded_file($file['tmp_name'], $targetPath)) {
			return ['success' => false, 'message' => 'Logo kaydedilemedi'];
		}

		Settings::set('LOGO_' . strtoupper($key), $targetName);

		return [
			'success' => true,
			'message' => self::LOGOS[$key]['label'] . ' güncellendi',
			'file' => $targetName,
		];
	}

	public static function resolveLogoFile(string $key): string
	{
		if (!isset(self::LOGOS[$key])) {
			return 'logo.png';
		}

		$stored = trim((string) Settings::get('LOGO_' . strtoupper($key)));

		if ($stored !== '' && is_file(self::imgDir() . '/' . $stored)) {
			return $stored;
		}

		return self::LOGOS[$key]['file'];
	}

	public static function resolveLogoUrl(string $key): string
	{
		global $domain;

		return rtrim($domain, '/') . '/img/' . self::resolveLogoFile($key);
	}
}
